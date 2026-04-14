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
        // Source of truth: Total Allocated payments
        $totalPaid = $this->allocations()->sum('amount');
        return max(0, $this->net_payable - $totalPaid);
    }
}
