<?php

namespace Vanguard\Http\Controllers\Web;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Vanguard\Exports\TallyExport;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Models\FedexInvoiceFbt;
use Vanguard\Services\FedExDocumentService;
use Vanguard\Services\FedExInvoiceService;
use Vanguard\Services\FedExShipmentService;

class FedExBatchController extends Controller
{
    public function index()
    {
        return view('fedex.batch', [
            'serviceTypes' => self::serviceTypes(),
            'pickupTypes'  => self::pickupTypes(),
        ]);
    }

    public function rows(Request $request): JsonResponse
    {
        try {
            $sheet = in_array($request->query('sheet'), ['FEDEX', 'CSB4']) ? $request->query('sheet') : null;
            $rows = FedExInvoiceService::listAllRows($sheet);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $processed = FedexInvoiceFbt::all()->keyBy('invoice_number');

        $result = array_map(function ($row) use ($processed) {
            $rec        = $processed->get($row['invoice_number']);
            $alreadyDone = (bool) ($rec?->tracking_number);

            if ($alreadyDone && $rec?->invoice_number) {
                try {
                    FedExInvoiceService::generateAndSavePdf(
                        $rec->invoice_number,
                        ['awb_number' => $rec->tracking_number]
                    );
                } catch (\Exception $e) { 
                }
            }

            $labelUrl = $rec?->label_url;
            $auxUrl   = $rec?->auxiliary_url;

            $isProxy = fn(?string $u) => $u && str_contains($u, 'label/download');
            $fileOk  = fn(?string $u) => $u && !str_contains($u, 'label/download')
                                             && file_exists(public_path($u));

            $labelBroken = $isProxy($labelUrl) || ($labelUrl && !$fileOk($labelUrl));
            $auxMissing  = $labelUrl && !$isProxy($labelUrl) && $fileOk($labelUrl)
                           && (!$auxUrl || !$fileOk($auxUrl));

            if ($alreadyDone && ($labelBroken || $auxMissing)) {
                $alreadyDone = false; 
            }

            return array_merge($row, [
                'already_done'    => $alreadyDone,
                'tracking'        => $rec?->tracking_number,
                'label_url'       => $fileOk($labelUrl) ? $labelUrl : null,
                'auxiliary_url'   => $fileOk($auxUrl)   ? $auxUrl   : null,
                'invoice_pdf_url' => $rec?->invoice_pdf_url,
            ]);
        }, $rows);

        return response()->json($result)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    public function processRow(Request $request): JsonResponse
    {
        set_time_limit(300); // override any server PHP limit; nginx timeout is the real ceiling

        $request->validate([
            'invoice_number' => 'required|string|max:100',
            'service_type'   => 'required|string',
            'pickup_type'    => 'required|string',
            'ship_date'      => 'required|date',
        ]);

        $ref = trim($request->invoice_number);
 
        if ($request->boolean('batch_reset')) {
            session(['tally_current_invoices' => []]);
        }

        try {
            $data = FedExInvoiceService::fetchFormData($ref);
        } catch (Exception $e) {
            return response()->json(['error' => 'Excel read error: ' . $e->getMessage()], 500);
        }

        if (!$data) {
            return response()->json(['error' => 'Not found in Excel: ' . $ref], 404);
        }

        $data['service_type'] = $request->service_type;
        $data['pickup_type']  = $request->pickup_type;
        $data['ship_date']    = $request->ship_date;
        $data['weight_unit']  = 'KG';

        FedexInvoiceFbt::where(function ($q) use ($ref) {
            $q->where('invoice_number', $ref)->orWhere('order_reference', $ref);
        })->whereNull('tracking_number')->delete();

        $invoicePdfUrl = null;
        try {
            [$documentId, $invoicePdfUrl] = FedExDocumentService::generateUploadAndSave(
                $ref,
                $data['recipient_country'] ?? 'US'
            );
            $data['etd_document_id'] = $documentId;
        } catch (Exception $e) { 
            \Log::warning('FedEx ETD upload skipped: ' . $e->getMessage(), ['invoice' => $ref]);
            $data['etd_document_id'] = null;
        }

        $trackingNo   = null;
        $labelUrl     = null;
        $auxiliaryUrl = null;
        $destLocId    = null;
        $lastError    = null;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response     = FedExShipmentService::createShipment($data);
                $trackingNo   = FedExShipmentService::parseTrackingNumber($response);
                $labelUrl     = FedExShipmentService::parseLabelUrl($response);
                $auxiliaryUrl = FedExShipmentService::parseAuxiliaryLabelUrl($response);
                $destLocId    = FedExShipmentService::parseDestinationLocationId($response);
                $lastError    = null;
                break;
            } catch (Exception $e) {
                $lastError = $e;
                $isTransient = str_contains($e->getMessage(), 'SERVICE.UNAVAILABLE')
                            || str_contains($e->getMessage(), 'SERVICE_UNAVAILABLE')
                            || str_contains($e->getMessage(), 'INTERNAL.SERVER.ERROR')
                            || str_contains($e->getMessage(), '"500"')
                            || str_contains($e->getMessage(), 'HTTP 5');
                if (!$isTransient || $attempt === 3) break;
                sleep(3); // wait before retry
            }
        }

        if ($lastError) {
            return response()->json(['error' => $lastError->getMessage()], 500);
        }

        if ($trackingNo) {
            try {
                FedExInvoiceService::generateAndSavePdf($ref, ['awb_number' => $trackingNo]);
            } catch (Exception $e) {
            }
        }

        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $ref);
        [$labelDownload, $auxiliaryDownload] = $this->saveLabelsInParallel(
            [$labelUrl, $auxiliaryUrl],
            [$safeName . '_LABEL', $safeName . '_AUX']
        );

        $csbType  = ($data['sheet_name'] ?? '') === 'CSB4' ? 'CSB4' : 'CSB5';
        $labelDir = storage_path('app/public/fedex/labels');
        foreach ([$labelDownload, $auxiliaryDownload] as $localUrl) {
            if ($localUrl && str_ends_with($localUrl, '.png')) {
                $this->overlayLabelText($labelDir . '/' . basename($localUrl), $csbType);
            }
        }

        $labelZplUrl = null;
        if ($labelDownload && str_ends_with($labelDownload, '.png')) {
            $labelZplUrl = $this->generateZplFromPng(
                $labelDir . '/' . basename($labelDownload),
                $labelDir,
                $safeName . '_LABEL'
            );
        }

        $invoicePdfLocal   = $invoicePdfUrl ? preg_replace('#^https?:#', '', $invoicePdfUrl) : null; 
        $serviceTypeLabel = str_replace('_', ' ', strtoupper($data['service_type'] ?? ''));
        $tallyRow = [
            'csb_type'                => ($data['sheet_name'] ?? '') === 'CSB4' ? 'CSB4' : 'CSB5',
            'recipient_name'          => $data['recipient_name'] ?? '',
            'recipient_company'       => $data['recipient_company'] ?? '',
            'recipient_street1'       => $data['recipient_street1'] ?? '',
            'recipient_street2'       => $data['recipient_street2'] ?? '',
            'recipient_city'          => $data['recipient_city'] ?? '',
            'recipient_state'         => $data['recipient_state'] ?? '',
            'recipient_postal'        => $data['recipient_postal'] ?? '',
            'recipient_country'       => $data['recipient_country'] ?? '',
            'recipient_phone'         => $data['recipient_phone'] ?? '',
            'recipient_email'         => $data['recipient_email'] ?? '',
            'reference'               => $data['reference'] ?? '',
            'inv_number'              => $data['inv_number'] ?? $ref,
            'invoice_date_raw'        => $data['invoice_date_raw'] ?? '',
            'freight_charges'         => $data['freight_charges'] ?? 0,
            'insurance_charges'       => $data['insurance_charges'] ?? 0,
            'total_packages'          => $data['total_packages'] ?? '1',
            'weight'                  => $data['weight'] ?? '',
            'length'                  => $data['length'] ?? '',
            'width'                   => $data['width'] ?? '',
            'height'                  => $data['height'] ?? '',
            'fob_value'               => $data['fob_value'] ?? 0,
            'currency'                => $data['currency'] ?? 'USD',
            'incoterm'                => $data['incoterm'] ?? 'CFR',
            'fedex_service_type'      => $serviceTypeLabel,
            'destination_location_id' => $destLocId ?? '',
        ]; 
        FedexInvoiceFbt::where('invoice_number', $ref)
            ->orWhere('order_reference', $ref)
            ->update([
                'tracking_number' => $trackingNo,
                'label_url'       => $labelDownload,
                'auxiliary_url'   => $auxiliaryDownload,
                'invoice_pdf_url' => $invoicePdfLocal,
                'tally_data'      => json_encode($tallyRow),
            ]);

        $tallyInvoices = session('tally_current_invoices', []);
        $tallyInvoices[] = $ref;
        session(['tally_current_invoices' => $tallyInvoices]);

        return response()->json([
            'invoice_number'  => $ref,
            'recipient'       => $data['recipient_name'],
            'tracking'        => $trackingNo,
            'label_url'       => $labelDownload,
            'zpl_url'         => $labelZplUrl,
            'auxiliary_url'   => $auxiliaryDownload,
            'invoice_pdf_url' => $invoicePdfLocal,
        ]);
    }

    private function saveLabelsInParallel(array $urls, array $baseNames): array
    {
        $dir = storage_path('app/public/fedex/labels');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
            \Log::info('FedEx labels dir created', ['path' => $dir]);
        }

        $results = array_fill(0, count($urls), null);

        $valid = [];
        foreach ($urls as $i => $url) {
            if ($url) $valid[$i] = $url;
        }

        if (empty($valid)) return $results;

        $downloads = $this->curlMultiGetStaggered(array_values($valid), 200000);
        $origIdx   = array_keys($valid);

        foreach ($downloads as $j => $resp) {
            $i    = $origIdx[$j];
            $name = $baseNames[$i];

            \Log::info('FedEx label download', [
                'name'     => $name,
                'http'     => $resp['code'],
                'len'      => is_string($resp['body']) ? strlen($resp['body']) : 'false',
                'first4'   => is_string($resp['body']) && strlen($resp['body']) > 4
                              ? bin2hex(substr($resp['body'], 0, 4)) : 'n/a',
            ]);

            $saved = $this->tryPersistLabel($resp['body'], $resp['code'], $name, $dir);
            if ($saved !== null) {
                $results[$i] = $saved;
                continue;
            }

            sleep(1);
            \Log::info('FedEx label retry', ['name' => $name]);
            $retry = $this->curlGet($urls[$i]);
            \Log::info('FedEx label retry result', [
                'name' => $name, 'http' => $retry['code'],
                'len'  => is_string($retry['body']) ? strlen($retry['body']) : 'false',
            ]);

            $saved = $this->tryPersistLabel($retry['body'], $retry['code'], $name, $dir);
            if ($saved !== null) {
                $results[$i] = $saved;
            } else {
                \Log::error('FedEx label retry also failed — proxy fallback', ['name' => $name]);
                $results[$i] = '/fedex/label/download?url=' . urlencode($urls[$i])
                             . '&name=' . urlencode($name);
            }
        }

        return $results;
    }

    private function curlMultiGetStaggered(array $urls, int $usec = 0): array
    {
        $mh      = curl_multi_init();
        $handles = [];

        foreach ($urls as $i => $url) {
            if ($i > 0 && $usec > 0) usleep($usec);
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
            if ($running) curl_multi_select($mh, 0.5);
        } while ($running > 0);

        $results = [];
        foreach ($handles as $i => $ch) {
            $results[$i] = [
                'body' => curl_multi_getcontent($ch),
                'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
            ];
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);

        return $results;
    }

    private function curlGet(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ['body' => $body, 'code' => $code];
    }

    private function tryPersistLabel(mixed $body, int $httpCode, string $baseName, string $dir): ?string
    {
        if ($body === false || strlen($body) === 0 || $httpCode >= 400) {
            return null;
        }
        $isPng = substr($body, 0, 4) === "\x89PNG";
        $isPdf = substr($body, 0, 4) === '%PDF';
        if (!$isPng && !$isPdf) {
            \Log::warning('FedEx label bad content', [
                'name'  => $baseName,
                'code'  => $httpCode,
                'first' => bin2hex(substr($body, 0, 8)),
            ]);
            return null;
        }
        $filename = $baseName . '.' . ($isPng ? 'png' : 'pdf');
        $written  = file_put_contents($dir . '/' . $filename, $body);
        if ($written === false) {
            \Log::error('FedEx label write failed', ['file' => $filename]);
            return null;
        }
        \Log::info('FedEx label saved locally', ['file' => $filename, 'bytes' => $written]);
        return '/storage/fedex/labels/' . $filename;
    }

    private function saveLabelLocally(?string $fedexUrl, string $baseName): ?string
    {
        if (!$fedexUrl) return null;

        try {
            $token = \Vanguard\Services\FedExAuthService::getAccessToken();

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $fedexUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            ]);
            $body     = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($body === false || $httpCode >= 400 || strlen($body) === 0) {
                throw new \Exception("HTTP {$httpCode} empty or error response");
            }

            $isPng = strlen($body) > 4 && substr($body, 0, 4) === "\x89PNG";
            $isPdf = strlen($body) > 4 && substr($body, 0, 4) === '%PDF';
            if (!$isPng && !$isPdf) {
                throw new \Exception("FedEx returned unexpected content (HTTP {$httpCode}) — URL may have expired");
            }
            $ext = $isPng ? 'png' : 'pdf';

            $dir = storage_path('app/public/fedex/labels');
            if (!is_dir($dir)) mkdir($dir, 0755, true);

            $filename = $baseName . '.' . $ext;
            file_put_contents($dir . '/' . $filename, $body);

            return '/storage/fedex/labels/' . $filename;

        } catch (\Exception $e) { 
            return '/fedex/label/download?url=' . urlencode($fedexUrl) . '&name=' . urlencode($baseName);
        }
    }

    public function tallyExport(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    { 
        $invoiceNumbers = session('tally_current_invoices', []); 
        if (empty($invoiceNumbers) && $request->filled('invoices')) {
            $invoiceNumbers = array_filter(array_map('trim', explode(',', $request->query('invoices'))));
        }

        $filename = 'tally_upload_' . now()->format('Ymd') . '.xlsx';
        return Excel::download(new TallyExport($invoiceNumbers), $filename);
    }

    public function printLabel(Request $request): \Illuminate\View\View
    {
        $url = $request->query('url', '');
        $valid = $url
            && !str_contains($url, '..') 
            && (
                str_starts_with($url, '/storage/fedex/')
                || str_starts_with($url, '/fedex/label/download')
            );

        if (!$valid) {
            abort(400, 'Invalid image URL');
        }

        return view('fedex.print-label', ['url' => $url]);
    }

    public function printInvoice(Request $request): \Illuminate\View\View
    {
        $file = $request->query('file', '');
        if (!$file || str_contains($file, '..')) abort(400, 'Invalid file');
        $url = '/fedex/invoice/view?file=' . urlencode(basename($file));
        return view('fedex.print-invoice', ['url' => $url]);
    }

    public function viewInvoice(Request $request)
    {
        $filename = basename($request->query('file', ''));
        if (!$filename) abort(404);

        $path = storage_path('app/public/fedex/invoices/' . $filename);
        if (!file_exists($path)) abort(404);

        $baseName    = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($filename, PATHINFO_FILENAME));
        $downloadName = $baseName . '_INVOICE.pdf';

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }

    private static function serviceTypes(): array
    {
        return [
            'FEDEX_INTERNATIONAL_PRIORITY'   => 'FedEx International Priority',
            'FEDEX_INTERNATIONAL_ECONOMY'    => 'FedEx International Economy',
            'INTERNATIONAL_PRIORITY'         => 'International Priority',
            'INTERNATIONAL_ECONOMY'          => 'International Economy',
            'INTERNATIONAL_PRIORITY_EXPRESS' => 'International Priority Express',
        ];
    }

    private static function pickupTypes(): array
    {
        return [
            'DROPOFF_AT_FEDEX_LOCATION' => 'Drop-off at FedEx Location',
            'USE_SCHEDULED_PICKUP'      => 'Use Scheduled Pickup',
            'CONTACT_FEDEX_TO_SCHEDULE' => 'Contact FedEx to Schedule',
        ];
    }

    private function overlayLabelText(string $pngPath, string $text): void
    {
        if (!function_exists('imagecreatefrompng') || !file_exists($pngPath)) return;

        $img = @imagecreatefrompng($pngPath);
        if (!$img) return;

        $w = imagesx($img);

        $font = resource_path('fonts/DejaVuSans-Bold.ttf');

        if (file_exists($font) && function_exists('imagettftext')) {
            $ptSize = 24; 
            $b0     = imagettfbbox($ptSize, 0, $font, $text);
            $textW  = abs($b0[2] - $b0[0]);
            $textH  = abs($b0[5] - $b0[1]);
            $ascent = abs($b0[7] - $b0[1]);
            $pad    = 6;
 
            $tmpW = $textW + $pad * 2;
            $tmpH = $textH + $pad * 2;
            $tmp  = imagecreatetruecolor($tmpW, $tmpH);
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            $tTransparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
            imagefilledrectangle($tmp, 0, 0, $tmpW - 1, $tmpH - 1, $tTransparent);
            imagealphablending($tmp, true);
            $tBlack     = imagecolorallocate($tmp, 0, 0, 0);
            $tLightGray = imagecolorallocate($tmp, 200, 200, 200);
            imagerectangle($tmp, 0, 0, $tmpW - 1, $tmpH - 1, $tLightGray);
            imagettftext($tmp, $ptSize, 0, $pad, $pad + $ascent, $tBlack, $font, $text);

            // Step 2: rotate 90° CW so the word runs top-to-bottom along the right edge
            $tRotFill = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
            $rotated  = imagerotate($tmp, -90, $tRotFill);
            imagesavealpha($rotated, true);
            imagedestroy($tmp); 
            $rotW = imagesx($rotated);
            $rotH = imagesy($rotated);
            imagealphablending($img, true);
            imagecopy($img, $rotated, $w - $rotW-9, 0, 0, 0, $rotW, $rotH);
            imagedestroy($rotated);

        } else { 
            $gFont = 5;
            $pad   = 6;
            $boxW  = strlen($text) * imagefontwidth($gFont) + $pad * 2;
            $boxH  = imagefontheight($gFont) + $pad * 2;
            $black = imagecolorallocate($img, 0, 0, 0);
            $white = imagecolorallocate($img, 255, 255, 255);
            imagefilledrectangle($img, $w - $boxW, 0, $w - 1, $boxH, $white);
            imagerectangle($img, $w - $boxW, 0, $w - 1, $boxH, $black);
            imagestring($img, $gFont, $w - $boxW + $pad, $pad, $text, $black);
        }

        imagepng($img, $pngPath);
        imagedestroy($img);
    }
 
    private function generateZplFromPng(string $pngPath, string $dir, string $baseName): ?string
    {
        if (!function_exists('imagecreatefrompng') || !file_exists($pngPath)) return null;

        $img = @imagecreatefrompng($pngPath);
        if (!$img) return null;

        $w = 812; $h = 1218; $topPad = 80; 
        $canvas = imagecreatetruecolor($w, $h);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); 
        imagecopyresampled($canvas, $img, 0, $topPad, 0, 0, $w, $h - $topPad, imagesx($img), imagesy($img));
        imagedestroy($img);
        imagefilter($canvas, \IMG_FILTER_GRAYSCALE);
        imagefilter($canvas, \IMG_FILTER_CONTRAST, -40); // push gray pixels toward black or white
        if (defined('IMG_FILTER_SHARPEN')) {
            imagefilter($canvas, \IMG_FILTER_SHARPEN);
        }

        $rowBytes = (int) ceil($w / 8);
        $hexData  = '';
        for ($y = 0; $y < $h; $y++) {
            for ($xb = 0; $xb < $rowBytes; $xb++) {
                $byte = 0;
                for ($b = 0; $b < 8; $b++) {
                    $px = $xb * 8 + $b;
                    if ($px < $w) {
                        $rgb = imagecolorat($canvas, $px, $y);
                        if ((($rgb >> 16) & 0xFF) < 160) $byte |= (0x80 >> $b);
                    }
                }
                $hexData .= sprintf('%02X', $byte);
            }
        }
        imagedestroy($canvas);

        $totalBytes = $rowBytes * $h;
        $zpl = "^XA\n^PW{$w}\n^LL{$h}\n^FO0,0^GFA,{$totalBytes},{$totalBytes},{$rowBytes},{$hexData}^FS\n^XZ";

        $zplFile = $dir . '/' . $baseName . '.zpl';
        if (file_put_contents($zplFile, $zpl) === false) return null;

        \Log::info('FedEx ZPL generated', ['file' => $baseName . '.zpl', 'bytes' => strlen($zpl)]);
        return '/storage/fedex/labels/' . $baseName . '.zpl';
    }
}
