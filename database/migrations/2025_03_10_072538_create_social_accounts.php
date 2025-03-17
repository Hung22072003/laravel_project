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
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('social_user_id', 50)->nullable();
            $table->string('screen_name', 50)->nullable();
            $table->enum('platform', ['TWITTER', 'FACEBOOK', 'REDDIT'])->default('TWITTER');
            $table->text('access_token');
            $table->text('access_token_secret')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->unique(['user_id','platform']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
