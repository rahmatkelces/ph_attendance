<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();     // contoh: default_ip
            $table->string('name');              // contoh: Default Device IP
            $table->string('value');             // contoh: 10.12.27.240
            $table->boolean('is_active')->default(true);
        });

        DB::table('device_configs')->insert([
            'key' => 'ip_mesin',
            'name' => 'Ip Mesin',
            'value' => '10.11.12.141',
            'is_active' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_configs');
    }
};
