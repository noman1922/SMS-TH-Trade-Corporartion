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
        // Invoice indexes for report performance
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('date');
        });

        // Invoice items indexes for profit reports
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index('invoice_id');
            $table->index('product_id');
        });

        // Stock history indexes
        // Schema::table('stock_histories', function (Blueprint $table) {
        //     $table->index('product_id');
        //     $table->index('date');
        // });

        // Additional required indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('product_id');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('mobile');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropIndex(['date']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['product_id']);
        });

        Schema::table('stock_histories', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['date']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['mobile']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });
    }
};
