<?php

namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use \Vanguard\Helpers\GoogleSheetHelper;
use Vanguard\Models\AppSheet6FromDB23janModel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppSheet6FromInhouseDB23jan extends Command
{
    /**
     * The name and signature of the console command.App_item_logs
     *
     * @var string
     */
    protected $signature = 'fetchinsheet6fromdb23jan:additemlogcron';
 
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'get Data Into DB and saved in Sheet6';

    /**
     * Execute the console command.
     */
    public function handle()
    { 
        try {
            DB::connection('inhousedb23jansql')->getPdo();
        } catch (\Exception $e) {
            $this->error("Remote DB (inhousedb23jansql) connection failed: " . $e->getMessage());
            return Command::FAILURE;
        }
        
        $sheetData = GoogleSheetHelper::fetchAppSheet6FromStagingInhouseDB23jan();

        if (!is_array($sheetData) || count($sheetData) <= 1) {
            $this->info('No valid sheet data found.');
            return 0;
        }

        $lastRow = end($sheetData);
        $lastEntityId = isset($lastRow[0]) ? (int) $lastRow[0] : 9400;
        $this->info("Last Entity ID in Sheet: " . $lastEntityId);
        $newRows = [];

        AppSheet6FromDB23janModel::where('entity_id', '>', $lastEntityId)
            ->orderBy('entity_id', 'asc')
            ->chunk(500, function ($logs) use (&$newRows) {

            foreach ($logs as $log) {
                $location = $log->location;
                $updatedAt = strval(Carbon::parse($log->updated_at)->format('m/d/Y H:i:s'));                 
                if ($log->doername === 'EOD' && !empty($log->location)) {
                    try {
                        $location = strval(Carbon::parse($log->location)->format('m/d/Y'));
                    } catch (\Exception $e) {
                        $location = $log->location;
                    }
                }
                $newRows[] = [
                    $log->entity_id,
                    $log->unique_id,
                    $log->andaaz_order_id,
                    $log->product_sku,
                    $log->updated_by,
                    $log->doername,
                    $location,
                    $log->sub_loaction,
                    $log->source,
                    $log->type,
                    $updatedAt,
                ];
            } 
        }); 

        if (!empty($newRows)) {
            GoogleSheetHelper::appendToSheet6InhouseDB23jan($newRows);

            $this->info(count($newRows) . " new records added.");
        } else {
            $this->info("No new records found.");
        }
        return 0;
    }
}
