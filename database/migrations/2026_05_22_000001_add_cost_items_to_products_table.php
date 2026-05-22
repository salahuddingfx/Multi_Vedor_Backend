<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'cost_items')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('cost_items')->nullable()->after('discount_percentage');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'cost_items')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('cost_items');
            });
        }
    }
};
