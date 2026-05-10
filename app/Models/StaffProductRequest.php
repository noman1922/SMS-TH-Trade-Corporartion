<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// STAFF PRODUCT REQUEST
// PRODUCT APPROVAL FLOW
class StaffProductRequest extends Model
{
    protected $fillable = [
        'requested_by',
        'reviewed_by',
        'product_id',
        'requested_product_name',
        'approved_product_name',
        'generated_product_id',
        'model_no',
        'pack_size',
        'category',
        'requested_price',
        'approved_cost_price',
        'approved_selling_price',
        'status',
        'admin_notes',
        'reviewed_at',
    ];

    protected $casts = [
        'requested_price' => 'decimal:2',
        'approved_cost_price' => 'decimal:2',
        'approved_selling_price' => 'decimal:2',
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
