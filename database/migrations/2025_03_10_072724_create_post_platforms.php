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
        Schema::create('post_platforms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('post_id');
            $table->uuid('social_account_id');
            $table->enum('platform', ['TWITTER', 'FACEBOOK', 'REDDIT'])->default('TWITTER');
            $table->enum('status', ['PENDING', 'FAILED', 'SUCCESS'])->default('PENDING');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('social_account_id')->references('id')->on('social_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_platforms');
    }
};
