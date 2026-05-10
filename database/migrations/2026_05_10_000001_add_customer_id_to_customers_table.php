<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // CUSTOMER MODULE IMPROVEMENT
            // CUSTOMER ID GENERATOR
            $table->string('customer_id', 20)->nullable()->after('id');
        });

        DB::table('customers')
            ->whereNull('customer_id')
            ->select('id')
            ->chunkById(100, function ($customers) {
                foreach ($customers as $customer) {
                    DB::table('customers')
                        ->where('id', $customer->id)
                        ->update(['customer_id' => 'CUS-' . str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT)]);
                }
            });

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('customer_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['customer_id']);
            $table->dropIndex(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
