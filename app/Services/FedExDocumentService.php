<?php

namespace Vanguard\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Vanguard\Models\FedexInvoiceFbt;
use Vanguard\Services\FedExInvoiceService;

class FedExDocumentService
{ 
    public static function generateAndUpload(string $invoiceRef, string $destinationCountry, array $overrides = []): string
    {
        $existing = FedexInvoiceFbt::where('invoice_number', $invoiceRef)
            ->orWhere('order_reference', $invoiceRef)
            ->first();

        if ($existing) {
            return $existing->document_id;
        }

        $pdfPath = FedExInvoiceService::generateForReference($invoiceRef, $overrides);
        try {
            $documentId = self::uploadEtd($pdfPath, $destinationCountry);
        } finally {
            @unlink($pdfPath);
        } 
        $invoiceNumber = $overrides['invoice_number'] ?? $invoiceRef;
        $orderRef      = $overrides['awb_number'] ?? ($invoiceNumber !== $invoiceRef ? $invoiceRef : null);

        FedexInvoiceFbt::create([
            'invoice_number'      => $invoiceNumber,
            'order_reference'     => $orderRef,
            'document_id'         => $documentId,
            'destination_country' => $destinationCountry,
        ]);

        return $documentId;
    } 
    public static function generateUploadAndSave(string $invoiceRef, string $destinationCountry): array
    {
        $existing = FedexInvoiceFbt::where('invoice_number', $invoiceRef)
            ->orWhere('order_reference', $invoiceRef)
            ->first();

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $invoiceRef) . '.pdf';
        $savedPath = storage_path('app/public/fedex/invoices/' . $filename);

        if ($existing) {
            try {
                FedExInvoiceService::generateAndSavePdf($existing->invoice_number);
            } catch (\Exception $e) {
                // Non-fatal
            }
            return [$existing->document_id, '/fedex/invoice/view?file=' . urlencode($filename)];
        } 
        $savedFilename = FedExInvoiceService::generateAndSavePdf($invoiceRef);
        $diskPath = storage_path('app/public/fedex/invoices/' . $savedFilename);

        $invoicePdfUrl = '/fedex/invoice/view?file=' . urlencode($savedFilename);

        try {
            $documentId = self::uploadEtd($diskPath, $destinationCountry);
        } catch (\Exception $e) {
            \Log::warning('FedEx ETD upload failed, invoice PDF still available: ' . $e->getMessage(), ['invoice' => $invoiceRef]);
            FedexInvoiceFbt::create([
                'invoice_number'      => $invoiceRef,
                'order_reference'     => null,
                'document_id'         => null,
                'destination_country' => $destinationCountry,
            ]);
            return [null, $invoicePdfUrl];
        }

        FedexInvoiceFbt::create([
            'invoice_number'      => $invoiceRef,
            'order_reference'     => null,
            'document_id'         => $documentId,
            'destination_country' => $destinationCountry,
        ]);

        return [$documentId, $invoicePdfUrl];
    }

    public static function uploadEtd(string $filePath, string $destinationCountry): string
    {
        $token     = FedExAuthService::getAccessToken();
        $uploadUrl = config('services.fedexftb.document_url') . '/documents/v1/etds/upload';

        $documentJson = json_encode([
            'workflowName' => 'ETDPreshipment',
            'carrierCode'  => 'FDXE',
            'name'         => basename($filePath),
            'contentType'  => 'application/pdf',
            'meta'         => [
                'shipDocumentType'       => 'COMMERCIAL_INVOICE',
                'originCountryCode'      => config('services.fedexftb.shipper.country', 'IN'),
                'destinationCountryCode' => $destinationCountry,
            ],
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $uploadUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => [
                'document'   => $documentJson,
                'attachment' => new \CURLFile($filePath, 'application/pdf', basename($filePath)),
            ],
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
            ],
        ]);

        $responseBody = curl_exec($curl);
        $curlError    = curl_error($curl);
        $httpCode     = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($responseBody === false) {
            throw new Exception('FedEx ETD upload curl error: ' . $curlError);
        }

        $json = json_decode($responseBody, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $fedexErrors = $json['errors'] ?? [];
            if (!empty($fedexErrors)) {
                $messages = array_map(fn($e) => trim(($e['code'] ?? '') . ': ' . ($e['message'] ?? '')), $fedexErrors);
                throw new Exception(implode('; ', $messages));
            }
            throw new Exception('FedEx ETD upload failed (HTTP ' . $httpCode . ')');
        }

        $documentId = data_get($json, 'output.documentId')
                   ?? data_get($json, 'output.meta.docId')
                   ?? data_get($json, 'documentId');

        if (empty($documentId)) {
            throw new Exception('FedEx ETD upload: documentId missing in response: ' . $responseBody);
        }

        return $documentId;
    }
}
