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
        return $this->invoices()->sum('net_payable');
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getCurrentDueAttribute()
    {
        return ($this->previous_due + $this->total_purchased) - $this->total_paid;
    }
}
