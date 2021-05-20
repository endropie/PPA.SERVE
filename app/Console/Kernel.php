<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\RecurringCheck::class,
        Commands\TripRun::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule_time = env('APP_SCHEDULE_TIME', '03:00');
        // $schedule->command('recurring:check')
        //     ->dailyAt($schedule_time);

        $schedule->command('trip:run')
            ->hourly()
            ->runInBackground()
            ->withoutOverlapping()
            ->onSuccess(function () {
                Log::info("TRIP: running sucsess[". now() ."].");
            })
            ->onFailure(function () {
                Log::error("TRIP: running failed[". now() ."].");
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
