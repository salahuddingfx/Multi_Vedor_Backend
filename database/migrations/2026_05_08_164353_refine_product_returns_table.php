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
        Schema::table('product_returns', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('product_returns', 'return_date')) {
                $blueprint->timestamp('return_date')->after('reason')->nullable();
            }
            if (!Schema::hasColumn('product_returns', 'type')) {
                $blueprint->string('type')->after('return_date')->default('return'); // return, damage, etc.
            }
            if (!Schema::hasColumn('product_returns', 'variation_id')) {
                $blueprint->unsignedBigInteger('variation_id')->after('product_id')->nullable();
                $blueprint->foreign('variation_id')->references('id')->on('product_variations')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_returns', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['variation_id']);
            $blueprint->dropColumn(['return_date', 'type', 'variation_id']);
        });
    }
};
