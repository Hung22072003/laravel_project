<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->nullable();
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->text('failed_job_ids');
            $table->mediumText('options');
            $table->integer('created_at');
            $table->integer('cancelled_at')->nullable();
            $table->integer('finished_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_batches');
    }
};
