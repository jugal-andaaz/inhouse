<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Helpers\GoogleSheetHelper;
use Vanguard\Models\AppSheet3FromDB23janModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppSheet3FromInhouseDB23jan extends Command
{
    protected $signature = 'fetchinsheet3fromdb23jan:addstitchlogcron';
    protected $description = 'Sync DB data to Sheet3 (Insert + Update based on updated_at)';

    public function handle()
    {
        try {
            DB::connection('inhousedb23jansql')->getPdo();
        } catch (\Exception $e) {
            $this->error("Remote DB (inhousedb23jansql) connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        $sheetData = GoogleSheetHelper::fetchAppSheet3FromStagingInhouseDB23jan();

        if (!is_array($sheetData) || count($sheetData) <= 1) {
            $this->info('No valid sheet data found.');
            return 0;
        }

        $sheetMap = [];

        foreach ($sheetData as $index => $row) {
            if ($index === 0) continue; // skip header

            $entityId = isset($row[0]) ? (int) $row[0] : null;
            $updatedAt = isset($row[44]) ? $row[44] : null;

            if ($entityId) {
                $sheetMap[$entityId] = [
                    'row_index' => $index + 1, // Google sheet row number
                    'updated_at' => $updatedAt,
                ];
            }
        }

        $newRows = [];
        $updateRows = [];

        AppSheet3FromDB23janModel::orderBy('id', 'asc')
            ->chunk(500, function ($logs) use (&$sheetMap, &$newRows, &$updateRows) {
            foreach ($logs as $log) {
                $dbUpdatedAt = Carbon::parse($log->updated_at)->format('m/d/Y H:i:s'); 
                $rowData = [
                    $log->id,
                    $log->process_id,
                    $log->timestamp,
                    $log->dress_type,
                    $log->unique_Id,
                    $log->increment_id,
                    $log->product_sku,
                    $log->allocate_tailor,
                    $log->emp_id_tailor,
                    $log->fstart,
                    $log->fhold,
                    $log->ftime_consumed,
                    $log->sstart,
                    $log->shold,
                    $log->stime_consumed,
                    $log->thstart,
                    $log->thhold,
                    $log->thtime_consumed,
                    $log->frtstart,
                    $log->frthold,
                    $log->frttime_consumed,
                    $log->fvstart,
                    $log->stitching_finished,
                    $log->nfinishing_doer,
                    $log->tfinishing,
                    $log->final_quality_status,
                    $log->timestamp_pressing_packing,
                    $dbUpdatedAt,
                    $log->enter_stitching_time,
                    $log->uniqueid,
                    $log->enter_stitching_time,
                    $log->fvtime_consumed,
                    $log->total_time,
                ];

                if (!isset($sheetMap[$log->id])) {
                    $newRows[] = $rowData;
                } else {
                    $sheetUpdatedAt = $sheetMap[$log->id]['updated_at'];
                    if ($dbUpdatedAt !== $sheetUpdatedAt) {
                        $updateRows[] = [
                            'row_index' => $sheetMap[$log->id]['row_index'],
                            'data' => $rowData,
                        ];
                    }
                }
            }
        });

        if (!empty($newRows)) {
            GoogleSheetHelper::appendToSheet3InhouseDB23jan($newRows);
            $this->info(count($newRows) . " new rows inserted.");
        }
        if (!empty($updateRows)) {
            GoogleSheetHelper::updateRowsInSheet3InhouseDB23jan($updateRows);
            $this->info(count($updateRows) . " rows updated.");
        }

        if (empty($newRows) && empty($updateRows)) {
            $this->info("No changes found.");
        }

        return 0;
    }
}