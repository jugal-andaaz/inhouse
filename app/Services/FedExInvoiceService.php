<?php

namespace Vanguard\Services;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class FedExInvoiceService
{
    const SHEET_NAME     = 'FEDEX';
    const DATA_START_ROW = 3;
    const SPREADSHEET_ID = '1ak70n-T1-AeJTHpATuRj8gYQ6G3WJgzLb3q2-uRcqyg';
 
    private static array $sheetCache = [];

    private static function getSheetsService(): \Google\Service\Sheets
    {
        $client = new \Google_Client();
        $client->setApplicationName('FedEx Invoice');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        return new \Google\Service\Sheets($client);
    }
 
    private static function indexToCol(int $index): string
    {
        $col = '';
        $n   = $index + 1;
        while ($n > 0) {
            $n--;
            $col = chr(65 + ($n % 26)) . $col;
            $n   = intdiv($n, 26);
        }
        return $col;
    }
 
    private static function getSheetData(string $sheetName): array
    {
        if (isset(self::$sheetCache[$sheetName])) {
            return self::$sheetCache[$sheetName];
        }

        try {
            $service  = self::getSheetsService();
            $response = $service->spreadsheets_values->get(
                self::SPREADSHEET_ID,
                $sheetName . '!A:AZ' 
            );
            $values = $response->getValues() ?? [];
        } catch (\Exception $e) { 
            return self::$sheetCache[$sheetName] = [];
        }

        $data = [];
        foreach ($values as $rowIndex => $row) {
            $rowNum = $rowIndex + 1;
            foreach ($row as $colIndex => $value) {
                $data[$rowNum][self::indexToCol($colIndex)] = $value;
            }
        }

        return self::$sheetCache[$sheetName] = $data;
    }

    private static function cell(array $sheetData, int $row, string $col): string
    {
        return trim((string) ($sheetData[$row][$col] ?? ''));
    }

    public static function fetchByReference(string $ref, array $overrides = []): ?array
    {
        $refNorm = strtolower(trim($ref));

        foreach ([self::SHEET_NAME, 'CSB4'] as $sheetName) {
            $sheetData = self::getSheetData($sheetName);
            if (empty($sheetData)) continue;

            $startRow = $sheetName === 'CSB4' ? 3 : self::DATA_START_ROW;
            $highest  = max(array_keys($sheetData));

            $matchedRows = [];
            for ($row = $startRow; $row <= $highest; $row++) {
                $invNum = strtolower(self::cell($sheetData, $row, 'T'));
                $awbNum = strtolower(self::cell($sheetData, $row, 'O'));

                if ($invNum === $refNorm || $awbNum === $refNorm) {
                    $matchedRows[] = $row;
                }
            }

            if (empty($matchedRows)) continue;

            $data = self::buildData($sheetName, $sheetData, $matchedRows[0], $overrides);

            for ($i = 1; $i < count($matchedRows); $i++) {
                $extra         = self::buildData($sheetName, $sheetData, $matchedRows[$i]);
                $data['items'] = array_merge($data['items'], $extra['items']);
            }

            $data['total_invoice_amount'] = array_sum(
                array_map(fn($item) => (float)($item['quantity'] ?? 0) * (float)($item['unit_value'] ?? 0), $data['items'])
            );
            $data['total_value'] = $data['total_invoice_amount'];

            return $data;
        }

        return null;
    }
 
    public static function fetchFormData(string $ref): ?array
    {
        $refNorm = strtolower(trim($ref));

        foreach ([self::SHEET_NAME, 'CSB4'] as $sheetName) {
            $sheetData = self::getSheetData($sheetName);
            if (empty($sheetData)) continue;

            $startRow = $sheetName === 'CSB4' ? 3 : self::DATA_START_ROW;
            $highest  = max(array_keys($sheetData));

            $matchedRows = [];
            for ($row = $startRow; $row <= $highest; $row++) {
                $invNum = strtolower(self::cell($sheetData, $row, 'T'));
                $awbNum = strtolower(self::cell($sheetData, $row, 'O'));

                if ($invNum === $refNorm || $awbNum === $refNorm) {
                    $matchedRows[] = $row;
                }
            }

            if (empty($matchedRows)) continue;

            $data          = self::buildFormData($sheetData, $matchedRows[0]);
            $commodities   = [];
            $totalDeclared = 0;

            foreach ($matchedRows as $r) {
                $get = fn(string $col): string => self::cell($sheetData, $r, $col);

                $mfgParts  = explode('-', $get('AI'), 2);
                $mfgCode   = strtoupper($mfgParts[0]) ?: 'IN';
                $qty       = (float) ($get('AN') ?: 0);
                $unitVal   = (float) ($get('AP') ?: 0);
                $lineTotal = $qty * $unitVal;
                $totalDeclared += $lineTotal;

                $commodities[] = [
                    'description' => $get('AJ'),
                    'quantity'    => $get('AN'),
                    'qty_unit'    => self::normalizeUom($get('AO')),
                    'hs_code'     => $get('AK'),
                    'country_mfg' => $mfgCode,
                    'unit_value'  => $unitVal,
                    'total_value' => $lineTotal,
                    'weight'      => (float) $get('AQ'),
                ];
            }

            $data['commodities']    = $commodities;
            $data['declared_value'] = $totalDeclared;
            $data['sheet_name']     = $sheetName;

            return $data;
        }

        return null;
    }

    public static function listAllRows(?string $sheetFilter = null): array
    {
        $rows = [];
        $seen = [];

        $all = [self::SHEET_NAME => self::DATA_START_ROW, 'CSB4' => 3];
        $map = $sheetFilter ? array_intersect_key($all, [$sheetFilter => true]) : $all;

        foreach ($map as $sheetName => $startRow) {
            $sheetData = self::getSheetData($sheetName);
            if (empty($sheetData)) continue;

            $highest = max(array_keys($sheetData));

            for ($row = $startRow; $row <= $highest; $row++) {
                $invNum = self::cell($sheetData, $row, 'T');
                if ($invNum === '') continue;

                if (isset($seen[$invNum])) continue;
                $seen[$invNum] = true;

                $countryRaw = self::cell($sheetData, $row, 'G');
                $rows[] = [
                    'invoice_number' => $invNum,
                    'awb'            => self::cell($sheetData, $row, 'O'),
                    'recipient'      => self::cell($sheetData, $row, 'B'),
                    'country'        => strtoupper(explode('-', $countryRaw, 2)[0]),
                    'weight'         => self::cell($sheetData, $row, 'W'),
                    'value'          => self::cell($sheetData, $row, 'AG'),
                    'sheet'          => $sheetName,
                ];
            }
        }

        return $rows;
    }

    public static function generateAndSavePdf(string $ref, array $overrides = []): string
    {
        $data = self::fetchByReference($ref, $overrides);
        if (!$data) {
            throw new RuntimeException("No row found for reference: {$ref}");
        }

        $signPath = public_path('images/fedexesign.jpg');
        $data['signature_image'] = file_exists($signPath) ? 'file://' . $signPath : null;

        $dir = storage_path('app/public/fedex/invoices');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['invoice_number'] ?? $ref) . '.pdf';
        file_put_contents($dir . '/' . $filename, self::renderPdf($data));

        return $filename;
    }

    public static function generateForReference(string $ref, array $overrides = []): string
    {
        $data = self::fetchByReference($ref, $overrides);
        if (!$data) {
            throw new RuntimeException("No row found for reference: {$ref}");
        }
        return self::generatePdf($data);
    }

    public static function generatePdf(array $data): string
    {
        $signPath = public_path('images/fedexesign.jpg');
        $data['signature_image'] = file_exists($signPath) ? 'file://' . $signPath : null;

        $tmpPath = sys_get_temp_dir() . '/fedex-invoice-' . uniqid() . '.pdf';
        file_put_contents($tmpPath, self::renderPdf($data));

        return $tmpPath;
    }

    private static function buildData(string $sheetName, array $sheetData, int $row, array $overrides = []): array
    {
        $get = fn(string $col): string => self::cell($sheetData, $row, $col);

        $currencyRaw   = $get('AH');
        $currParts     = explode('-', $currencyRaw);
        $currencyCode  = strtoupper(end($currParts));
        $currencyWords = strtoupper(count($currParts) > 1
            ? implode('-', array_slice($currParts, 0, -1))
            : self::currencyWords($currencyCode));

        $countryRaw  = $get('G');
        $countryCode = strtoupper(explode('-', $countryRaw, 2)[0]);

        $mfgRaw     = $get('AI');
        $mfgParts   = explode('-', $mfgRaw, 2);
        $countryMfg = strtoupper($mfgParts[1] ?? $mfgParts[0]);
        if (empty($countryMfg)) $countryMfg = 'INDIA';

        $city     = $get('H');
        $state    = $get('I');
        $postal   = $get('J');
        $cityLine = implode(', ', array_filter([$city, $state, $postal, $countryCode]));

        $invoiceDate = self::parseDate($get('U'));
        $totalValue  = (float) ($get('AG') ?: 0);
        $unitValue   = (float) ($get('AP') ?: 0);
        $weight      = $get('W');

        $data = [
            'sheet_name'           => $sheetName,
            'awb_number'           => $get('O'),
            'invoice_number'       => $get('T'),
            'export_reference'     => $get('O') ?: $get('T'),
            'date_of_export'       => $invoiceDate,
            'invoice_date'         => $invoiceDate,
            'recipient_name'       => $get('B'),
            'recipient_address1'   => $get('D'),
            'recipient_address2'   => $get('E'),
            'recipient_city_state' => $cityLine,
            'recipient_country'    => $countryCode,
            'recipient_email'      => $get('N'),
            'recipient_phone'      => $get('K'),
            'recipient_vat'        => '',
            'incoterm'             => $get('AV') ?: 'CFR',
            'notes'                => $get('AT'),
            'currency'             => $currencyCode ?: 'INR',
            'currency_words'       => $currencyWords ?: 'INDIAN RUPEE',
            'total_packages'       => (string) max(1, (int) $get('V')),
            'total_weight'         => $weight,
            'total_value'          => $totalValue,
            'total_invoice_amount' => $totalValue,
            'freight_charges'      => (float)($get('AA') ?: 0),
            'insurance_charges'    => (float)($get('AB') ?: 0),
            'items' => [[
                'description'        => $get('AJ'),
                'state_of_origin'    => strtoupper($get('AL')),
                'district_of_origin' => strtoupper($get('AM')),
                'hs_code'            => $get('AK'),
                'country_of_mfg'     => $countryMfg,
                'net_weight'         => $get('AQ') ?: $weight,
                'quantity'           => $get('AN'),
                'uom'                => strtoupper($get('AO')) ?: 'PCS',
                'unit_value'         => $unitValue,
                'total_value'        => $totalValue,
                'igst_pct'           => '',
                'igst_amt'           => '',
            ]],
        ];

        return array_merge($data, $overrides);
    }

    private static function buildFormData(array $sheetData, int $row): array
    {
        $get = fn(string $col): string => self::cell($sheetData, $row, $col);

        $countryCode = strtoupper(explode('-', $get('G'), 2)[0]);
        $stateParts  = explode('-', $get('I'), 2);
        $stateCode   = strtoupper($stateParts[0]);

        $currencyRaw  = $get('AH');
        $currParts    = explode('-', $currencyRaw);
        $currencyCode = strtoupper(end($currParts));

        $mfgCode = strtoupper(explode('-', $get('AI'), 2)[0]);
        $country = $countryCode ?: 'US';

        return [
            'inv_number'                  => $get('T'),
            'reference'                   => $get('O'),
            'po_number'                   => $get('AX'),
            'dept_number'                 => $get('AW'),
            'recipient_name'              => $get('B'),
            'recipient_company'           => $get('C'),
            'recipient_phone'             => $get('K'),
            'recipient_email'             => $get('N'),
            'recipient_street1'           => $get('D'),
            'recipient_street2'           => $get('E'),
            'recipient_city'              => $get('H'),
            'recipient_state'             => $stateCode,
            'recipient_postal'            => self::formatPostalCode($get('J'), $country),
            'recipient_country'           => $country,
            'weight'                      => $get('W'),
            'length'                      => $get('X'),
            'width'                       => $get('Y'),
            'height'                      => $get('Z'),
            'declared_value'              => (float)($get('AN') ?: 0) * (float)($get('AP') ?: 0),
            'currency'                    => $currencyCode ?: 'USD',
            'customs_commodity'           => $get('AJ'),
            'customs_quantity'            => $get('AN'),
            'customs_qty_unit'            => self::normalizeUom($get('AO')),
            'customs_hs_code'             => $get('AK'),
            'customs_country_manufacture' => $mfgCode ?: 'IN',
            // Extra fields for Tally upload export
            'invoice_date_raw'            => trim($get('U')),
            'total_packages'              => (string) max(1, (int) $get('V')),
            'freight_charges'             => (float)($get('AA') ?: 0),
            'insurance_charges'           => (float)($get('AB') ?: 0),
            'fob_value'                   => (float)($get('AG') ?: 0),
            'incoterm'                    => $get('AV') ?: 'CFR',
        ];
    }

    private static function renderPdf(array $data): string
    {
        $html = view('fedex.invoice-pdf', $data)->render();

        $options = array_merge(app('dompdf.options'), ['isRemoteEnabled' => true]);
        $dompdf  = new \Dompdf\Dompdf($options);
        $dompdf->setBasePath(public_path());
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    private static function parseDate(string $val): string
    {
        $val = trim($val);
        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $val, $m)) {
            try {
                return Carbon::createFromDate('20' . $m[3], $m[2], $m[1])->format('d M y');
            } catch (\Exception $e) {}
        }
        return $val ?: Carbon::today()->format('d M y');
    }

    private static function currencyWords(string $code): string
    {
        return match (strtoupper($code)) {
            'INR'   => 'INDIAN RUPEE',
            'USD'   => 'US DOLLARS',
            'GBP'   => 'POUND STERLING',
            'EUR'   => 'EURO',
            'AED'   => 'UAE DIRHAM',
            'AUD'   => 'AUSTRALIAN DOLLAR',
            default => strtoupper($code),
        };
    }

    private static function formatPostalCode(string $postal, string $countryCode): string
    {
        $postal = trim($postal);
        $padMap = ['US' => 5];
        $len    = $padMap[$countryCode] ?? 0;
        if ($len && ctype_digit($postal) && strlen($postal) < $len) {
            $postal = str_pad($postal, $len, '0', STR_PAD_LEFT);
        }
        return $postal;
    }

    private static function normalizeUom(string $raw): string
    {
        return match (strtoupper(trim($raw))) {
            'SET', 'SETS' => 'SET',
            'DZ', 'DOZEN' => 'DZ',
            'PR', 'PAIR'  => 'PR',
            default       => 'PCS',
        };
    }
}
