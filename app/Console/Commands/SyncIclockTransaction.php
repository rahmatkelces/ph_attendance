<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Throwable;

class SyncIclockTransaction extends Command
{
    protected $signature = 'sync:iclock-transaction';

    protected $description =
        'Sinkronisasi transaksi baru BioTime ke database lokal';

    public function handle(): int
    {
        try {
            $source = DB::connection('pgsql_biotime');
            $target = DB::connection('mysql');

            $this->info('Mulai sinkronisasi transaksi baru BioTime...');

            $total = 0;
            $inserted = 0;
            $skipped = 0;

            $source->table('iclock_transaction')
                ->whereNotNull('emp_code')
                ->where('emp_code', '<>', '')
                ->whereNotNull('punch_time')
                ->orderBy('id')
                ->chunkById(500, function ($records) use (
                    $target,
                    &$total,
                    &$inserted,
                    &$skipped
                ) {
                    $insertData = [];

                    // Ambil seluruh source_id dalam batch ini.
                    $sourceIds = $records->pluck('id')->all();

                    // Cari transaksi yang sudah ada di MySQL.
                    $existingIds = $target
                        ->table('biotime_transactions')
                        ->whereIn('source_id', $sourceIds)
                        ->pluck('source_id')
                        ->all();

                    $existingIds = array_flip($existingIds);

                    foreach ($records as $record) {

                        $total++;

                        // Lewati transaksi yang sudah ada.
                        if (isset($existingIds[$record->id])) {
                            $skipped++;
                            continue;
                        }

                        // Konversi waktu UTC+8 ke Asia/Jakarta UTC+7.
                        $punchTime = $record->punch_time
                            ? Carbon::parse($record->punch_time)
                                ->setTimezone('Asia/Jakarta')
                                ->format('Y-m-d H:i:s')
                            : null;

                        $uploadTime = $record->upload_time
                            ? Carbon::parse($record->upload_time)
                                ->setTimezone('Asia/Jakarta')
                                ->format('Y-m-d H:i:s')
                            : null;

                        $insertData[] = [
                            'source_id' => $record->id,

                            'emp_code' => $record->emp_code,
                            'punch_time' => $punchTime,
                            'punch_state' => $record->punch_state,
                            'verify_type' => $record->verify_type,
                            'work_code' => $record->work_code,
                            'terminal_sn' => $record->terminal_sn,
                            'terminal_alias' => $record->terminal_alias,
                            'area_alias' => $record->area_alias,
                            'is_attendance' => $record->is_attendance,
                            'source' => $record->source,
                            'purpose' => $record->purpose,
                            'upload_time' => $uploadTime,
                            'emp_id' => $record->emp_id,
                            'terminal_id' => $record->terminal_id,
                            'company_code' => $record->company_code,

                            'is_sent' => 0,

                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    // INSERT saja, tidak melakukan UPDATE.
                    if (!empty($insertData)) {
                        $target
                            ->table('biotime_transactions')
                            ->insertOrIgnore($insertData);

                        $inserted += count($insertData);
                    }

                    $this->info(
                        "Diproses: {$total} | Insert: {$inserted} | Dilewati: {$skipped}"
                    );
                }, 'id');

            $this->info('=================================');
            $this->info('Sinkronisasi BioTime selesai.');
            $this->info("Total diperiksa : {$total}");
            $this->info("Total insert    : {$inserted}");
            $this->info("Total dilewati  : {$skipped}");
            $this->info('=================================');

            Log::info('BioTime new transaction sync completed', [
                'total' => $total,
                'inserted' => $inserted,
                'skipped' => $skipped,
                'completed_at' => now()->toDateTimeString(),
            ]);

            return self::SUCCESS;

        } catch (Throwable $e) {

            Log::error('BioTime transaction sync failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->error('Sync gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
