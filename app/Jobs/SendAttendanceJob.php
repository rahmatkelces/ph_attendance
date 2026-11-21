<?php

namespace App\Jobs;

use App\Models\AccTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Throwable;

class SendAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $source;
    protected array $recordIds;

    public $tries = 3;
    public $timeout = 120;

    public function __construct(string $source, array $recordIds)
    {
        $this->source = $source;
        $this->recordIds = $recordIds;
    }

    public function handle(): void
    {
        /*
         * 1. Tentukan sumber data
         */
        if ($this->source === 'iclock') {
            $connection = 'mysql';
            $table = 'biotime_transactions';
        } elseif ($this->source === 'acc') {
            $connection = 'mysql';
            $table = 'acc_transaction';
        } else {
            throw new \InvalidArgumentException(
                'Source tidak dikenal: ' . $this->source
            );
        }

        $db = DB::connection($connection);

        /*
         * 2. Ambil record
         */
        $records = $db->table($table)
            ->whereIn('id', $this->recordIds)
            ->where('is_sent', 0)
            ->get();

        if ($records->isEmpty()) {
            Log::info('SendAttendanceJob: tidak ada record', [
                'source' => $this->source,
                'ids' => $this->recordIds,
            ]);

            return;
        }

        /*
         * 3. Mapping payload ESS
         */
        $data = [];
        $sentIds = [];

        foreach ($records as $record) {

            if ($this->source === 'iclock') {

                if (
                    empty(trim((string) $record->emp_code)) ||
                    empty($record->punch_time)
                ) {
                    continue;
                }

                $attendanceTime = Carbon::parse(
                    $record->punch_time
                );

                $nik = (string) $record->emp_code;
                $type = (string) $record->punch_state;

            } else {

                if (
                    empty(trim((string) $record->pin)) ||
                    empty($record->event_time)
                ) {
                    continue;
                }

                $attendanceTime = Carbon::parse(
                    $record->event_time
                );

                $nik = (string) $record->pin;
                $type = (string) $record->reader_state;
            }

            $data[] = [
                'nik' => $nik,
                'date' => $attendanceTime->format('Y-m-d'),
                'time' => $attendanceTime->format('H:i:s'),
                'type_check_in_check_out' => $type,
            ];

            $sentIds[] = $record->id;
        }

        if (empty($data)) {
            Log::warning('SendAttendanceJob: payload kosong', [
                'source' => $this->source,
                'ids' => $this->recordIds,
            ]);

            return;
        }

        /*
         * 4. Validasi konfigurasi API
         */
        $apiUrl = config('attendance.api_url');
        $apiKey = config('attendance.api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            throw new \Exception(
                'Konfigurasi ATTENDANCE_API_URL atau ATTENDANCE_API_KEY kosong'
            );
        }

        /*
         * 5. Kirim ke ESS
         */
        $response = Http::timeout(60)
            ->withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->post($apiUrl, $data);

        /*
         * 6. Simpan log
         */
        DB::connection('mysql')
            ->table('log_jobs')
            ->insert([
                'job_name' => 'send_data_ess',
                'status' => $response->status(),
                'records' => json_encode([
                    'source' => $this->source,
                    'ids' => $sentIds,
                ]),
                'response' => $response->body(),
                'created_at' => now(),
            ]);

        /*
         * 7. Periksa respons API
         */
        if (!$response->successful()) {

            Log::error('SendAttendanceJob gagal', [
                'source' => $this->source,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            throw new \Exception(
                'ESS API gagal HTTP ' .
                $response->status() .
                ': ' . $response->body()
            );
        }

        /*
         * 8. Update status lokal
         */
        $update = [
            'is_sent' => 1,
        ];

        if ($this->source === 'iclock') {
            $update['sent_at'] = now();
        }

        $db->table($table)
            ->whereIn('id', $sentIds)
            ->update($update);

        /*
         * 9. Log sukses
         */
        Log::info('SendAttendanceJob berhasil', [
            'source' => $this->source,
            'total_sent' => count($sentIds),
            'ids' => $sentIds,
            'http_status' => $response->status(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('SendAttendanceJob gagal permanen', [
            'source' => $this->source,
            'record_ids' => $this->recordIds,
            'error' => $exception->getMessage(),
        ]);
    }
}
