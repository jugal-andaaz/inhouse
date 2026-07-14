<?php

namespace Vanguard\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class FedExShipmentService
{
    public static function createShipment(array $data): array
    {
        $token         = FedExAuthService::getAccessToken();
        $accountNumber = config('services.fedexftb.account_number');
        $shipper       = config('services.fedexftb.shipper');
        $etdDocumentId = $data['etd_document_id'] ?? config('services.fedexftb.etd_document_id');

        $shipperCountry = config('services.fedexftb.shipper.country', 'IN');
        $isIntl = str_contains(strtoupper($data['service_type'] ?? ''), 'INTERNATIONAL')
               || ($data['recipient_country'] ?? '') !== $shipperCountry;

        $referenceMap = [
            'inv_number'  => ['type' => 'INVOICE_NUMBER',    'max' => 40],
            'po_number'   => ['type' => 'P_O_NUMBER',        'max' => 40],
            'reference'   => ['type' => 'CUSTOMER_REFERENCE','max' => 40],
            'dept_number' => ['type' => 'DEPARTMENT_NUMBER', 'max' => 30],
        ];
        $customerReferences = [];
        foreach ($referenceMap as $field => $spec) {
            if (!empty($data[$field])) {
                $customerReferences[] = [
                    'customerReferenceType' => $spec['type'],
                    'value'                 => substr((string) $data[$field], 0, $spec['max']),
                ];
            }
        }

        $packageItem = [
            'weight' => ['units' => $data['weight_unit'] ?? 'KG', 'value' => (float) $data['weight']],
        ];
        if (!empty($data['length']) && !empty($data['width']) && !empty($data['height'])) {
            $packageItem['dimensions'] = [
                'length' => (int) $data['length'],
                'width'  => (int) $data['width'],
                'height' => (int) $data['height'],
                'units'  => $data['dim_unit'] ?? 'CM',
            ];
        }
        if (!empty($customerReferences)) {
            $packageItem['customerReferences'] = $customerReferences;
        }
        $payload = [
            'labelResponseOptions' => 'URL_ONLY',
            'requestedShipment'    => [
                'shipper' => [
                    'contact' => [
                        'personName'  => $shipper['name'],
                        'phoneNumber' => $shipper['phone'],
                        'companyName' => $shipper['company'],
                    ],
                    'address' => [
                        'streetLines'         => [$shipper['street']],
                        'city'                => $shipper['city'],
                        'stateOrProvinceCode' => $shipper['state'],
                        'postalCode'          => $shipper['postal'],
                        'countryCode'         => $shipper['country'],
                    ],
                    'tins' => [
                        ['number' => config('services.fedexftb.shipper.gstin'), 'tinType' => 'BUSINESS_NATIONAL'],
                    ],
                ],
                'recipients' => [[
                    'contact' => [
                        'personName'  => $data['recipient_name'],
                        'phoneNumber' => $data['recipient_phone'],
                        'companyName' => $data['recipient_company'] ?? $data['recipient_name'],
                    ],
                    'address' => array_filter([
                        'streetLines'         => array_values(array_filter([
                            $data['recipient_street1'],
                            $data['recipient_street2'] ?? null,
                        ])),
                        'city'                => $data['recipient_city'],
                        'stateOrProvinceCode' => ($data['recipient_state'] ?? '') ?: null,
                        'postalCode'          => ($data['recipient_postal'] ?? '') ?: null,
                        'countryCode'         => $data['recipient_country'],
                    ]),
                ]],
                'shipDatestamp'          => $data['ship_date'],
                'serviceType'            => $data['service_type'],
                'packagingType'          => 'FEDEX_PAK',
                'pickupType'             => $data['pickup_type'] ?? 'DROPOFF_AT_FEDEX_LOCATION',
                'totalPackageCount'      => 1,
                'shippingChargesPayment' => ['paymentType' => 'SENDER'],
                'labelSpecification'     => ['imageType' => 'PNG', 'labelStockType' => 'PAPER_4X6'],
                'requestedPackageLineItems' => [$packageItem],
            ],
            'accountNumber' => ['value' => $accountNumber],
        ];

        if ($etdDocumentId) {
            $reordered = [];
            foreach ($payload['requestedShipment'] as $key => $val) {
                $reordered[$key] = $val;
                if ($key === 'labelSpecification') {
                    $reordered['shipmentSpecialServices'] = [
                        'specialServiceTypes' => ['ELECTRONIC_TRADE_DOCUMENTS'],
                        'etdDetail'           => ['attachedDocuments' => [[
                            'documentType'      => 'COMMERCIAL_INVOICE',
                            'documentReference' => !empty($data['inv_number'])
                                ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['inv_number']) . '.pdf'
                                : 'invoice.pdf',
                            'description'       => 'COMMERCIAL_INVOICE',
                            'documentId'        => $etdDocumentId,
                        ]]],
                    ];
                }
            }
            $payload['requestedShipment'] = $reordered;
        }

        if ($isIntl) {
            $currency = $data['currency'] ?? 'INR';
            $weightUnit = $data['weight_unit'] ?? 'KG';
            $totalWeight = (float) $data['weight'];

            if (!empty($data['commodities']) && is_array($data['commodities'])) {
                $commodities       = [];
                $totalCustomsValue = array_sum(array_column($data['commodities'], 'total_value'));

                foreach ($data['commodities'] as $i => $item) {
                    $qty             = (string) ((int) ($item['quantity'] ?? 1));
                    $lineTotal       = (float) ($item['total_value'] ?? 0);
                    $commodityWeight = (float) ($item['weight'] ?? $totalWeight);

                    $commodity = [
                        'description'          => $item['description'] ?? 'Clothing',
                        'countryOfManufacture' => $item['country_mfg'] ?? 'IN',
                        'quantity'             => $qty,
                        'numberOfPieces'       => $qty,
                        'quantityUnits'        => $item['qty_unit'] ?? 'PCS',
                        'unitPrice'            => [
                            'amount'   => (string) ((float) ($item['unit_value'] ?? 0)),
                            'currency' => $currency,
                        ],
                        'customsValue'         => [
                            'amount'   => (string) $lineTotal,
                            'currency' => $currency,
                        ],
                        'weight' => [
                            'units' => $weightUnit,
                            'value' => $commodityWeight,
                        ],
                    ];

                    if (!empty($item['hs_code']) && strlen((string) $item['hs_code']) >= 6) {
                        $commodity['harmonizedCode'] = $item['hs_code'];
                    }

                    $commodities[] = $commodity;
                }
            } else {
                $qty = (string) ((int) ($data['customs_quantity'] ?? 1));
                $declaredValue = (float) ($data['declared_value'] ?? 0);

                $commodity = [
                    'description'          => $data['customs_commodity'] ?? ($data['item_description'] ?? 'Clothing'),
                    'countryOfManufacture' => $data['customs_country_manufacture'] ?? 'IN',
                    'quantity'             => $qty,
                    'numberOfPieces'       => $qty,
                    'quantityUnits'        => $data['customs_qty_unit'] ?? 'PCS',
                    'unitPrice'            => [
                        'amount'   => (string) $declaredValue,
                        'currency' => $currency,
                    ],
                    'customsValue'         => [
                        'amount'   => (string) $declaredValue,
                        'currency' => $currency,
                    ],
                    'weight' => [
                        'units' => $weightUnit,
                        'value' => $totalWeight,
                    ],
                ];

                if (!empty($data['customs_hs_code']) && strlen((string) $data['customs_hs_code']) >= 6) {
                    $commodity['harmonizedCode'] = $data['customs_hs_code'];
                }

                $commodities       = [$commodity];
                $totalCustomsValue = $declaredValue;
            }

            $shipment = [];
            foreach ($payload['requestedShipment'] as $key => $val) {
                if ($key === 'requestedPackageLineItems') {
                    $dutiesPaymentType = (($data['sheet_name'] ?? '') === 'FEDEX') ? 'SENDER' : 'RECIPIENT';
                    $shipment['customsClearanceDetail'] = [
                        'totalCustomsValue' => [
                            'amount'   => $totalCustomsValue,
                            'currency' => $currency,
                        ], 
                        'dutiesPayment'  => ['paymentType' => $dutiesPaymentType],
                        'isDocumentOnly' => false,
                        'commodities'    => $commodities,
                    ];
                }
                $shipment[$key] = $val;
            }
            $payload['requestedShipment'] = $shipment;
        }

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json', 'x-locale' => 'en_US'])
            ->post(config('services.fedexftb.base_url') . '/ship/v1/shipments', $payload);

        $json = $response->json();

        if (!$response->successful() || !empty($json['errors'])) {
            $errors = $json['errors'] ?? [];
            if (!empty($errors)) {
                $messages = array_map(fn($e) => trim(($e['code'] ?? '') . ': ' . ($e['message'] ?? '')), $errors);
                throw new Exception(implode('; ', $messages));
            }
            throw new Exception('FedEx Create Shipment failed (HTTP ' . $response->status() . ')');
        }
 
        $docs = data_get($json, 'output.transactionShipments.0.pieceResponses.0.packageDocuments', []);
        \Log::info('FedEx packageDocuments', ['docs' => array_map(fn($d) => ['contentType' => $d['contentType'] ?? $d['type'] ?? 'N/A', 'url' => substr($d['url'] ?? '', 0, 80)], $docs)]);

        return $json;
    }

    public static function parseTrackingNumber(array $responseJson): ?string
    {
        $shipment = data_get($responseJson, 'output.transactionShipments.0');
        if (!$shipment) return null;

        return data_get($shipment, 'masterTrackingNumber')
            ?? data_get($shipment, 'pieceResponses.0.trackingNumber');
    }

    public static function parsePackageDocumentUrl(array $responseJson, string $contentType): ?string
    {
        $docs = data_get($responseJson, 'output.transactionShipments.0.pieceResponses.0.packageDocuments', []);
        foreach ($docs as $doc) {
            $type = $doc['contentType'] ?? $doc['type'] ?? '';
            if (strtoupper($type) === strtoupper($contentType)) {
                return $doc['url'] ?? null;
            }
        }
        // Fallback: return first doc URL when looking for the primary label
        if (strtoupper($contentType) === 'LABEL' && !empty($docs[0]['url'])) {
            return $docs[0]['url'];
        }
        return null;
    }

    public static function parseLabelUrl(array $responseJson): ?string
    {
        return self::parsePackageDocumentUrl($responseJson, 'LABEL');
    }

    public static function parseAuxiliaryLabelUrl(array $responseJson): ?string
    {
        foreach (['AUXILIARY_LABEL', 'AUXILIARY', 'AUXILIARY_DOCUMENT'] as $type) {
            $url = self::parsePackageDocumentUrl($responseJson, $type);
            if ($url) return $url;
        }
        $docs  = data_get($responseJson, 'output.transactionShipments.0.pieceResponses.0.packageDocuments', []);
        $types = array_map(fn($d) => $d['contentType'] ?? $d['type'] ?? '(unknown)', $docs);
        \Log::info('FedEx AUX not found — available document types', ['types' => $types]);
        return null;
    }

    public static function parseDestinationLocationId(array $responseJson): ?string
    {
        $shipment = data_get($responseJson, 'output.transactionShipments.0');
        return data_get($shipment, 'completedShipmentDetail.operationalDetail.destinationLocationId')
            ?? data_get($shipment, 'operationalDetail.destinationLocationId');
    }
}
