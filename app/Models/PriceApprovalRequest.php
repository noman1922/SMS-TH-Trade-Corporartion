<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// PRICE APPROVAL SYSTEM
// STAFF PRICE RESTRICTION
class PriceApprovalRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'reviewed_by',
        'customer_id',
        'product_id',
        'current_price',
        'requested_price',
        'status',
        'reason',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'requested_price' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
