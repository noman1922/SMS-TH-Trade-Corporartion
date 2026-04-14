@extends('layouts.admin')

@section('content')
<div class="row g-4 mb-4">
    <!-- Today's Sales -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card h-100 border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="text-muted small text-uppercase fw-bold">Today's Sales</div>
                        <h3 class="mb-0 fw-bold">৳ 45,250.00</h3>
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
                        <h3 class="mb-0 fw-bold text-danger">৳ 12,800.00</h3>
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
                        <h3 class="mb-0 fw-bold">1,240</h3>
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
                        <h3 class="mb-0 fw-bold">850</h3>
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
                        <h3 class="mb-0 fw-bold text-warning">14</h3>
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
                        <h3 class="mb-0 fw-bold">৳ 1.2M</h3>
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

<!-- Recent Transactions Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i> Recent Transactions</span>
        <button class="btn btn-sm btn-primary">View All</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    <tr>
                        <td class="px-4 fw-medium">#INV-20260401</td>
                        <td>Rahim Ahmed</td>
                        <td>৳ 1,500.00</td>
                        <td><span class="badge bg-success">Paid</span></td>
                        <td>14 Apr 2026</td>
                        <td class="text-end px-4">
                            <button class="btn btn-sm btn-outline-secondary">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 fw-medium">#INV-20260402</td>
                        <td>Kamal Hossain</td>
                        <td>৳ 2,800.00</td>
                        <td><span class="badge bg-warning text-dark">Pending</span></td>
                        <td>14 Apr 2026</td>
                        <td class="text-end px-4">
                            <button class="btn btn-sm btn-outline-secondary">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 fw-medium">#INV-20260403</td>
                        <td>Modern Solutions</td>
                        <td>৳ 12,500.00</td>
                        <td><span class="badge bg-danger">Due</span></td>
                        <td>13 Apr 2026</td>
                        <td class="text-end px-4">
                            <button class="btn btn-sm btn-outline-secondary">View</button>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-4 fw-medium">#INV-20260404</td>
                        <td>Sattar Traders</td>
                        <td>৳ 850.00</td>
                        <td><span class="badge bg-success">Paid</span></td>
                        <td>13 Apr 2026</td>
                        <td class="text-end px-4">
                            <button class="btn btn-sm btn-outline-secondary">View</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
