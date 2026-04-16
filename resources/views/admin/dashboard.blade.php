@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <!-- Today's Sales -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Today's Sales</div>
                        <h3 class="mb-0 fw-bold">৳ {{ number_format($todaySales, 2) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 p-3 rounded text-primary">
                            <i class="bi bi-cart-check fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Due -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-danger border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Total Due</div>
                        <h3 class="mb-0 fw-bold text-danger">৳ {{ number_format($totalDue, 2) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 p-3 rounded text-danger">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Customers -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Total Customers</div>
                        <h3 class="mb-0 fw-bold">{{ number_format($totalCustomers) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 p-3 rounded text-success">
                            <i class="bi bi-person-hearts fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Products -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Total Products</div>
                        <h3 class="mb-0 fw-bold">{{ number_format($totalProducts) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 p-3 rounded text-info">
                            <i class="bi bi-box-seam fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Low Stock Alert</div>
                        <h3 class="mb-0 fw-bold text-warning">{{ $lowStockCount }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 p-3 rounded text-warning">
                            <i class="bi bi-exclamation-octagon fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Sales -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-dark border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Monthly Sales</div>
                        <h3 class="mb-0 fw-bold">৳ {{ number_format($monthlySales, 2) }}</h3>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="bg-dark bg-opacity-10 p-3 rounded text-dark">
                            <i class="bi bi-graph-up-arrow fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Transactions Table -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i> Recent Transactions</span>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Invoice ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-end px-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $invoice)
                            <tr>
                                <td class="px-4 fw-medium">{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->customer->customer_name }}</td>
                                <td>৳ {{ number_format($invoice->net_payable, 2) }}</td>
                                <td>
                                    @if($invoice->due_amount <= 0)
                                        <span class="badge bg-success">Paid</span>
                                    @elseif($invoice->received_amount > 0)
                                        <span class="badge bg-warning text-dark">Partial</span>
                                    @else
                                        <span class="badge bg-danger">Due</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d M Y') }}</td>
                                <td class="text-end px-4">
                                    <a href="{{ route('pos.print', $invoice->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No transactions yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> Low Stock Items
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($lowStockProducts as $product)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ $product->product_name }}</div>
                            <small class="text-muted">{{ $product->product_id }}</small>
                        </div>
                        <span class="badge bg-danger rounded-pill">{{ $product->stock_quantity }} left</span>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-check-circle text-success fs-3"></i>
                        <p class="mb-0 mt-2">All stock levels are healthy!</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @if($lowStockCount > 5)
            <div class="card-footer text-center">
                <a href="{{ route('reports.stock') }}" class="text-danger">View all {{ $lowStockCount }} items →</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
