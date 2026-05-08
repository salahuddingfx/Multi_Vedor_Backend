<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('placed', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'placed'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('placed', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled') DEFAULT 'placed'");
    }
};
