<?php

namespace Vanguard\Http\Controllers\Web;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Services\FedExAuthService;
use Vanguard\Services\FedExDocumentService;
use Vanguard\Services\FedExInvoiceService;
use Vanguard\Services\FedExShipmentService;

class FedExShipmentController extends Controller
{
    public function create()
    {
        return view('fedex.create-shipment', [
            'serviceTypes' => self::serviceTypes(),
            'pickupTypes'  => self::pickupTypes(),
            'countries'    => self::countries(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ship_date'                    => 'required|date',
            'service_type'                 => 'required|string',
            'pickup_type'                  => 'required|string',
            'recipient_name'               => 'required|string',
            'recipient_phone'              => 'required|string',
            'recipient_street1'            => 'required|string',
            'recipient_city'               => 'required|string',
            'recipient_postal'             => 'required|string',
            'recipient_country'            => 'required|string|size:2',
            'weight'                       => 'required|numeric|min:0.1',
            'declared_value'               => 'required|numeric|min:0',
            'customs_commodity'            => 'nullable|string|max:255',
            'customs_quantity'             => 'nullable|integer|min:1',
            'customs_qty_unit'             => 'nullable|string',
            'customs_country_manufacture'  => 'nullable|string|size:2',
            'customs_hs_code'              => 'nullable|digits_between:6,12',
            'inv_number'                   => 'nullable|string|max:40',
            'po_number'                    => 'nullable|string|max:40',
            'reference'                    => 'nullable|string|max:40',
            'dept_number'                  => 'nullable|string|max:30',
            'invoice_pdf'                  => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if (!empty($request->length) && !empty($request->width) && !empty($request->height)) {
            $l = (float) $request->length;
            $w = (float) $request->width;
            $h = (float) $request->height;
            $combined = $l + 2 * $h + 2 * $w;
            $max = strtoupper($request->dim_unit ?? 'CM') === 'IN' ? 130 : 330;

            if ($combined > $max) {
                $request->flash();
                return view('fedex.create-shipment', [
                    'serviceTypes' => self::serviceTypes(),
                    'pickupTypes'  => self::pickupTypes(),
                    'countries'    => self::countries(),
                    'error'        => "Dimensions exceed the FedEx limit. Length + Girth = {$combined} {$request->dim_unit} (max {$max} {$request->dim_unit}). Formula: L + (2×H) + (2×W).",
                ]);
            }
        }

        $data = $request->all();

        if ($request->hasFile('invoice_pdf') && $request->file('invoice_pdf')->isValid()) {
            try {
                $tmpPath = $request->file('invoice_pdf')->getRealPath();
                $data['etd_document_id'] = FedExDocumentService::uploadEtd($tmpPath, $data['recipient_country'] ?? 'US');
            } catch (Exception $e) {
                $request->flash();
                return view('fedex.create-shipment', [
                    'serviceTypes' => self::serviceTypes(),
                    'pickupTypes'  => self::pickupTypes(),
                    'countries'    => self::countries(),
                    'error'        => 'ETD document upload failed: ' . $e->getMessage(),
                ]);
            }
        } elseif (!empty($data['inv_number'])) {
            try {
                $data['etd_document_id'] = FedExDocumentService::generateAndUpload(
                    $data['inv_number'],
                    $data['recipient_country'] ?? 'US',
                    ['recipient_name' => $data['recipient_name'] ?? '']
                );
            } catch (Exception $e) {
                Log::warning('FedEx ETD auto-generate failed for inv_number=' . $data['inv_number'] . ': ' . $e->getMessage());
            }
        }

        try {
            $response     = FedExShipmentService::createShipment($data);
            $trackingNo   = FedExShipmentService::parseTrackingNumber($response);
            $labelUrl     = FedExShipmentService::parseLabelUrl($response);
            $invoiceUrl   = FedExShipmentService::parseInvoiceUrl($response);
            $alerts       = data_get($response, 'output.transactionShipments.0.alerts', []);

            return view('fedex.create-shipment', [
                'serviceTypes'  => self::serviceTypes(),
                'pickupTypes'   => self::pickupTypes(),
                'countries'     => self::countries(),
                'success'       => true,
                'trackingNo'    => $trackingNo,
                'labelUrl'      => $labelUrl,
                'invoiceUrl'    => $invoiceUrl,
                'alerts'        => $alerts,
                'rawResponse'   => $response,
            ]);

        } catch (Exception $e) {
            $request->flash();
            return view('fedex.create-shipment', [
                'serviceTypes' => self::serviceTypes(),
                'pickupTypes'  => self::pickupTypes(),
                'countries'    => self::countries(),
                'error'        => $e->getMessage(),
            ]);
        }
    }

    public function quickCreate(Request $request)
    {
        $request->validate([
            'quick_ref'    => 'required|string|max:100',
            'service_type' => 'required|string',
            'pickup_type'  => 'required|string',
            'ship_date'    => 'required|date',
        ]);

        try {
            $data = FedExInvoiceService::fetchFormData(trim($request->quick_ref));
        } catch (\Exception $e) {
            return $this->quickError('Excel read error: ' . $e->getMessage());
        }

        if (!$data) {
            return $this->quickError('No record found in Excel for: ' . $request->quick_ref);
        }

        $data['service_type'] = $request->service_type;
        $data['pickup_type']  = $request->pickup_type;
        $data['ship_date']    = $request->ship_date;
        $data['weight_unit']  = 'KG';

        // Generate invoice PDF + upload ETD (dedup: reuses stored document_id if already uploaded)
        try {
            $data['etd_document_id'] = FedExDocumentService::generateAndUpload(
                $data['inv_number'],
                $data['recipient_country'] ?? 'US'
            );
        } catch (Exception $e) {
            Log::warning('Quick ship ETD failed for ' . $data['inv_number'] . ': ' . $e->getMessage());
        }

        try {
            $response   = FedExShipmentService::createShipment($data);
            $trackingNo = FedExShipmentService::parseTrackingNumber($response);
            $labelUrl   = FedExShipmentService::parseLabelUrl($response);
            $invoiceUrl = FedExShipmentService::parseInvoiceUrl($response);
            $alerts     = data_get($response, 'output.transactionShipments.0.alerts', []);

            return view('fedex.create-shipment', [
                'serviceTypes' => self::serviceTypes(),
                'pickupTypes'  => self::pickupTypes(),
                'countries'    => self::countries(),
                'success'      => true,
                'trackingNo'   => $trackingNo,
                'labelUrl'     => $labelUrl,
                'invoiceUrl'   => $invoiceUrl,
                'alerts'       => $alerts,
                'rawResponse'  => $response,
            ]);
        } catch (Exception $e) {
            return $this->quickError($e->getMessage());
        }
    }

    private function quickError(string $message)
    {
        return view('fedex.create-shipment', [
            'serviceTypes' => self::serviceTypes(),
            'pickupTypes'  => self::pickupTypes(),
            'countries'    => self::countries(),
            'error'        => $message,
        ]);
    }

    public function invoiceLookup(Request $request): JsonResponse
    {
        $ref = trim($request->query('ref', ''));
        if (empty($ref)) {
            return response()->json(['error' => 'ref parameter is required'], 422);
        }

        try {
            $data = FedExInvoiceService::fetchFormData($ref);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        if (!$data) {
            return response()->json(['error' => 'No record found for: ' . $ref], 404);
        }

        return response()->json($data);
    }

    public function downloadLabel(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !str_contains($url, 'fedex.com')) {
            abort(400, 'Invalid label URL.');
        }

        try {
            $token = FedExAuthService::getAccessToken();

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $token],
            ]);

            $body      = curl_exec($curl);
            $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($body === false) {
                abort(502, 'Label fetch error: ' . $curlError);
            }
            if ($httpCode >= 400) {
                abort(502, 'FedEx returned HTTP ' . $httpCode . ' for label URL.');
            }

            $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->query('name', 'fedex-label')); 
            $isPng = strlen($body) > 4 && substr($body, 0, 4) === "\x89PNG";
            $isPdf = strlen($body) > 4 && substr($body, 0, 4) === '%PDF';
            if (!$isPng && !$isPdf) {
                abort(410, 'Label URL has expired or returned invalid content. Please re-run the batch to regenerate.');
            }
            $contentType = $isPng ? 'image/png' : 'application/pdf';
            $ext         = $isPng ? 'png' : 'pdf'; 
            if ($isPng && $request->boolean('print')) {
                $dataUrl = 'data:image/png;base64,' . base64_encode($body);
                $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                    . '@page{size:4in 6in;margin:0}'
                    . '*{margin:0;padding:0;box-sizing:border-box}'
                    . 'body{width:4in;height:6in;overflow:hidden;background:#fff}'
                    . 'img{display:block;width:4in;height:6in;object-fit:contain}'
                    . '</style></head><body>'
                    . '<img src="' . $dataUrl . '">'
                    . '</body></html>';
                return response($html, 200, ['Content-Type' => 'text/html']);
            }

            return response($body, 200, [
                'Content-Type'        => $contentType,
                'Content-Length'      => strlen($body),
                'Content-Disposition' => "inline; filename=\"{$name}.{$ext}\"",
            ]);

        } catch (Exception $e) {
            abort(502, 'Label download error: ' . $e->getMessage());
        }
    }

    private static function serviceTypes(): array
    {
        return [
            'FEDEX_INTERNATIONAL_PRIORITY'    => 'FedEx International Priority',
            'FEDEX_INTERNATIONAL_ECONOMY'     => 'FedEx International Economy',
            'INTERNATIONAL_PRIORITY'          => 'International Priority',
            'INTERNATIONAL_ECONOMY'           => 'International Economy',
            'INTERNATIONAL_PRIORITY_EXPRESS'  => 'International Priority Express',
            'PRIORITY_OVERNIGHT'              => 'Priority Overnight',
            'STANDARD_OVERNIGHT'              => 'Standard Overnight',
            'FEDEX_2_DAY'                     => 'FedEx 2 Day',
            'FEDEX_GROUND'                    => 'FedEx Ground',
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

    private static function countries(): array
    {
        return [
            'IN' => 'India',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'AE' => 'UAE',
            'AU' => 'Australia',
            'CA' => 'Canada',
            'DE' => 'Germany',
            'FR' => 'France',
            'SG' => 'Singapore',
            'NZ' => 'New Zealand',
            'ZA' => 'South Africa',
            'PK' => 'Pakistan',
            'BD' => 'Bangladesh',
            'LK' => 'Sri Lanka',
            'NP' => 'Nepal',
        ];
    }
}
