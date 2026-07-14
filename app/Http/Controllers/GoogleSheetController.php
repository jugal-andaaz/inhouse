<?php

namespace Vanguard\Http\Controllers;

use Google_Client;
use Google_Service_Sheets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Vanguard\Http\Controllers\Controller;


class GoogleSheetController extends Controller
{
    public function updateSheet($itemId)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/google-service-account.json'));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);

        $service = new Google_Service_Sheets($client);

        $spreadsheetId = '1Pz26iL1S8Qh_7byqPSv41lYWBhekgsq3gCcMDC32ApE';
        $sheetName = 'Sheet4'; // update if different

        // 1. Read existing sheet data

        $range = 'Sheet4!A:Z'; // adjust based on where your `unique_id` lives

        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        foreach ($values as $row) {
            if (isset($row[0]) && $row[0] === 'ANDFS_' . $itemId) {
                return $row;
            }
        }

        return null;

        /*
        $range = "$sheetName!A1:Z1000";
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) return "No data found.";

        $header = array_map('strtolower', $values[0]); // Normalize header
        $uniqueIdIndex = array_search('unique_id', $header);

        if ($uniqueIdIndex === false) return "unique_id column not found.";

        // 2. Fetch from Laravel DB (e.g., 'andaaz_inhouse_new')
        $items = DB::table('andaaz_inhouse_new')->get();

        $updates = [];
        foreach ($items as $item) {
            foreach ($values as $rowIndex => $row) {
                if ($rowIndex === 0) continue; // skip header
                if (isset($row[$uniqueIdIndex]) && $row[$uniqueIdIndex] == $item->unique_id) {
                    // Assuming you want to update column M (13th col) with `product_status`
                    $updates[] = [
                        'range' => "$sheetName!M" . ($rowIndex + 1),
                        'values' => [[$item->product_status]],
                    ];
                }
            }
        }

        // 3. Batch Update
        $body = new \Google_Service_Sheets_BatchUpdateValuesRequest([
            'valueInputOption' => 'USER_ENTERED',
            'data' => $updates,
        ]);

        $service->spreadsheets_values->batchUpdate($spreadsheetId, $body);

        return "Sheet updated successfully.";*/
    }
}
