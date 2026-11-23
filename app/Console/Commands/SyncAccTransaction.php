<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Throwable;

class SyncAccTransaction extends Command
{
    protected $signature = 'sync:acc-transaction';

    protected $description =
        'Sync only new acc_transaction from BioSecurity PostgreSQL to MySQL';

    public function handle(): int
    {
        try {
            $this->info('Mulai sync transaksi baru BioSecurity...');

            DB::purge('pgsql_biosecurity');

            $source = DB::connection('pgsql_biosecurity');
            $target = DB::connection('mysql');

            $source->select('SELECT 1');

            $this->info('Koneksi BioSecurity berhasil.');

            $total = 0;

            // Ambil ID terakhir yang sudah ada di MySQL.
            $lastId = $target->table('acc_transaction')->max('id') ?? 0;

            $this->info("ID terakhir MySQL: {$lastId}");

            // Hanya mengambil transaksi dengan ID lebih besar.
            $source->table('public.acc_transaction')
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->chunkById(500, function ($records) use (
                    $target,
                    &$total
                ) {
                    $insertData = [];

                    foreach ($records as $row) {
                        $insertData[] = [
                            'id' => $row->id,

                            'app_id' => $row->app_id,
                            'bio_tbl_id' => $row->bio_tbl_id,
                            'company_id' => $row->company_id,

                            'create_time' => $this->formatDateTime($row->create_time),
                            'creater_code' => $row->creater_code,
                            'creater_id' => $row->creater_id,
                            'creater_name' => $row->creater_name,

                            'op_version' => $row->op_version,

                            'update_time' => $this->formatDateTime($row->update_time),
                            'updater_code' => $row->updater_code,
                            'updater_id' => $row->updater_id,
                            'updater_name' => $row->updater_name,

                            'acc_zone' => $row->acc_zone,
                            'acc_zone_code' => $row->acc_zone_code,

                            'area_name' => $row->area_name,
                            'card_no' => $row->card_no,

                            'dept_code' => $row->dept_code,
                            'dept_name' => $row->dept_name,

                            'description' => $row->description,

                            'dev_alias' => $row->dev_alias,
                            'dev_id' => $row->dev_id,
                            'dev_sn' => $row->dev_sn,

                            'event_addr' => $row->event_addr,
                            'event_name' => $row->event_name,
                            'event_no' => $row->event_no,

                            'event_point_id' => $row->event_point_id,
                            'event_point_name' => $row->event_point_name,
                            'event_point_type' => $row->event_point_type,

                            'event_time' => $this->formatDateTime($row->event_time),

                            'last_name' => $row->last_name,
                            'log_id' => $row->log_id,

                            'mask_flag' => $row->mask_flag,
                            'name' => $row->name,
                            'pin' => $row->pin,

                            'reader_name' => $row->reader_name,
                            'reader_state' => $row->reader_state,

                            'temperature' => $row->temperature,
                            'trigger_cond' => $row->trigger_cond,

                            'unique_key' => $row->unique_key,

                            'verify_mode_name' => $row->verify_mode_name,
                            'verify_mode_no' => $row->verify_mode_no,

                            'vid_linkage_handle' => $row->vid_linkage_handle,

                            'is_sent' => 0,
                        ];
                    }

                    if (!empty($insertData)) {
                        // INSERT saja, tidak melakukan UPDATE.
                        $target->table('acc_transaction')->insertOrIgnore(
                            $insertData
                        );

                        $count = count($insertData);
                        $total += $count;

                        $this->info(
                            "Memeriksa {$count} transaksi. Total: {$total}"
                        );
                    }
                }, 'id');

            $this->info('=================================');
            $this->info('Sync transaksi baru selesai.');
            $this->info("Total diperiksa: {$total}");
            $this->info('=================================');

            Log::info('BioSecurity new transaction sync completed', [
                'total' => $total,
                'completed_at' => now()->toDateTimeString(),
            ]);

            return self::SUCCESS;

        } catch (Throwable $e) {
            Log::error('Sync acc_transaction gagal', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->error('Sync gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Preserve original PostgreSQL timestamp.
     */
    private function formatDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value, 'Asia/Jakarta')
            ->format('Y-m-d H:i:s');
    }
}
