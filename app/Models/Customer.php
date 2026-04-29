<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_name',
        'hospital_name',
        'address',
        'mobile',
        'previous_due'
    ];

    // FINANCIAL CALCULATION FIX
    protected $casts = [
        'previous_due' => 'decimal:2',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Financial Summaries
     */
    public function getTotalPurchasedAttribute()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // FINANCIAL CALCULATION FIX
        $total = $this->invoices_sum_net_payable ?? $this->invoices()->sum('net_payable');
        return round((float) $total, 2);
    }

    public function getTotalPaidAttribute()
    {
        // PERFORMANCE OPTIMIZATION
        // QUERY OPTIMIZATION
        // FINANCIAL CALCULATION FIX
        $total = $this->payments_sum_amount ?? $this->payments()->sum('amount');
        return round((float) $total, 2);
    }

    public function getCurrentDueAttribute()
    {
        // FINANCIAL CALCULATION FIX
        $purchased = round((float) ($this->invoices_sum_net_payable ?? $this->invoices()->sum('net_payable')), 2);
        $paid = round((float) ($this->payments_sum_amount ?? $this->payments()->sum('amount')), 2);
        return round(max(0, ((float) $this->previous_due + $purchased) - $paid), 2);
    }
}
