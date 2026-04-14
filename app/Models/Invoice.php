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
}
