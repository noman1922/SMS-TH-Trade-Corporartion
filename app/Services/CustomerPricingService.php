<?php

namespace App\Services;

use App\Models\InvoiceItem;
use App\Models\PriceApprovalRequest;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

// CUSTOMER PRICE MEMORY
// DYNAMIC CUSTOMER PRICING
// Service: Looks up the last selling price for a customer+product pair from invoice history.

class CustomerPricingService
{
    /**
     * Get the last selling price for a specific customer and product.
     *
     * Searches invoice_items joined with invoices to find the most recent
     * unit_price used for this customer+product combination.
     *
     * @param int $customerId
     * @param int $productId
     * @return float|null  The last unit_price, or null if no history exists.
     */
    public function getLastPrice(int $customerId, int $productId): ?float
    {
        // CUSTOMER PRICE MEMORY
        $item = InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.customer_id', $customerId)
            ->where('invoice_items.product_id', $productId)
            ->orderByDesc('invoices.date')
            ->orderByDesc('invoices.id')
            ->orderByDesc('invoice_items.id')
            ->select('invoice_items.unit_price')
            ->first();

        return $item ? round((float) $item->unit_price, 2) : null;
    }

    /**
     * Batch lookup: get last selling prices for multiple products for a single customer.
     * Avoids N+1 by using a single query with window functions (PostgreSQL)
     * or a subquery approach for broader compatibility.
     *
     * @param int   $customerId
     * @param array $productIds
     * @return array  Map of [product_id => last_unit_price]
     */
    public function getLastPricesForProducts(int $customerId, array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        // CUSTOMER PRICE MEMORY
        // Use DISTINCT ON (PostgreSQL) for optimal performance on Supabase.
        // Falls back to subquery approach for other drivers.
        if (DB::getDriverName() === 'pgsql') {
            $rows = DB::table('invoice_items as ii')
                ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
                ->where('i.customer_id', $customerId)
                ->whereIn('ii.product_id', $productIds)
                ->selectRaw('DISTINCT ON (ii.product_id) ii.product_id, ii.unit_price')
                ->orderBy('ii.product_id')
                ->orderByDesc('i.date')
                ->orderByDesc('i.id')
                ->orderByDesc('ii.id')
                ->get()
                ->toArray();
        } else {
            // Fallback: subquery approach for MySQL/SQLite
            $rows = DB::table('invoice_items as ii')
                ->join('invoices as i', 'ii.invoice_id', '=', 'i.id')
                ->where('i.customer_id', $customerId)
                ->whereIn('ii.product_id', $productIds)
                ->whereRaw('ii.id = (
                    SELECT ii2.id FROM invoice_items ii2
                    JOIN invoices i2 ON ii2.invoice_id = i2.id
                    WHERE i2.customer_id = ? AND ii2.product_id = ii.product_id
                    ORDER BY i2.date DESC, i2.id DESC, ii2.id DESC
                    LIMIT 1
                )', [$customerId])
                ->select('ii.product_id', 'ii.unit_price')
                ->get()
                ->toArray();
        }

        $result = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $result[(int) $row['product_id']] = round((float) $row['unit_price'], 2);
        }

        return $result;
    }

    /**
     * Resolve customer-memory prices for multiple products and include defaults
     * where the customer has no previous purchase history.
     *
     * @param int   $customerId
     * @param array $productIds
     * @return array<int, array{price: float, source: string}>
     */
    public function resolvePricesForProducts(int $customerId, array $productIds): array
    {
        // CUSTOMER PRICE MEMORY
        // DYNAMIC CUSTOMER PRICING
        $productIds = array_values(array_unique(array_map('intval', $productIds)));

        if (empty($productIds)) {
            return [];
        }

        $lastPrices = $this->getLastPricesForProducts($customerId, $productIds);
        // PRICE APPROVAL SYSTEM
        $approvedPrices = PriceApprovalRequest::whereIn('product_id', $productIds)
            ->where('status', 'approved')
            ->where(function ($query) use ($customerId) {
                $query->where('customer_id', $customerId)
                    ->orWhereNull('customer_id');
            })
            ->orderByRaw('CASE WHEN customer_id = ? THEN 0 ELSE 1 END', [$customerId])
            ->orderByDesc('reviewed_at')
            ->orderByDesc('id')
            ->get(['product_id', 'requested_price'])
            ->unique('product_id')
            ->keyBy('product_id');
        $defaults = Product::whereIn('id', $productIds)
            ->pluck('selling_price', 'id');

        $resolved = [];
        foreach ($productIds as $productId) {
            if ($approvedPrices->has($productId)) {
                $resolved[$productId] = [
                    'price' => round((float) $approvedPrices[$productId]->requested_price, 2),
                    'source' => 'approved_special_price',
                ];
                continue;
            }

            if (array_key_exists($productId, $lastPrices)) {
                $resolved[$productId] = [
                    'price' => $lastPrices[$productId],
                    'source' => 'customer_history',
                ];
                continue;
            }

            $resolved[$productId] = [
                'price' => round((float) ($defaults[$productId] ?? 0), 2),
                'source' => 'default',
            ];
        }

        return $resolved;
    }

    /**
     * Resolve the best price for a customer+product pair.
     * Returns customer's last price if history exists, otherwise the product default.
     *
     * @param int $customerId
     * @param int $productId
     * @return array{price: float, source: string}
     */
    public function resolvePrice(int $customerId, int $productId): array
    {
        // PRICE APPROVAL SYSTEM
        // STAFF PRICE RESTRICTION
        $approvedPrice = PriceApprovalRequest::where('product_id', $productId)
            ->where('status', 'approved')
            ->where(function ($query) use ($customerId) {
                $query->where('customer_id', $customerId)
                    ->orWhereNull('customer_id');
            })
            ->orderByRaw('CASE WHEN customer_id = ? THEN 0 ELSE 1 END', [$customerId])
            ->latest('reviewed_at')
            ->latest('id')
            ->value('requested_price');

        if ($approvedPrice !== null) {
            return [
                'price' => round((float) $approvedPrice, 2),
                'source' => 'approved_special_price',
            ];
        }

        // DYNAMIC CUSTOMER PRICING
        $lastPrice = $this->getLastPrice($customerId, $productId);

        if ($lastPrice !== null) {
            return [
                'price' => $lastPrice,
                'source' => 'customer_history',
            ];
        }

        // Fallback to product default selling price
        $product = Product::select('selling_price')->find($productId);
        $defaultPrice = $product ? round((float) $product->selling_price, 2) : 0;

        return [
            'price' => $defaultPrice,
            'source' => 'default',
        ];
    }
}
