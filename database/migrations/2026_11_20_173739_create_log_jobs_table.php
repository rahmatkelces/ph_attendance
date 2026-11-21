
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('job_name', 100);
            $table->string('status', 50)->nullable();

            $table->json('records')->nullable();
            $table->longText('response')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index('job_name');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_jobs');
    }
};
