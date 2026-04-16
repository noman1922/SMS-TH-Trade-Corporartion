<div class="sidebar" id="sidebar">
    <div class="p-3 border-bottom border-secondary text-center">
        <h5 class="mb-0 text-white fw-bold" style="font-size: 1rem; letter-spacing: 1px;">
            <i class="bi bi-building me-1"></i> TH TRADE CORP
        </h5>
        <small class="text-white-50" style="font-size: 0.7rem;">Stock Management System</small>
    </div>

    <ul class="nav flex-column p-2 mt-2">
        <!-- Main -->
        <li class="sidebar-section">Main Menu</li>
        <li class="nav-item">
            <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('staff.dashboard') }}" class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                <i class="bi bi-cart"></i> Sales / POS
            </a>
        </li>

        <!-- Inventory -->
        <li class="sidebar-section mt-3">Inventory</li>
        @if(auth()->user()->role === 'admin')
        <li class="nav-item">
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> Products
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                <i class="bi bi-stack"></i> Stock
            </a>
        </li>

        <!-- Business -->
        <li class="sidebar-section mt-3">Business</li>
        <li class="nav-item">
            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack"></i> Due Collection
            </a>
        </li>

        <!-- Reports (Admin Only) -->
        @if(auth()->user()->role === 'admin')
        <li class="sidebar-section mt-3">Reports</li>
        <li class="nav-item">
            <a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                <i class="bi bi-bar-chart"></i> Sales Report
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.profit') }}" class="nav-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Profit Report
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.stock') }}" class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data"></i> Stock Report
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.due') }}" class="nav-link {{ request()->routeIs('reports.due') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Due Report
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.ledger') }}" class="nav-link {{ request()->routeIs('reports.ledger') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Customer Ledger
            </a>
        </li>
        @endif
    </ul>
</div>
