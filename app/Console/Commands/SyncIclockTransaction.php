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

    protected $description = 'Tarik transaksi BioTime ke database lokal';

    public function handle(): int
    {
        try {
            $source = DB::connection('pgsql_biotime');
            $target = DB::connection('mysql');

            // Ambil ID terakhir yang sudah masuk ke lokal
            $lastSourceId = $target
                ->table('biotime_transactions')
                ->max('source_id') ?? 0;

            $this->info("Mulai sinkronisasi BioTime...");
            $this->info("Source ID terakhir: {$lastSourceId}");

            $total = 0;

            $source->table('iclock_transaction')
                ->where('id', '>', $lastSourceId)
                ->whereNotNull('emp_code')
                ->where('emp_code', '<>', '')
                ->whereNotNull('punch_time')
                ->orderBy('id')
                ->chunkById(1000, function ($records) use (
                    $target,
                    &$total
                ) {
                    $rows = [];

                    foreach ($records as $record) {
                        $rows[] = [
                            'source_id' => $record->id,
                            'emp_code' => $record->emp_code,
                            'punch_time' => $record->punch_time ? Carbon::parse($record->punch_time)->format('Y-m-d H:i:s') : null,
                            'punch_state' => $record->punch_state,
                            'verify_type' => $record->verify_type,
                            'work_code' => $record->work_code,
                            'terminal_sn' => $record->terminal_sn,
                            'terminal_alias' => $record->terminal_alias,
                            'area_alias' => $record->area_alias,
                            'is_attendance' => $record->is_attendance,
                            'source' => $record->source,
                            'purpose' => $record->purpose,
                            'upload_time' => $record->upload_time ? Carbon::parse($record->upload_time)->format('Y-m-d H:i:s') : null,
                            'emp_id' => $record->emp_id,
                            'terminal_id' => $record->terminal_id,
                            'company_code' => $record->company_code,
                            'is_sent' => false,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($rows)) {
                        $target->table('biotime_transactions')
                            ->insertOrIgnore($rows);

                        $total += count($rows);

                        $this->info(
                            'Memproses ' . count($rows) . ' transaksi'
                        );
                    }
                }, 'id');

            $this->info("Selesai. Total transaksi diproses: {$total}");

            Log::info('BioTime transaction sync completed', [
                'total' => $total,
                'last_source_id' => $lastSourceId,
            ]);

            return self::SUCCESS;

        } catch (Throwable $e) {
            Log::error('BioTime transaction sync failed', [
                'message' => $e->getMessage(),
            ]);

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
