<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class SyncAccTransaction extends Command
{
    protected $signature = 'sync:acc-transaction';

    protected $description =
        'Sync acc_transaction from BioSecurity PostgreSQL to MySQL';

    public function handle()
    {
        $this->info('Mulai sync acc_transaction...');

        try {

            // =====================================
            // CONNECT SERVER C
            // =====================================

            DB::purge('pgsql_biosecurity');

            DB::reconnect('pgsql_biosecurity');

            DB::connection('pgsql_biosecurity')
                ->statement("SET TIME ZONE 'Asia/Jakarta'");


            // =====================================
            // GET LAST CURSOR FROM MYSQL SERVER A
            // =====================================

            $lastRecord = DB::connection('mysql')
                ->table('acc_transaction')
                ->orderByDesc('event_time')
                ->orderByDesc('id')
                ->first(['event_time', 'id']);

            if ($lastRecord) {
                $lastEventTime = $lastRecord->event_time;
                $lastId = $lastRecord->id;
            } else {
                $lastEventTime = '1970-01-01 00:00:00';
                $lastId = '';
            }

            $this->info("Last event_time: {$lastEventTime}");
            $this->info("Last ID: {$lastId}");


            // =====================================
            // GET DATA FROM SERVER C
            // =====================================

            $rows = DB::connection('pgsql_biosecurity')
                ->table('public.acc_transaction')
                ->where(function ($query) use (
                    $lastEventTime,
                    $lastId
                ) {
                    $query->where(
                        'event_time',
                        '>',
                        $lastEventTime
                    )->orWhere(function ($subQuery) use (
                        $lastEventTime,
                        $lastId
                    ) {
                        $subQuery->where(
                            'event_time',
                            '=',
                            $lastEventTime
                        );

                        $subQuery->where(
                            'id',
                            '>',
                            $lastId
                        );
                    });
                })
                ->orderBy('event_time', 'asc')
                ->orderBy('id', 'asc')
                ->limit(500)
                ->get();


            if ($rows->isEmpty()) {

                $this->info('Tidak ada data baru.');

                return Command::SUCCESS;
            }

            $this->info(
                'Total data ditemukan: ' . $rows->count()
            );


            // =====================================
            // PREPARE DATA
            // =====================================

            $insertData = [];

            foreach ($rows as $row) {

                $insertData[] = [

                    'id' => $row->id,

                    'app_id' => $row->app_id,
                    'bio_tbl_id' => $row->bio_tbl_id,
                    'company_id' => $row->company_id,

                    'create_time' =>
                        $this->formatDateTime($row->create_time),

                    'creater_code' => $row->creater_code,
                    'creater_id' => $row->creater_id,
                    'creater_name' => $row->creater_name,

                    'op_version' => $row->op_version,

                    'update_time' =>
                        $this->formatDateTime($row->update_time),

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

                    'event_time' =>
                        $this->formatDateTime($row->event_time),

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

                    'vid_linkage_handle' =>
                        $row->vid_linkage_handle,

                    // Hanya untuk record baru.
                    'is_sent' => 0,
                ];
            }


            // =====================================
            // UPSERT TO MYSQL SERVER A
            // =====================================

            $updateColumns = [
                'app_id',
                'bio_tbl_id',
                'company_id',
                'create_time',
                'creater_code',
                'creater_id',
                'creater_name',
                'op_version',
                'update_time',
                'updater_code',
                'updater_id',
                'updater_name',
                'acc_zone',
                'acc_zone_code',
                'area_name',
                'card_no',
                'dept_code',
                'dept_name',
                'description',
                'dev_alias',
                'dev_id',
                'dev_sn',
                'event_addr',
                'event_name',
                'event_no',
                'event_point_id',
                'event_point_name',
                'event_point_type',
                'event_time',
                'last_name',
                'log_id',
                'mask_flag',
                'name',
                'pin',
                'reader_name',
                'reader_state',
                'temperature',
                'trigger_cond',
                'unique_key',
                'verify_mode_name',
                'verify_mode_no',
                'vid_linkage_handle',
            ];

            // is_sent sengaja tidak dimasukkan ke updateColumns.
            // Agar status pengiriman tidak kembali menjadi 0.

            foreach (array_chunk($insertData, 500) as $chunk) {

                DB::connection('mysql')
                    ->table('acc_transaction')
                    ->upsert(
                        $chunk,
                        ['id'],
                        $updateColumns
                    );
            }


            // =====================================
            // SUCCESS
            // =====================================

            $total = count($insertData);

            $this->info("Sync selesai. Total diproses: {$total}");

            Log::info('Sync acc_transaction berhasil', [
                'total' => $total,
                'last_event_time_before_sync' => $lastEventTime,
                'last_id_before_sync' => $lastId,
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            Log::error('Sync acc_transaction gagal', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $this->error('Sync gagal: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }


    private function formatDateTime($value)
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)
            ->setTimezone('Asia/Jakarta')
            ->format('Y-m-d H:i:s');
    }
}
