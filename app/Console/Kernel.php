<?php

namespace App\Console;

use App\Jobs\SendAttendanceJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Sinkronisasi BioTime setiap jam
        $schedule->command('sync:iclock-transaction')
            ->hourly()
            ->withoutOverlapping();

        // Sinkronisasi ACC setiap jam
        $schedule->command('sync:acc-transaction')
            ->hourly()
            ->withoutOverlapping();

        // Pengiriman data ESS setiap 5 menit
        $schedule->call(function () {

            /*
             * BIOTIME
             */
            $iclockIds = DB::connection('mysql')
                ->table('biotime_transactions')
                ->where('is_sent', 0)
                ->whereNotNull('emp_code')
                ->where('emp_code', '<>', '')
                ->whereNotNull('punch_time')
                ->orderBy('id')
                ->limit(100)
                ->pluck('id')
                ->toArray();

            if (count($iclockIds) > 0) {
                SendAttendanceJob::dispatch(
                    'iclock',
                    $iclockIds
                )->onQueue('send_data_ess');

                Log::info('Scheduler: BioTime berhasil didispatch', [
                    'total' => count($iclockIds),
                ]);
            }

            /*
             * BIOSECURITY / ACC
             */
            $accIds = DB::connection('mysql')
                ->table('acc_transaction')
                ->where(function ($query) {
                    $query->whereNull('is_sent')
                        ->orWhere('is_sent', 0);
                })
                ->whereNotNull('pin')
                ->whereRaw("TRIM(pin) <> ''")
                ->whereNotNull('event_time')
                ->orderBy('id')
                ->limit(100)
                ->pluck('id')
                ->toArray();

            if (count($accIds) > 0) {
                SendAttendanceJob::dispatch(
                    'acc',
                    $accIds
                )->onQueue('send_data_ess');

                Log::info('Scheduler: ACC berhasil didispatch', [
                    'total' => count($accIds),
                ]);
            }

        })
            ->name('send_data_ess_scheduler')
            ->everyMinute()
            ->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
