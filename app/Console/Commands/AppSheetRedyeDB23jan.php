<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Helpers\GoogleSheetHelper;
use Vanguard\Models\AppSheetRedyeDB23janModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppSheetRedyeDB23jan extends Command
{
    protected $signature = 'fetchinredyefromdb23jan:addredyecron';
    protected $description = 'Sync DB redye table data to Google Sheet (Insert new rows by entity_id + Update rows when redye fields change)';

    public function handle()
    {
        try {
            DB::connection('inhousedb23jansql')->getPdo();
        } catch (\Exception $e) {
            $this->error("AppSheetRedyeDB23jan__Remote DB (inhousedb23jansql) connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        $sheetData = GoogleSheetHelper::fetchRedyeDB23jan();

        $sheetMap = [];
        $maxSheetEntityId = 0;

        if (is_array($sheetData) && count($sheetData) > 1) {
            foreach ($sheetData as $index => $row) {
                if ($index === 0) continue;

                $entityId = isset($row[0]) ? (int) $row[0] : null;
                if (!$entityId) continue;

                $sheetMap[$entityId] = [
                    'row_index'                 => $index + 1,
                    'colur_as_per_Image'        => trim($row[12] ?? ''),
                    'saanth_Patta'              => trim($row[13] ?? ''),
                    'color_fastness_to_rubbing' => trim($row[14] ?? ''),
                    'used_fabric_is_okay'       => trim($row[15] ?? ''),
                    'redye_is_require'          => trim($row[16] ?? ''),
                    'dyer_name'                 => trim($row[17] ?? ''),
                    'dye_actual'                => trim($row[18] ?? ''),
                ];

                if ($entityId > $maxSheetEntityId) {
                    $maxSheetEntityId = $entityId;
                }
            }
        }

        $newRows = [];
        $updateRows = [];

        AppSheetRedyeDB23janModel::orderBy('entity_id', 'asc')
            ->chunk(500, function ($logs) use (&$sheetMap, &$maxSheetEntityId, &$newRows, &$updateRows) {

            foreach ($logs as $log) {
                $rowData = [
                    $log->entity_id,
                    $log->foreign_id,
                    $this->formatDateTime($log->created_timestamp),
                    $this->formatDate($log->created_date),
                    $log->unique_id,
                    $log->product_sku,
                    $log->order_number,
                    $log->qty_in_mtr,
                    $log->task,
                    $log->chart_name,
                    $log->page_no,
                    $log->upload_image,
                    $log->colur_as_per_Image,
                    $log->saanth_Patta,
                    $log->color_fastness_to_rubbing,
                    $log->used_fabric_is_okay,
                    $log->redye_is_require,
                    $log->dyer_name,
                    $this->formatDateTime($log->dye_actual),
                    $log->color_no,
                    $log->fabric_quality,
                    $log->dye_quality,
                    $log->approved_by,
                    $log->dev_remark,
                    $log->dye_colur_qc,
                    $log->prabir_remark,
                    $log->fault_of,
                    $this->formatDateTime($log->updated_at),
                ];

                if ($log->entity_id > $maxSheetEntityId) {
                    $newRows[] = $rowData;
                    continue;
                }

                if (isset($sheetMap[$log->entity_id])) {
                    $sheetRow = $sheetMap[$log->entity_id];

                    $changed =
                        trim((string) $log->colur_as_per_Image) !== $sheetRow['colur_as_per_Image'] ||
                        trim((string) $log->saanth_Patta) !== $sheetRow['saanth_Patta'] ||
                        trim((string) $log->color_fastness_to_rubbing) !== $sheetRow['color_fastness_to_rubbing'] ||
                        trim((string) $log->used_fabric_is_okay) !== $sheetRow['used_fabric_is_okay'] ||
                        trim((string) $log->redye_is_require) !== $sheetRow['redye_is_require'] ||
                        trim((string) $log->dyer_name) !== $sheetRow['dyer_name'] ||
                        $this->formatDateTime($log->dye_actual) !== $sheetRow['dye_actual'];

                    if ($changed) {
                        $updateRows[] = [
                            'row_index' => $sheetRow['row_index'],
                            'data' => $rowData,
                        ];
                    }
                }
            }
        });

        if (!empty($newRows)) {
            GoogleSheetHelper::appendToRedyeDB23jan($newRows);
            $this->info(count($newRows) . " new rows inserted into Redye sheet.");
        }
        if (!empty($updateRows)) {
            GoogleSheetHelper::updateRowsInRedyeDB23jan($updateRows);
            $this->info(count($updateRows) . " rows updated into Redye sheet.");
        }
        if (empty($newRows) && empty($updateRows)) {
            $this->info("No changes found.");
        }

        return 0;
    }

    protected function formatDateTime($value)
    {
        return $value ? Carbon::parse($value)->format('m/d/Y H:i:s') : '';
    }

    protected function formatDate($value)
    {
        return $value ? Carbon::parse($value)->format('m/d/Y') : '';
    }
}
