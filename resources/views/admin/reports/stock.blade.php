@extends('layouts.admin')

@section('title', 'Stock Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Inventory Valuation Report</h3>
        </div>
        <div class="col-md-6 text-end">
            <button onclick="window.print()" class="btn btn-secondary pe-3">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
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
        @if($products->hasPages())
            <div class="card-footer bg-white d-print-none">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
