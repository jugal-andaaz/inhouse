<?php
namespace Vanguard\Console\Commands;

use Illuminate\Console\Command;
use Vanguard\Services\SkeepersReviewService;
use Carbon\Carbon;

class SyncSkeepersReviews extends Command
{
    protected $signature = 'skeepers:sync-reviews';
    protected $description = 'Sync Skeepers product reviews';

    public function handle()
    {
        $from = Carbon::yesterday()->toDateString(); //'2026-02-27';
        $to   = Carbon::yesterday()->toDateString(); //'2026-02-27';
        $count = SkeepersReviewService::fetchAndStore($from, $to);

        $this->info("{$count} reviews synced successfully.");
    }
}
