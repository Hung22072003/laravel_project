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
        Schema::create('interactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('post_platform_id');
            $table->integer('number_of_likes');
            $table->integer('number_of_shares');
            $table->integer('number_of_comments');
            $table->date('day');
            
            $table->softDeletes();
            
            $table->foreign('post_platform_id')->references('id')->on('post_platforms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
