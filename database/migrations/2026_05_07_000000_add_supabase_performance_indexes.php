<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // SUPABASE SPEED FIX
        // These indexes match dashboard, POS, due, stock, receipt, and report lookup paths.
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_customer_id_date_idx ON invoices (customer_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_user_id_date_idx ON invoices (user_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_date_created_at_idx ON invoices (date, created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_created_at_idx ON invoices (created_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_due_amount_idx ON invoices (due_amount)');

        DB::statement('CREATE INDEX IF NOT EXISTS invoice_items_invoice_id_product_id_idx ON invoice_items (invoice_id, product_id)');

        DB::statement('CREATE INDEX IF NOT EXISTS payments_customer_id_date_idx ON payments (customer_id, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS payments_created_by_date_idx ON payments (created_by, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS payments_invoice_id_date_idx ON payments (invoice_id, date)');

        DB::statement('CREATE INDEX IF NOT EXISTS products_stock_quantity_idx ON products (stock_quantity)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_product_name_idx ON products (product_name)');

        DB::statement('CREATE INDEX IF NOT EXISTS customers_customer_name_idx ON customers (customer_name)');

        DB::statement('CREATE INDEX IF NOT EXISTS stock_histories_date_idx ON stock_histories (date)');
        DB::statement('CREATE INDEX IF NOT EXISTS stock_histories_created_by_date_idx ON stock_histories (created_by, date)');
        DB::statement('CREATE INDEX IF NOT EXISTS stock_histories_created_at_idx ON stock_histories (created_at)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS stock_histories_created_at_idx');
        DB::statement('DROP INDEX IF EXISTS stock_histories_created_by_date_idx');
        DB::statement('DROP INDEX IF EXISTS stock_histories_date_idx');
        DB::statement('DROP INDEX IF EXISTS customers_customer_name_idx');
        DB::statement('DROP INDEX IF EXISTS products_product_name_idx');
        DB::statement('DROP INDEX IF EXISTS products_stock_quantity_idx');
        DB::statement('DROP INDEX IF EXISTS payments_invoice_id_date_idx');
        DB::statement('DROP INDEX IF EXISTS payments_created_by_date_idx');
        DB::statement('DROP INDEX IF EXISTS payments_customer_id_date_idx');
        DB::statement('DROP INDEX IF EXISTS invoice_items_invoice_id_product_id_idx');
        DB::statement('DROP INDEX IF EXISTS invoices_due_amount_idx');
        DB::statement('DROP INDEX IF EXISTS invoices_created_at_idx');
        DB::statement('DROP INDEX IF EXISTS invoices_date_created_at_idx');
        DB::statement('DROP INDEX IF EXISTS invoices_user_id_date_idx');
        DB::statement('DROP INDEX IF EXISTS invoices_customer_id_date_idx');
    }
};
