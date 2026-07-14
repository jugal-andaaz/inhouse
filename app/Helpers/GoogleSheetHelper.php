<?php

namespace Vanguard\Helpers;

use Google\Service\Sheets;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;

class GoogleSheetHelper
{   
    protected static function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName('Laravel Sheet Sync');
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig(app_path('google-sheets/appsheet6fromstagingdb.json'));
        $client->setAccessType('offline');

        return $client;
    }

    protected static function getService()
    {
        $client = self::getClient();
        return new Google_Service_Sheets($client);
    }

    public static function fetchEpsAwbNumbers(): array
    {
        $client = new \Google_Client();
        $client->setApplicationName('EPSPL Tracking Sync');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        $service = new \Google\Service\Sheets($client); 

        $spreadsheetId = '1DqOS8jEMMunm8YqrAURJijMXSsXxWdtLmngAyyf73hU';
        $range         = 'EPS!A:G';

        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $rows     = $response->getValues() ?? [];

            \Log::info('EPSPL Sheet total rows fetched: ' . count($rows));

            if (!empty($rows[0])) {
                \Log::info('EPSPL Sheet header row: ' . implode(' | ', $rows[0]));
            }
            foreach (array_slice($rows, 2, 3) as $i => $row) {
                \Log::info("DataRow " . ($i + 2) . ": A=" . ($row[0] ?? 'EMPTY') . " | B(1)=" . ($row[1] ?? 'EMPTY') . " | total_cols=" . count($row));
            }

            $awbNos = [];

            foreach ($rows as $index => $row) {
                if ($index <= 1) continue;

                $awbNo   = trim($row[0]  ?? '');
                $orderId = trim($row[2] ?? '');
                $uniqueId = trim($row[3] ?? '');
                $productSKU = trim($row[4] ?? '');
                $customerEmail = trim($row[6] ?? '');
                $carrier = trim($row[1] ?? '');

                if (strtoupper($carrier) === 'EPS' && !empty($awbNo)) {
                    $awbNos[] = ['awb_no' => $awbNo, 'customer_email' => $customerEmail, 
                                 'unique_id' => $uniqueId, 'product_sku' => $productSKU, 
                                 'order_id' => $orderId ?: null];
                }
            }

            return $awbNos;

        } catch (\Exception $e) {
            \Log::error('EPSPL Google Sheet fetch error: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function fetchSheetDataAndMatchinDB()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Appsheet To Inhouse');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1Pz26iL1S8Qh_7byqPSv41lYWBhekgsq3gCcMDC32ApE';
        $range = 'Sheet4!A:Z';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $values = $response->getValues();
    }
    public static function fetchSheetDataAndMatchingForShippNumberDB()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Appsheet To Inhouse');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1Pz26iL1S8Qh_7byqPSv41lYWBhekgsq3gCcMDC32ApE';
        $range = 'Sheet6!A:D';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $values = $response->getValues();
    }
    public static function fetchSheetDataAndMatch($itemId)
    {
        $client = new \Google_Client();
        $client->setApplicationName('Appsheet To Inhouse');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/credentials.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1Pz26iL1S8Qh_7byqPSv41lYWBhekgsq3gCcMDC32ApE';
        $range = 'Sheet4!A:Z';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        foreach ($values as $row) {
            if (isset($row[0]) && $row[0] === 'ANDFS_' . $itemId) {
                return $row;
            }
        }
        return 0;
    }

    public static function fetchAppSheet6FromStagingInhouseDB23jan()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Appsheet To Inhouse');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/appsheet6fromstagingdb.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1Ajw1uyLErqRJbtMQvgsW6ykytyvz7bjbDr22vxzIz3c';
        $range = 'Sheet1!A:K';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $values = $response->getValues();
    } 
    public static function fetchAppSheet3FromStagingInhouseDB23jan()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Appsheet To Inhouse');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/appsheet6fromstagingdb.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1Ajw1uyLErqRJbtMQvgsW6ykytyvz7bjbDr22vxzIz3c';
        $range         = 'Sheet3!A:AR';
        $maxRetries    = 3;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $service->spreadsheets_values->get($spreadsheetId, $range);
                return $response->getValues();
            } catch (\Google\Service\Exception $e) {
                if ($e->getCode() === 503 && $attempt < $maxRetries) {
                    sleep(5);
                    continue;
                }
                \Illuminate\Support\Facades\Log::warning('fetchAppSheet3 Google 503, skipping.', ['error' => $e->getMessage()]);
                return [];
            }
        }

        return [];
    }

    public static function appendToSheet3InhouseDB23jan($rows)
    {
        $spreadsheetId = '1Ajw1uyLErqRJbtMQvgsW6ykytyvz7bjbDr22vxzIz3c';
        $range = 'Sheet3!A:AR';

        $service = self::getService();
 
        $cleanRows = [];
        foreach ($rows as $row) {
            $cleanRows[] = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row));
        }  
        
        $body = new \Google_Service_Sheets_ValueRange();
        $body->setValues($cleanRows);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );
        return true;
    } 

    public static function updateRowsInSheet3InhouseDB23jan($updateRows)
    {
        $spreadsheetId = '1Ajw1uyLErqRJbtMQvgsW6ykytyvz7bjbDr22vxzIz3c';
        $sheetName = 'Sheet3';
        $service = self::getService();
        $data = [];
        foreach ($updateRows as $row) {
            $rowIndex = $row['row_index'];
            $cleanRow = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row['data']));

            $range = $sheetName . '!A' . $rowIndex;
            $data[] = new \Google_Service_Sheets_ValueRange([
                'range' => $range,
                'values' => [$cleanRow],
            ]);
        }

        if (empty($data)) {
            return true;
        }

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data,
        ]);

        $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

        return true;
    }
    public static function appendToSheet6InhouseDB23jan($rows)
    {
        $spreadsheetId = '1Ajw1uyLErqRJbtMQvgsW6ykytyvz7bjbDr22vxzIz3c';
        $range = 'Sheet1!A:K';

        $service = self::getService();
 
        $cleanRows = [];
        foreach ($rows as $row) {
            $cleanRows[] = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row));
        }
        $body = new \Google_Service_Sheets_ValueRange();
        $body->setValues($cleanRows);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );
        return true;
    }

    public static function sendIndicatorSheetDataAndMatch($itemId)
    {
        $client = new \Google_Client();
        $client->setApplicationName('New_Inhouse_to_Appsheet');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS]);
        $client->setAuthConfig(app_path('google-sheets/credentials-inhousappsheet.json'));

        $client->setHttpClient(new \GuzzleHttp\Client([
            'timeout' => 60,
            'connect_timeout' => 10,
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ]
        ]));

        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1E1liA9d3F1exN9PJu-LwHL0z2sVKS0v9kkr1CQj_iuo';
        $range = 'Measurment_Changes!A:H';

        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();
            return $values ?? [];
        } catch (\Exception $e) {
            \Log::error('Google Sheets API error: '.$e->getMessage());
            return [];
        }
    }
    public static function appendToIndicatorSheet(array $newRowData)
    {
        $client = new \Google_Client();
        $client->setApplicationName('Inhouse_to_Appsheet_Append');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS]); // ✅ NOT READONLY
        $client->setAuthConfig(app_path('google-sheets/credentials-inhousappsheet.json'));

        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1E1liA9d3F1exN9PJu-LwHL0z2sVKS0v9kkr1CQj_iuo';
        $range = 'Measurment_Changes!A:H'; // Adjust if more/less columns

        $body = new \Google\Service\Sheets\ValueRange([
            'values' => [$newRowData]
        ]);

        $params = [
            'valueInputOption' => 'USER_ENTERED' // Accepts formulas and formats
        ];

        $result = $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );

        return $result->getUpdates()->getUpdatedRange(); // Return updated cell range
    }

    public static function fetchFabricAgnstOrderDB23jan()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Test Sheet log');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/appsheet6fromstagingdb.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1UTzLprJNGdXcU1bxwFjK9jIXIPsZpXZoBhwybPo1k4A';
        $range = 'fabric_agnst_order!A:K';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $values = $response->getValues();
    }
    public static function appendToFabricAgnstOrderDB23jan($rows)
    {
        $spreadsheetId = '1UTzLprJNGdXcU1bxwFjK9jIXIPsZpXZoBhwybPo1k4A';
        $range = 'fabric_agnst_order!A:K';

        $service = self::getService();
 
        $cleanRows = [];
        foreach ($rows as $row) {
            $cleanRows[] = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row));
        }  
        
        $body = new \Google_Service_Sheets_ValueRange();
        $body->setValues($cleanRows);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );

        return true;
    }
    public static function updateRowsInFabricAgnstOrderDB23jan($updateRows)
    {
        $spreadsheetId = '1UTzLprJNGdXcU1bxwFjK9jIXIPsZpXZoBhwybPo1k4A';
        $sheetName = 'fabric_agnst_order';
        $service = self::getService();
        $data = [];
        foreach ($updateRows as $row) {
            $rowIndex = $row['row_index'];
            $cleanRow = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row['data']));

            $range = $sheetName . '!A' . $rowIndex;

            $data[] = new \Google_Service_Sheets_ValueRange([
                'range' => $range,
                'values' => [$cleanRow],
            ]);
        }

        if (empty($data)) {
            return true;
        }
        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data,
        ]);
        $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

        return true;
    }
    
    public static function fetchRedyeDB23jan()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Redye Sheet Sync');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/appsheet6fromstagingdb.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1WRNDzI_cmqoVdCdq-3Ccnk80QiAnxlIwV8W6BT_OQ9s';
        $range = 'Sheet1!A:AB';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $values = $response->getValues();
    }

    public static function appendToRedyeDB23jan($rows)
    {
        $spreadsheetId = '1WRNDzI_cmqoVdCdq-3Ccnk80QiAnxlIwV8W6BT_OQ9s';
        $range = 'Sheet1!A:AB';

        $service = self::getService();

        $cleanRows = [];
        foreach ($rows as $row) {
            $cleanRows[] = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row));
        }

        $body = new \Google_Service_Sheets_ValueRange();
        $body->setValues($cleanRows);

        $params = [
            'valueInputOption' => 'RAW'
        ];

        $service->spreadsheets_values->append(
            $spreadsheetId,
            $range,
            $body,
            $params
        );

        return true;
    }

    public static function updateRowsInRedyeDB23jan($updateRows)
    {
        $spreadsheetId = '1WRNDzI_cmqoVdCdq-3Ccnk80QiAnxlIwV8W6BT_OQ9s';
        $sheetName = 'Sheet1';
        $service = self::getService();
        $data = [];

        foreach ($updateRows as $row) {

            $rowIndex = $row['row_index'];

            $cleanRow = array_map(function($v) {
                return $v === null ? '' : $v;
            }, array_values($row['data']));

            $range = $sheetName . '!A' . $rowIndex;

            $data[] = new \Google_Service_Sheets_ValueRange([
                'range' => $range,
                'values' => [$cleanRow],
            ]);
        }

        if (empty($data)) {
            return true;
        }

        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'RAW',
            'data' => $data,
        ]);

        $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

        return true;
    }

    public static function fetchEmpCodeMappingSheet()
    {
        $client = new \Google_Client();
        $client->setApplicationName('Attendance Emp Code Mapping');
        $client->setScopes([\Google\Service\Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig(app_path('google-sheets/appsheet6fromstagingdb.json'));
        $service = new \Google\Service\Sheets($client);

        $spreadsheetId = '1UvFOqFWfxKW8-9vKIZHnlw9wfYd758GOXjd40GCb6Ao';
        $range = 'Sheet11!A:C';

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        return $values = $response->getValues();
    }
}
