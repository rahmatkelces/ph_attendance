
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biotime_transactions', function (Blueprint $table) {
            $table->id();

            // ID asli dari BioTime
            $table->unsignedBigInteger('source_id')->unique();

            $table->string('emp_code', 50)->nullable();
            $table->timestamp('punch_time')->nullable();
            $table->string('punch_state', 20)->nullable();
            $table->integer('verify_type')->nullable();
            $table->string('work_code', 50)->nullable();

            $table->string('terminal_sn', 100)->nullable();
            $table->string('terminal_alias', 150)->nullable();
            $table->string('area_alias', 150)->nullable();

            $table->boolean('is_attendance')->default(true);
            $table->integer('source')->nullable();
            $table->integer('purpose')->nullable();

            $table->timestamp('upload_time')->nullable();

            $table->unsignedBigInteger('emp_id')->nullable();
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->string('company_code', 50)->nullable();

            // Status pengiriman ke ESS
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index('is_sent');
            $table->index('punch_time');
            $table->index('emp_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biotime_transactions');
    }
};
