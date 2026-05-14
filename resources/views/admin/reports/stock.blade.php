@extends('layouts.admin')

@section('title', 'Stock Report')

@section('content')
{{-- // STANDARD PRINT SYSTEM --}}
{{-- // REPORT PRINT FLOW --}}
{{-- // ERP REPORT PRINT LAYOUT --}}
<div class="container-fluid standard-print-page">
    <div class="row mb-4 d-print-none">
        <div class="col-md-6">
            <h3 class="fw-bold">Inventory Valuation Report</h3>
        </div>
        <div class="col-md-6 text-end d-print-none">
            {{-- // SINGLE TAB PRINT FIX — no new tab, no duplicate pages --}}
            <button type="button" onclick="handlePrintClick(this)" class="btn btn-secondary pe-3">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>

    <div class="standard-print-title-row d-none">
        <div>
            <h1 class="standard-print-title">Inventory Valuation</h1>
            <div>TH Trade Corporation</div>
        </div>
        <div class="standard-print-meta">
            <strong>Printed:</strong> {{ now()->format('d M, Y h:i A') }}<br>
            <strong>Total Items:</strong> {{ method_exists($products, 'total') ? $products->total() : $products->count() }}
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Stock Value (at Cost)</h6>
                    <h2 class="mb-0 fw-bold">৳ {{ number_format($totalValuation, 2) }}</h2>
                    <small class="text-muted">Based on cost_price × stock_quantity</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Low Stock Items</h6>
                    <h2 class="mb-0 fw-bold text-danger">{{ $lowStockCount }}</h2>
                    <small class="text-muted">Products with < 5 units remaining</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Full Inventory List</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Product Name</th>
                            <th>In Stock</th>
                            <th>Cost Price</th>
                            <th>Total Valuation</th>
                            <th class="text-end pe-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr class="{{ $product->stock_quantity < 5 ? 'table-danger-subtle' : '' }}">
                                <td class="ps-3 fw-medium">{{ $product->product_name }}</td>
                                <td>{{ $product->stock_quantity }}</td>
                                <td>৳ {{ number_format($product->cost_price, 2) }}</td>
                                <td class="fw-bold text-primary">
                                    ৳ {{ number_format($product->stock_quantity * $product->cost_price, 2) }}
                                </td>
                                <td class="text-end pe-3">
                                    @if($product->stock_quantity == 0)
                                        <span class="badge bg-dark">Out of Stock</span>
                                    @elseif($product->stock_quantity < 5)
                                        <span class="badge bg-danger">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">Healthy</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($products, 'hasPages') && $products->hasPages())
            <div class="card-footer bg-white d-print-none">
                {{ $products->links() }}
            </div>
        @endif
    </div>
    <div class="standard-print-footer d-none">
        <div class="standard-print-signatures">
            <div class="standard-print-signature">Prepared By</div>
            <div class="standard-print-signature">Authorized Signature</div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // SINGLE TAB PRINT FIX
    // ERP PRINT STANDARDIZATION
    function handlePrintClick(button) {
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Processing...';
        window.setTimeout(function() {
            window.print();
            button.disabled = false;
            button.innerHTML = original;
        }, 120);
    }
</script>
@endsection
