<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// DISCOUNT TYPE SYSTEM
// Adds discount_type and discount_amount columns to support both percentage and fixed-amount discounts.

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // DISCOUNT TYPE SYSTEM
            $table->string('discount_type', 20)->default('percentage')->after('discount_percent');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_type');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_amount']);
        });
    }
};
