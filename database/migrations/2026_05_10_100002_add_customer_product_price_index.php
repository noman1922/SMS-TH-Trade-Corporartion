<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// CUSTOMER PRICE MEMORY
// Performance index for fast customer-product last price lookups.

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // CUSTOMER PRICE MEMORY
        // Index to speed up "latest invoice for customer" queries used by CustomerPricingService.
        DB::statement('CREATE INDEX IF NOT EXISTS idx_invoices_customer_date_id_desc ON invoices (customer_id, date DESC, id DESC)');

        // Index to speed up invoice_items lookups by product within an invoice.
        DB::statement('CREATE INDEX IF NOT EXISTS idx_invoice_items_product_id_invoice_id ON invoice_items (product_id, invoice_id)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS idx_invoice_items_product_id_invoice_id');
        DB::statement('DROP INDEX IF EXISTS idx_invoices_customer_date_id_desc');
    }
};
