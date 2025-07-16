<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        $schedule->call(function () {
            \Illuminate\Support\Facades\Log::info('✅ Scheduler is working at ' . now());
            \Illuminate\Support\Facades\Http::get('http://localhost/device-data');
        })->everyMinute();
        
        $schedule->call(function () {
            \Illuminate\Support\Facades\Log::info('Scheduler: send-attendance-data running...');
            \Illuminate\Support\Facades\Http::get('http://localhost/api/send-attendance-data');
        })->everyMinute();

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
