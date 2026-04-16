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
        return $this->invoices_avg_net_payable ? $this->invoices_sum_net_payable : ($this->invoices_sum_net_payable ?? $this->invoices()->sum('net_payable'));
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments_avg_amount ? $this->payments_sum_amount : ($this->payments_sum_amount ?? $this->payments()->sum('amount'));
    }

    public function getCurrentDueAttribute()
    {
        $purchased = $this->invoices_sum_net_payable ?? $this->invoices()->sum('net_payable');
        $paid = $this->payments_sum_amount ?? $this->payments()->sum('amount');
        return ($this->previous_due + $purchased) - $paid;
    }
}
