<?php

namespace App\Jobs;

use App\Models\DeviceAttendanceRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAttendanceRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Cek apakah timestamp valid
        $timestamp = $this->data['timestamp'];

        if ($this->isValidTimestamp($timestamp)) {
            // Create a new attendance record
            DeviceAttendanceRecord::create($this->data);
        }
    }

    // Fungsi untuk memeriksa apakah timestamp valid
    private function isValidTimestamp($timestamp)
    {
        $d = \DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
        return $d && $d->format('Y-m-d H:i:s') === $timestamp;
    }
}

