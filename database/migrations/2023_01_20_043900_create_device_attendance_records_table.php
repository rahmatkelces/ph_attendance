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
        Schema::create('device_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('card_no', 10)->default('0000000000');
            $table->string('name', 15)->nullable();
            $table->unsignedBigInteger('uid'); // Kolom untuk user ID
            $table->unsignedBigInteger('employee_id'); // Kolom untuk employee ID
            $table->string('state'); // Kolom untuk state
            $table->timestamp('timestamp'); // Kolom untuk timestamp
            $table->string('type'); // Kolom untuk type
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_attendance_records');
    }
};
