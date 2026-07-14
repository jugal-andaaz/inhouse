<?php

namespace Vanguard\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
    \Vanguard\Console\Commands\AppsheetGetData::class,
    \Vanguard\Console\Commands\ItemLogData::class,];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
      $schedule->command('itemlogtoinhous:newitemlogcron')
            ->everyFiveMinutes()
            ->withoutOverlapping(25);

        $schedule->command('appsheettoinhous:updatecron')
            ->everyThreeMinutes()
            ->withoutOverlapping(10);

        $schedule->command('measurement:query-email-cron')
            ->everyFiveMinutes()
            ->withoutOverlapping(8);  

        $schedule->command('trackingdb23jan:appsheetmsmntsheettocron')
            ->everyFiveMinutes()
            ->withoutOverlapping(10);

        $schedule->command('fetchinsheet6fromdb23jan:additemlogcron')
            ->everyTenMinutes()
            ->withoutOverlapping(15);

        $schedule->command('fetchinsheet3fromdb23jan:addstitchlogcron')
            ->everyMinute()
            ->withoutOverlapping(5);
 
        $schedule->command('fetchinfabricagnstorderfromdb23jan:addfabricagnstordercron')
            ->everyMinute()
            ->withoutOverlapping(5);

        $schedule->command('fetchinredyefromdb23jan:addredyecron')
            ->everyThreeMinutes()
            ->withoutOverlapping(8);

        $schedule->command('skeepers:sync-reviews')
            ->cron('0 2 */2 * *')
            ->timezone('Asia/Kolkata')
            ->withoutOverlapping()
            ->onFailure(function () {
                Log::error('Skeepers cron failed');
            });

        $schedule->command('epspl:fetch-tracking')
            ->cron('*/35 * * * *')
            ->withoutOverlapping(35);

        $schedule->command('fedex:fetch-tracking')
            ->hourly()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
