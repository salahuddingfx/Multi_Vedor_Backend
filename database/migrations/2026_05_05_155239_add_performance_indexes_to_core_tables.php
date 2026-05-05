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
        Schema::table('sites', function (Blueprint $table) {
            $table->index('slug');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->index('site_id');
            $table->index('slug');
            $table->index('is_featured');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->index('site_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('is_featured');
        });
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->index('site_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['site_id']);
            $table->dropIndex(['slug']);
            $table->dropIndex(['is_featured']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['site_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['slug']);
            $table->dropIndex(['is_featured']);
        });
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropIndex(['site_id']);
            $table->dropIndex(['order']);
        });
    }
};
