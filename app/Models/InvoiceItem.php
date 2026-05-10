<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'quantity', 'cost_price', 'unit_price', 'total_price'
    ];

    // FINANCIAL CALCULATION FIX
    protected $casts = [
        'cost_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // CUSTOMER PRICE MEMORY
    // Scope to find the latest invoice item for a specific customer+product combination.
    public function scopeForCustomerProduct($query, int $customerId, int $productId)
    {
        return $query->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.customer_id', $customerId)
            ->where('invoice_items.product_id', $productId)
            ->orderByDesc('invoices.date')
            ->orderByDesc('invoices.id')
            ->select('invoice_items.unit_price');
    }
}
