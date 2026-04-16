<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-2 px-3 px-md-4 shadow-sm d-print-none">
    <div class="container-fluid p-0">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-link text-dark d-lg-none me-2 p-1" id="sidebarToggle" type="button">
            <i class="bi bi-list fs-4"></i>
        </button>

        <span class="navbar-brand fw-bold text-dark d-none d-md-block" style="font-size: 0.95rem;">
            TH TRADE CORPORATION
        </span>
        
        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border d-none d-sm-inline-block">
                <i class="bi bi-person-fill me-1"></i>{{ ucfirst(Auth::user()->role) }}
            </span>
            <div class="dropdown">
                <button class="btn btn-link text-dark text-decoration-none dropdown-toggle fw-semibold" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.875rem;">
                    {{ Auth::user()->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                    <li>
                        <span class="dropdown-item-text text-muted small">
                            Signed in as <strong>{{ ucfirst(Auth::user()->role) }}</strong>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
