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

        /* RESPONSIVENESS ROLLBACK */
        /* REMOVE OVERLAY SYSTEM */
        /* KEEP SIMPLE BUTTON LOADER */
        .ux-toast-stack {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 2100;
        }
        .ux-disabled {
            pointer-events: none;
            opacity: 0.72;
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
            /* // ERP REPORT PRINT LAYOUT */
            .sidebar, .navbar, .d-print-none, .sidebar-overlay { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            body {
                background: #fff;
                color: #111;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: A4;
                margin: 0;
            }
            .standard-print-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 42mm 12mm 12mm;
                background: #fff;
                box-shadow: none !important;
            }
            .standard-print-title-row {
                display: grid !important;
                grid-template-columns: 1fr 1fr;
                align-items: end;
                gap: 12px;
                border-bottom: 2px solid #111;
                padding-bottom: 6px;
                margin-bottom: 8px;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .standard-print-title {
                margin: 0;
                font-size: 18px;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
            }
            .standard-print-meta {
                text-align: right;
                font-size: 11px;
            }
            .standard-print-footer {
                display: block !important;
                margin-top: 18mm;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .standard-print-signatures {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 34mm;
            }
            .standard-print-signature {
                border-top: 1px solid #111;
                padding-top: 5px;
                text-align: center;
                font-size: 10px;
                font-weight: 700;
            }
            .card {
                box-shadow: none !important;
                border: 1px solid #111 !important;
                break-inside: avoid;
                border-radius: 0 !important;
                margin-bottom: 8px !important;
            }
            .card-header {
                border-bottom: 1px solid #111 !important;
                background: #f4f4f4 !important;
                color: #111 !important;
                border-radius: 0 !important;
                padding: 4px 6px !important;
            }
            .card-body {
                padding: 6px !important;
            }
            .table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 0 !important;
            }
            .table th,
            .table td {
                color: #111 !important;
                border-color: #555 !important;
                font-size: 10px;
                padding: 4px 5px;
            }
            .table th {
                background: #f4f4f4 !important;
                text-transform: uppercase;
                font-weight: 700;
            }
            .badge {
                background: transparent !important;
                border: 0 !important;
                color: #111 !important;
                padding: 0 !important;
                font-weight: 700;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
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

    <div class="ux-toast-stack toast-container" id="toastContainer"></div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // LOCAL CACHE SYSTEM
        // INDEXEDDB POS CACHE
        window.THTradeCache = (function() {
            const isAuthenticated = @json(auth()->check());
            const cacheUrl = @json(auth()->check() ? route('local-cache.bootstrap') : null);
            const dbName = 'th_trade_pos_cache_v1';
            const syncInterval = 3 * 60 * 1000;
            const stores = ['products', 'customers', 'pricing', 'recent_invoices', 'dashboard_summary', 'meta'];
            let dbPromise = null;

            function openDb() {
                if (!isAuthenticated || !window.indexedDB) {
                    return Promise.resolve(null);
                }

                if (dbPromise) {
                    return dbPromise;
                }

                dbPromise = new Promise(function(resolve, reject) {
                    const request = indexedDB.open(dbName, 1);

                    request.onupgradeneeded = function(event) {
                        const db = event.target.result;
                        stores.forEach(function(store) {
                            if (!db.objectStoreNames.contains(store)) {
                                db.createObjectStore(store, { keyPath: store === 'meta' ? 'key' : 'id' });
                            }
                        });
                    };

                    request.onsuccess = function(event) {
                        resolve(event.target.result);
                    };
                    request.onerror = function() {
                        reject(request.error);
                    };
                });

                return dbPromise;
            }

            async function getAll(storeName) {
                const db = await openDb();
                if (!db) return [];

                return new Promise(function(resolve) {
                    const tx = db.transaction(storeName, 'readonly');
                    const request = tx.objectStore(storeName).getAll();
                    request.onsuccess = function() {
                        resolve(request.result || []);
                    };
                    request.onerror = function() {
                        resolve([]);
                    };
                });
            }

            async function getMeta(key) {
                const db = await openDb();
                if (!db) return null;

                return new Promise(function(resolve) {
                    const tx = db.transaction('meta', 'readonly');
                    const request = tx.objectStore('meta').get(key);
                    request.onsuccess = function() {
                        resolve(request.result || null);
                    };
                    request.onerror = function() {
                        resolve(null);
                    };
                });
            }

            async function replaceStore(storeName, rows, keyMapper) {
                const db = await openDb();
                if (!db) return;

                return new Promise(function(resolve, reject) {
                    const tx = db.transaction(storeName, 'readwrite');
                    const store = tx.objectStore(storeName);
                    store.clear();
                    rows.forEach(function(row) {
                        store.put(keyMapper ? keyMapper(row) : row);
                    });
                    tx.oncomplete = resolve;
                    tx.onerror = function() {
                        reject(tx.error);
                    };
                });
            }

            async function saveMeta(key, value) {
                const db = await openDb();
                if (!db) return;

                return new Promise(function(resolve) {
                    const tx = db.transaction('meta', 'readwrite');
                    tx.objectStore('meta').put({ key, value, updated_at: Date.now() });
                    tx.oncomplete = resolve;
                    tx.onerror = resolve;
                });
            }

            async function sync(force = false) {
                if (!isAuthenticated || !cacheUrl || !window.indexedDB) {
                    return false;
                }

                const lastSync = await getMeta('last_sync');
                if (!force && lastSync && Date.now() - Number(lastSync.value || 0) < syncInterval) {
                    return false;
                }

                try {
                    const response = await fetch(cacheUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        return false;
                    }

                    const payload = await response.json();
                    await Promise.all([
                        replaceStore('products', payload.products || []),
                        replaceStore('customers', payload.customers || []),
                        replaceStore('pricing', payload.pricing || [], row => ({ ...row, id: row.key })),
                        replaceStore('recent_invoices', payload.recent_invoices || []),
                        replaceStore('dashboard_summary', [{ id: 'current', ...(payload.dashboard_summary || {}) }])
                    ]);
                    await saveMeta('last_sync', Date.now());
                    return true;
                } catch (error) {
                    return false;
                }
            }

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function productScore(product, query) {
                // LOCAL FIRST SEARCH
                const normalized = normalize(query);
                if (!normalized) return 0;

                const haystack = normalize(`${product.product_name} ${product.product_id} ${product.model_no || ''}`);
                const name = normalize(product.product_name);
                const terms = normalized.split(/\s+/).filter(Boolean);
                let score = 0;

                if (name.startsWith(normalized) || normalize(product.product_id).startsWith(normalized)) score += 120;
                if (haystack.includes(normalized)) score += 70;
                terms.forEach(term => {
                    if (haystack.includes(term)) score += 25;
                });

                return score;
            }

            function customerScore(customer, query) {
                // LOCAL FIRST SEARCH
                const normalized = normalize(query);
                if (!normalized) return 0;

                const haystack = normalize(`${customer.customer_id} ${customer.customer_name} ${customer.hospital_name} ${customer.mobile}`);
                let score = 0;
                if (normalize(customer.customer_id).startsWith(normalized) || normalize(customer.hospital_name).startsWith(normalized)) score += 120;
                if (haystack.includes(normalized)) score += 70;
                normalized.split(/\s+/).filter(Boolean).forEach(term => {
                    if (haystack.includes(term)) score += 25;
                });
                return score;
            }

            async function searchProducts(query, limit = 8) {
                const products = await getAll('products');
                return products
                    .map(product => ({ product, score: productScore(product, query) }))
                    .filter(row => row.score > 0)
                    .sort((a, b) => b.score - a.score || String(a.product.product_name).localeCompare(String(b.product.product_name)))
                    .slice(0, limit)
                    .map(row => row.product);
            }

            async function searchCustomers(query, limit = 25) {
                const customers = await getAll('customers');
                return customers
                    .map(customer => ({ customer, score: customerScore(customer, query) }))
                    .filter(row => row.score > 0)
                    .sort((a, b) => b.score - a.score || String(a.customer.hospital_name || a.customer.customer_name).localeCompare(String(b.customer.hospital_name || b.customer.customer_name)))
                    .slice(0, limit)
                    .map(row => row.customer);
            }

            async function getProduct(id) {
                const products = await getAll('products');
                return products.find(product => Number(product.id) === Number(id)) || null;
            }

            async function getCustomer(id) {
                const customers = await getAll('customers');
                return customers.find(customer => Number(customer.id) === Number(id)) || null;
            }

            async function resolvePrice(customerId, productId, defaultPrice) {
                const pricing = await getAll('pricing');
                const customerPrice = pricing.find(row => Number(row.customer_id) === Number(customerId) && Number(row.product_id) === Number(productId) && row.source === 'approved_special_price')
                    || pricing.find(row => row.customer_id === null && Number(row.product_id) === Number(productId) && row.source === 'approved_special_price')
                    || pricing.find(row => Number(row.customer_id) === Number(customerId) && Number(row.product_id) === Number(productId));

                return customerPrice || {
                    price: Number(defaultPrice || 0),
                    source: 'default'
                };
            }

            if (isAuthenticated) {
                window.setTimeout(() => sync(false), 400);
                window.setInterval(() => sync(false), syncInterval);
            }

            return {
                allProducts: () => getAll('products'),
                allCustomers: () => getAll('customers'),
                getProduct,
                getCustomer,
                resolvePrice,
                searchProducts,
                searchCustomers,
                sync
            };
        })();

        // RESPONSIVENESS ROLLBACK
        // REMOVE OVERLAY SYSTEM
        // LOADING STATE FIX
        window.THTradeUX = (function() {
            const toastContainer = document.getElementById('toastContainer');
            const buttonText = new WeakMap();
            let debounceTimer = null;

            function toast(message, type = 'success') {
                if (!toastContainer || !message) return;
                const bg = type === 'error' ? 'text-bg-danger' : (type === 'warning' ? 'text-bg-warning' : 'text-bg-success');
                const toastEl = document.createElement('div');
                toastEl.className = `toast align-items-center ${bg} border-0`;
                toastEl.setAttribute('role', 'status');
                toastEl.setAttribute('aria-live', 'polite');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                toastContainer.appendChild(toastEl);
                const instance = new bootstrap.Toast(toastEl, { delay: 3500 });
                toastEl.addEventListener('hidden.bs.toast', function() {
                    toastEl.remove();
                });
                instance.show();
            }

            function setButtonLoading(button, text = 'Processing...') {
                if (!button || button.disabled) return;
                buttonText.set(button, button.innerHTML);
                button.disabled = true;
                button.classList.add('ux-disabled');
                button.innerHTML = `<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>${text}`;
            }

            function resetButton(button) {
                if (!button || !buttonText.has(button)) return;
                button.innerHTML = buttonText.get(button);
                button.disabled = false;
                button.classList.remove('ux-disabled');
                buttonText.delete(button);
            }

            function getLoadingText(button, form) {
                if (button?.dataset.loadingText) return button.dataset.loadingText;
                if (form?.dataset.loadingText) return form.dataset.loadingText;
                const label = (button?.textContent || '').trim().toLowerCase();
                if (label.includes('generate') || label.includes('view ledger') || label.includes('analyze')) return 'Generating...';
                if (label.includes('save') || label.includes('update')) return 'Saving...';
                if (label.includes('collect') || label.includes('record')) return 'Processing...';
                if (label.includes('search')) return 'Searching...';
                return 'Processing...';
            }

            function bindDebouncedSearch() {
                document.querySelectorAll('form.js-debounce-search input[name="search"]').forEach(function(input) {
                    const form = input.form;
                    let lastValue = input.value;
                    input.addEventListener('input', function() {
                        window.clearTimeout(debounceTimer);
                        debounceTimer = window.setTimeout(function() {
                            if (input.value === lastValue) return;
                            lastValue = input.value;
                            form.requestSubmit();
                        }, Number(form.dataset.debounce || 400));
                    });
                });
            }

            function bindForms() {
                document.addEventListener('submit', function(event) {
                    const form = event.target;
                    if (!(form instanceof HTMLFormElement) || form.dataset.noLoading === 'true') return;
                    if (!form.checkValidity()) return;

                    const submitter = event.submitter || form.querySelector('[type="submit"]');
                    const text = getLoadingText(submitter, form);
                    setButtonLoading(submitter, text);

                    // KEEP SIMPLE BUTTON LOADER
                });
            }

            bindDebouncedSearch();
            bindForms();

            return {
                toast,
                setButtonLoading,
                resetButton
            };
        })();

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

        // TOAST NOTIFICATIONS
        @if(session('success'))
            window.THTradeUX.toast(@json(session('success')), 'success');
        @endif
        @if(session('error'))
            window.THTradeUX.toast(@json(session('error')), 'error');
        @endif
    </script>
    @yield('scripts')
</body>
</html>
