<div class="sidebar bg-dark text-white shadow-sm" style="min-width: 250px; min-height: 100vh;">
    <div class="p-3 border-bottom border-secondary">
        <h5 class="mb-0 text-uppercase fw-bold text-center">Admin Panel</h5>
    </div>
    <ul class="nav flex-column p-2 mt-2">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link text-white py-3 border-bottom border-secondary {{ request()->routeIs('admin.dashboard') ? 'bg-secondary' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-cart me-2"></i> Sales / POS
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-box-seam me-2"></i> Products
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-people me-2"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-stack me-2"></i> Stock
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-receipt me-2"></i> Invoice
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-cash-stack me-2"></i> Due Collection
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-graph-up me-2"></i> Reports
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3 border-bottom border-secondary">
                <i class="bi bi-person-gear me-2"></i> Users
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link text-white py-3">
                <i class="bi bi-gear me-2"></i> Settings
            </a>
        </li>
    </ul>
</div>
