<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_name',
        'product_id',
        'model_no',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'category'
    ];

    public function isLowStock()
    {
        return $this->stock_quantity < 5;
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function stockHistories()
    {
        return $this->hasMany(StockHistory::class);
    }
}
