<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedInteger('max_uses')->nullable()->after('value');
            $table->unsignedInteger('per_user_limit')->nullable()->default(1)->after('max_uses');
            $table->boolean('first_order_only')->default(false)->after('per_user_limit');
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->string('customer_phone');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['coupon_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['max_uses', 'per_user_limit', 'first_order_only']);
        });
    }
};
