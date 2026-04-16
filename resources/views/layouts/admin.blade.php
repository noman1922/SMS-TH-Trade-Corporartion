<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - TH Trade Corporation</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-active: #3b82f6;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --body-bg: #f1f5f9;
            --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--body-bg);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            overflow-x: hidden;
            color: #334155;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            width: var(--sidebar-width);
            height: 100vh;
            overflow-y: auto;
            background: var(--sidebar-bg);
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 4px; }

        .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.75rem 1.25rem;
            border-radius: 0.5rem;
            margin: 2px 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: var(--sidebar-hover);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--sidebar-active) !important;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .sidebar .sidebar-section {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #475569;
            padding: 1rem 1.25rem 0.5rem;
            font-weight: 600;
            margin-left: 0.75rem;
        }

        /* ===== MAIN WRAPPER ===== */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ===== CARDS ===== */
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            transition: box-shadow 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-radius: 0.75rem 0.75rem 0 0 !important;
        }

        /* ===== TABLES ===== */
        .table { font-size: 0.875rem; }
        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.875rem 0.75rem;
        }
        .table tbody td {
            vertical-align: middle;
            padding: 0.75rem;
            border-color: #f1f5f9;
        }
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: #f8fafc;
        }
        .table-hover > tbody > tr:hover {
            background-color: #eff6ff !important;
        }

        /* ===== BUTTONS ===== */
        .btn { font-weight: 500; font-size: 0.875rem; border-radius: 0.5rem; }
        .btn-primary { background-color: #3b82f6; border-color: #3b82f6; }
        .btn-primary:hover { background-color: #2563eb; border-color: #2563eb; }
        .btn-success { background-color: #22c55e; border-color: #22c55e; }
        .btn-success:hover { background-color: #16a34a; border-color: #16a34a; }
        .btn-danger { background-color: #ef4444; border-color: #ef4444; }
        .btn-danger:hover { background-color: #dc2626; border-color: #dc2626; }
        .btn-secondary { background-color: #64748b; border-color: #64748b; }

        /* ===== BADGES ===== */
        .badge { font-weight: 500; font-size: 0.75rem; padding: 0.35em 0.65em; border-radius: 0.375rem; }

        /* ===== PAGINATION ===== */
        .pagination { margin-bottom: 0; }
        .page-link {
            color: #3b82f6;
            border-radius: 0.375rem !important;
            margin: 0 2px;
            font-size: 0.875rem;
            border: 1px solid #e2e8f0;
        }
        .page-item.active .page-link {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }

        /* ===== FORM CONTROLS ===== */
        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: #cbd5e1;
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* ===== ALERTS ===== */
        .alert { border-radius: 0.5rem; border: none; font-size: 0.875rem; }

        /* ===== OVERLAY ===== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1035;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay.show {
                display: block;
            }
            .main-wrapper {
                margin-left: 0;
            }
        }

        /* ===== PRINT ===== */
        @media print {
            .sidebar, .navbar, .d-print-none, .sidebar-overlay { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            body { background: #fff; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex">
        <!-- Sidebar -->
        @include('admin.partials.sidebar')

        <!-- Main Content Wrapper -->
        <div class="main-wrapper flex-grow-1">
            <!-- Navbar -->
            @include('admin.partials.navbar')

            <!-- Page Content -->
            <div class="p-3 p-md-4 flex-grow-1">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="text-center py-3 text-muted bg-white border-top mt-auto">
                <small>© {{ date('Y') }} TH Trade Corporation. All rights reserved.</small>
            </footer>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });
        }
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
            setTimeout(function() {
                var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                if (bsAlert) bsAlert.close();
            }, 5000);
        });
    </script>
    @yield('scripts')
</body>
</html>
