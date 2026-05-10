<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Customer extends Model
{
    protected $fillable = [
        'customer_id',
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

    public static function hasCustomerIdColumn(): bool
    {
        // CUSTOMER_ID MIGRATION FIX
        static $hasColumn = null;

        if ($hasColumn === null) {
            $hasColumn = Schema::hasColumn('customers', 'customer_id');
        }

        return $hasColumn;
    }

    public static function safeSelectColumns(array $columns): array
    {
        // SAFE CUSTOMER QUERY
        if (static::hasCustomerIdColumn()) {
            return $columns;
        }

        return array_values(array_filter($columns, fn ($column) => $column !== 'customer_id'));
    }

    public static function displayOrderColumn(): string
    {
        // SAFE CUSTOMER QUERY
        return static::hasCustomerIdColumn() ? 'customer_id' : 'id';
    }

    public static function generateNextCustomerId(): string
    {
        // CUSTOMER_ID MIGRATION FIX
        // CUSTOMER MODULE IMPROVEMENT
        // CUSTOMER ID GENERATOR
        $nextNumber = ((int) static::max('id')) + 1;

        if (! static::hasCustomerIdColumn()) {
            return 'CUS-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        }

        do {
            $customerId = 'CUS-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (static::where('customer_id', $customerId)->exists());

        return $customerId;
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
