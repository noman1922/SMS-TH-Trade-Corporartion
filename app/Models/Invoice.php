<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no', 'customer_id', 'user_id', 'sub_total', 
        'discount_percent', 'vat_percent', 'ait_percent', 
        'extra_charge', 'net_payable', 'received_amount', 
        'due_amount', 'date'
    ];

    // FINANCIAL CALCULATION FIX
    protected $casts = [
        'sub_total' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'vat_percent' => 'decimal:2',
        'ait_percent' => 'decimal:2',
        'extra_charge' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * Financial Summaries
     */
    public function getRemainingDueAttribute()
    {
        // FINANCIAL CALCULATION FIX
        // CRITICAL ACCOUNTING FIX
        // DUE CALCULATION FIX
        return round(max(0, (float) $this->due_amount), 2);
    }
}
