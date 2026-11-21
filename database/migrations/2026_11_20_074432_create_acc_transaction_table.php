<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('acc_transaction', function (Blueprint $table) {

            $table->string('id', 50)->primary();

            $table->string('app_id', 255)->nullable();
            $table->string('bio_tbl_id', 255)->nullable();
            $table->string('company_id', 255)->nullable();

            $table->dateTime('create_time')->nullable();

            $table->string('creater_code', 100)->nullable();
            $table->string('creater_id', 50)->nullable();
            $table->string('creater_name', 100)->nullable();

            $table->integer('op_version')->nullable();

            $table->dateTime('update_time')->nullable();

            $table->string('updater_code', 100)->nullable();
            $table->string('updater_id', 100)->nullable();
            $table->string('updater_name', 100)->nullable();

            $table->string('acc_zone', 30)->nullable();
            $table->string('acc_zone_code', 30)->nullable();

            $table->string('area_name', 100)->nullable();
            $table->string('card_no', 50)->nullable();

            $table->string('dept_code', 100)->nullable();
            $table->string('dept_name', 100)->nullable();

            $table->string('description', 200)->nullable();

            $table->string('dev_alias', 100)->nullable();
            $table->string('dev_id', 255)->nullable();
            $table->string('dev_sn', 30)->nullable();

            $table->smallInteger('event_addr')->nullable();
            $table->string('event_name', 100)->nullable();

            $table->smallInteger('event_no');

            $table->string('event_point_id', 255)->nullable();
            $table->string('event_point_name', 100)->nullable();

            $table->smallInteger('event_point_type')->nullable();

            $table->dateTime('event_time');

            $table->string('last_name', 50)->nullable();
            $table->integer('log_id')->nullable();

            $table->string('mask_flag', 10)->nullable();
            $table->string('name', 50)->nullable();
            $table->string('pin', 30)->nullable();

            $table->string('reader_name', 100)->nullable();
            $table->smallInteger('reader_state')->nullable();

            $table->string('temperature', 50)->nullable();

            $table->smallInteger('trigger_cond')->nullable();

            $table->string('unique_key', 250)->nullable();

            $table->string('verify_mode_name', 100)->nullable();
            $table->smallInteger('verify_mode_no')->nullable();

            $table->string('vid_linkage_handle', 256)->nullable();

            // Tambahan khusus Server A
            $table->boolean('is_sent')->default(false);

            // Index
            $table->index('event_time');
            $table->index('pin');
            $table->index('dev_sn');
            $table->index('is_sent');

        });
    }

    public function down()
    {
        Schema::dropIfExists('acc_transaction');
    }
};
