@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 class="fw-bold">Stock Management</h3>
        </div>
        <div class="col-md-6 text-end">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('stock.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Add Stock (Manual IN)
                </a>
            @endif
        </div>
    </div>

    <!-- Current Stock Levels -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0">Current Stock Levels</h5>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('stock.index') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Search product..." value="{{ $search ?? '' }}">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Product ID</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-3 fw-medium">{{ $product->product_id }}</td>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->category }}</td>
                                <td>
                                    <span class="fw-bold {{ $product->isLowStock() ? 'text-danger' : 'text-dark' }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td>
                                    @if($product->stock_quantity == 0)
                                        <span class="badge bg-dark">Out of Stock</span>
                                    @elseif($product->isLowStock())
                                        <span class="badge bg-danger">Low Stock</span>
                                    @else
                                        <span class="badge bg-success">In Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white">
                {{ $products->appends(['history_page' => request('history_page')])->links() }}
            </div>
        @endif
    </div>

    <!-- Stock History Logs -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Stock History (Recent Movements)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reference</th>
                            <th>Note</th>
                            <th class="text-end pe-3">By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $log)
                            <tr>
                                <td class="ps-3 text-muted" style="width: 120px;">
                                    {{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}
                                </td>
                                <td class="fw-medium">{{ $log->product->product_name }}</td>
                                <td>
                                    @if($log->type === 'IN')
                                        <span class="badge bg-success-subtle text-success px-2 py-1">
                                            <i class="bi bi-arrow-down-left me-1"></i> IN
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">
                                            <i class="bi bi-arrow-up-right me-1"></i> OUT
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $log->quantity }}</td>
                                <td>
                                    @if($log->reference_type === 'invoice')
                                        <span class="text-primary fw-bold">INV-{{ $log->reference_id }}</span>
                                    @else
                                        <span class="text-secondary">Manual</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $log->note }}</small></td>
                                <td class="text-end pe-3">
                                    <span class="badge bg-light text-dark border">{{ $log->user->name }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($histories->hasPages())
            <div class="card-footer bg-white">
                {{ $histories->appends(['products_page' => request('products_page')])->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
