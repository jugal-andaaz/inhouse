<?php

namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Models\TrackingDB23janModel;
use Vanguard\Models\TrackingOrderMsrmt;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrackingInhouseDB23jan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trackingdb23jan:appsheetmsmntsheettocron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data from order_msrmt_appsheet and store into order_msrmt_tracker';

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
        
        $this->info("Tracking Sync Started : ".Carbon::now());

        $lastEntityId = TrackingDB23janModel::max('entity_id');
        $MyTracklastEntityId = TrackingOrderMsrmt::max('entity_id');

        $this->info("Source Last ID : ".$lastEntityId);
        $this->info("Target Last ID : ".$MyTracklastEntityId);

        if (!$MyTracklastEntityId) {
            $MyTracklastEntityId = 0;
        }

        if ($lastEntityId > $MyTracklastEntityId) {

            $this->info("New records found. Sync started...");

            TrackingDB23janModel::where('entity_id', '>', $MyTracklastEntityId)
                ->orderBy('entity_id', 'asc')
                ->chunk(100, function ($rows) {

                    $data = [];

                    foreach ($rows as $row) {
                        $data[] = $row->getAttributes();
                    }

                    TrackingOrderMsrmt::insert($data);

                    $this->info(count($data)." rows inserted...");
                });

            $this->info("Sync completed.");

        } else {

            $this->info("Database already up to date.");
        }

        $this->info("Tracking Sync Completed : ".Carbon::now());

        return 0;
    }
}