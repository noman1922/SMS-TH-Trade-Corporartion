<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// STAFF PRODUCT REQUEST
// PRODUCT APPROVAL FLOW

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_product_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requested_product_name');
            $table->string('approved_product_name')->nullable();
            $table->string('generated_product_id')->nullable();
            $table->string('model_no')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('category')->nullable();
            $table->decimal('requested_price', 15, 2)->default(0);
            $table->decimal('approved_cost_price', 15, 2)->default(0);
            $table->decimal('approved_selling_price', 15, 2)->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_product_requests');
    }
};
