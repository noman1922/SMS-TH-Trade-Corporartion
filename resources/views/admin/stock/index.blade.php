@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 g-3 align-items-center">
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
            <div class="row align-items-center g-3">
                <div class="col-lg">
                    <h5 class="mb-0">Current Stock Levels</h5>
                </div>
                <div class="col-lg-6">
                    {{-- // SEARCH INPUT OPTIMIZATION --}}
                    {{-- // POS SEARCH IMPROVEMENT --}}
                    <form action="{{ route('stock.index') }}" method="GET" class="js-debounce-search" data-debounce="400" data-loading-text="Searching...">
                        <input type="hidden" name="history_page" value="{{ request('history_page') }}">
                        <div class="row g-2 justify-content-lg-end">
                            <div class="col-sm-7">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search name, model, or code..." value="{{ $search ?? '' }}">
                                    <button class="btn btn-outline-secondary" type="submit" aria-label="Search stock products">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <select name="per_page" class="form-select" onchange="this.form.submit()" aria-label="Stock products per page">
                                    @foreach([20, 50, 100] as $size)
                                        <option value="{{ $size }}" @selected($perPage === $size)>Show {{ $size }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive stock-table-wrap">
                <table class="table table-hover align-middle mb-0 stock-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Product ID</th>
                            <th>Product Name</th>
                            <th>Model No</th>
                            <th>Category</th>
                            <th class="text-center">Current Stock</th>
                            <th class="text-end pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4 fw-semibold text-nowrap">{{ $product->product_id }}</td>
                                <td class="fw-semibold text-dark">{{ $product->product_name }}</td>
                                <td>{{ $product->model_no ?? 'N/A' }}</td>
                                <td><span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">{{ $product->category }}</span></td>
                                <td class="text-center">
                                    <span class="fw-bold {{ $product->isLowStock() ? 'text-danger' : 'text-dark' }}">
                                        {{ $product->stock_quantity }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
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
                                <td colspan="6" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white py-3">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 stock-pagination">
                    <div class="text-muted small">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                    </div>
                    <div class="stock-pagination-links">
                        {{ $products->appends(['history_page' => request('history_page')])->links('pagination::bootstrap-5') }}
                    </div>
                </div>
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
            <div class="card-footer bg-white py-3">
                <div class="d-flex justify-content-end stock-pagination">
                    {{ $histories->appends(['products_page' => request('products_page')])->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .stock-table-wrap {
        overflow-x: auto;
    }

    .stock-table {
        min-width: 860px;
    }

    .stock-table thead th {
        white-space: nowrap;
    }

    .stock-table tbody td {
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .stock-pagination .pagination {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.25rem;
        margin-bottom: 0;
    }

    .stock-pagination .page-link {
        min-width: 2.25rem;
        min-height: 2.25rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }

    .stock-pagination .page-item.active .page-link {
        color: #fff;
        box-shadow: 0 0.35rem 0.75rem rgba(59, 130, 246, 0.25);
    }
</style>
@endsection
