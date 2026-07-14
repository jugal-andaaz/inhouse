<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Helpers\GoogleSheetHelper;
use Vanguard\Models\AppSheetFabricAgnstOrderDB23janModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppSheetFabricAgnstOrderDB23jan extends Command
{
    protected $signature = 'fetchinfabricagnstorderfromdb23jan:addfabricagnstordercron';
    protected $description = 'Sync DB data to fabric_agnst_order (Insert + Update based on updated_at)';

    public function handle()
    {
        try {
            DB::connection('inhousedb23jansql')->getPdo();
        } catch (\Exception $e) {
            $this->error("FabricAgnstOrderDB23jan__Remote DB (inhousedb23jansql) connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        $sheetData = GoogleSheetHelper::fetchFabricAgnstOrderDB23jan();

        if (!is_array($sheetData) || count($sheetData) <= 1) {
            $this->info('No valid sheet data found.');
            return 0;
        }
        $sheetMap = [];
        foreach ($sheetData as $index => $row) {
            if ($index === 0) continue;

            $entityId = isset($row[0]) ? $row[0] : null;
            $updatedAt = isset($row[44]) ? $row[44] : null;

            if ($entityId) {
                $sheetMap[$entityId] = [
                    'row_index' => $index + 1,
                    'updated_at' => $updatedAt,
                ];
            }
        }

        $newRows = [];
        $updateRows = [];

        AppSheetFabricAgnstOrderDB23janModel::orderBy('id', 'asc')
            ->chunk(500, function ($logs) use (&$sheetMap, &$newRows, &$updateRows) {

            foreach ($logs as $log) {
                $uniqueid = trim($log->uniqueid);
                $dbUpdatedAt = Carbon::parse($log->updated_at)
                    ->format('m/d/Y H:i:s');

                $rowData = [
                    $uniqueid,
                    $log->timestamp,
                    $log->unique_id,
                    $log->scan_required_fabric,
                    $log->enter_qty_to_deduct,
                    $log->timestamp_qty,
                    $log->bg_timestamp,
                    $log->confirmation_of_bag_processing,
                    $log->checked_by_samim,
                    $log->cbs_timestamp,
                    $log->mens_ladies,
                    $dbUpdatedAt,
                ];
                if (!isset($sheetMap[$uniqueid])) {
                    $newRows[] = $rowData;
                } else {
                    $sheetUpdatedAt = isset($sheetMap[$uniqueid]['updated_at'])
                        ? Carbon::parse(trim($sheetMap[$uniqueid]['updated_at']))
                            ->format('m/d/Y H:i:s')
                        : null;

                    if ($dbUpdatedAt != $sheetUpdatedAt) {
                        $updateRows[] = [
                            'row_index' => $sheetMap[$uniqueid]['row_index'],
                            'data' => $rowData,
                        ];
                    }
                }
            }
        });
        if (!empty($newRows)) {
            GoogleSheetHelper::appendToFabricAgnstOrderDB23jan($newRows);
            $this->info(count($newRows) . " new rows inserted into Fabric_Agnst_Order.");
        }
        if (!empty($updateRows)) {
            GoogleSheetHelper::updateRowsInFabricAgnstOrderDB23jan($updateRows);
            $this->info(count($updateRows) . " rows updated into Fabric_Agnst_Order.");
        }
        if (empty($newRows) && empty($updateRows)) {
            $this->info("No changes found.");
        }
        return 0;
    }
}