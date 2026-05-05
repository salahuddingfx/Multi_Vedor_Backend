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
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('developer_credits');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('free_delivery_threshold')->nullable();
            $table->json('social_links')->nullable();
        });

        Schema::create('developer_credits', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
