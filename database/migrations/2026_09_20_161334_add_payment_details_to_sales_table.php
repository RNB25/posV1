<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('total_price');
            $table->decimal('pay_amount', 12, 2)->default(0)->after('payment_method');
            $table->decimal('change_amount', 12, 2)->default(0)->after('pay_amount');
            $table->text('payment_note')->nullable()->after('change_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'pay_amount', 'change_amount', 'payment_note']);
        });
    }
};