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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['IN', 'OUT']);
            $table->integer('quantity');
            $table->string('reference_type'); // 'invoice' or 'manual'
            $table->unsignedBigInteger('reference_id')->nullable(); // invoice_id
            $table->text('note')->nullable();
            $table->date('date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('product_id');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
