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
        // Existing databases may already have some of these foreign-key indexes.
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_customer_id_index ON invoices (customer_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoices_date_index ON invoices (date)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoice_items_invoice_id_index ON invoice_items (invoice_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS invoice_items_product_id_index ON invoice_items (product_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_product_id_index ON products (product_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS customers_mobile_index ON customers (mobile)');
        DB::statement('CREATE INDEX IF NOT EXISTS payments_customer_id_index ON payments (customer_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS invoices_customer_id_index');
        DB::statement('DROP INDEX IF EXISTS invoices_date_index');
        DB::statement('DROP INDEX IF EXISTS invoice_items_invoice_id_index');
        DB::statement('DROP INDEX IF EXISTS invoice_items_product_id_index');
        DB::statement('DROP INDEX IF EXISTS products_product_id_index');
        DB::statement('DROP INDEX IF EXISTS customers_mobile_index');
        DB::statement('DROP INDEX IF EXISTS payments_customer_id_index');
    }
};
