<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// DUE COLLECTION IMPROVEMENT
// DUE HISTORY SYSTEM
// Stores the due amount before and after each collection for accurate receipts/history.

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('previous_due', 15, 2)->nullable()->after('amount');
            $table->decimal('remaining_due', 15, 2)->nullable()->after('previous_due');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['previous_due', 'remaining_due']);
        });
    }
};
