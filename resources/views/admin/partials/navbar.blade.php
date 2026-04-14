<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3 px-4 shadow-sm">
    <div class="container-fluid p-0">
        <span class="navbar-brand fw-bold text-dark">TH TRADE CORPORATION</span>
        
        <div class="ms-auto d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-link text-dark text-decoration-none dropdown-toggle fw-semibold" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ Auth::user()->name }} (Admin)
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
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
