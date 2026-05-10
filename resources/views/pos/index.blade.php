@extends('layouts.admin')

@section('content')
@php
    // POS SEARCH IMPROVEMENT
    $productCatalog = $products->map(fn ($product) => [
        'id' => $product->id,
        'product_id' => $product->product_id,
        'product_name' => $product->product_name,
        'selling_price' => round((float) $product->selling_price, 2),
        'stock_quantity' => (int) $product->stock_quantity,
    ])->values();
@endphp

<style>
    .pos-panel { border-radius: 6px; }
    .pos-muted-box { background: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 6px; }
    .product-search-wrap { position: relative; }
    .product-results {
        position: absolute;
        z-index: 1050;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ced4da;
        border-top: 0;
        border-radius: 0 0 6px 6px;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
    }
    .product-result { cursor: pointer; padding: 9px 12px; border-bottom: 1px solid #f1f3f5; }
    .product-result:hover, .product-result.active { background: #eef5ff; }
    .summary-row { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 9px; }
    .summary-row strong { white-space: nowrap; }
    .summary-total { font-size: 1.08rem; padding-top: 10px; border-top: 1px solid #dee2e6; }
    .price-source { min-width: 86px; display: inline-block; }
</style>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm pos-panel mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice Generation</h5>
                <span class="badge bg-light text-dark"># {{ $invoice_no }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Select Customer</label>
                        <select id="customer_select" class="form-select">
                            <option value="">-- Choose Customer --</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-mobile="{{ $customer->mobile }}" data-address="{{ $customer->address }}" data-due="{{ $customer->current_due }}">
                                    {{ $customer->customer_id }} - {{ $customer->hospital_name }} @if($customer->customer_name) ({{ $customer->customer_name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer Details</label>
                        <div id="customer_details" class="small text-muted pos-muted-box p-2">
                            Select a customer to see details...
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3 pos-muted-box p-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Search Product</label>
                        <div class="product-search-wrap">
                            <input type="text" id="product_search" class="form-control" autocomplete="off" placeholder="Type product name, model, or code">
                            <input type="hidden" id="product_select">
                            <div id="product_results" class="product-results d-none"></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" id="product_qty" class="form-control" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Stock</label>
                        <input type="text" id="selected_stock" class="form-control" value="-" readonly>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" id="add_item_btn" class="btn btn-primary w-100" data-loading-text="Adding...">Add Item</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mt-3" id="invoice_table">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 54px;">SL</th>
                                <th>Product</th>
                                <th style="width: 110px;" class="text-center">Quantity</th>
                                <th style="width: 180px;" class="text-end">Unit Price</th>
                                <th style="width: 140px;" class="text-end">Total</th>
                                <th style="width: 80px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No products added.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm sticky-top pos-panel" style="top: 20px;">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Billing Summary</h5>
            </div>
            <div class="card-body bg-light">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong id="summary_subtotal">Tk. 0.00</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Discount</label>
                    <div class="input-group input-group-sm">
                        {{-- // DISCOUNT TYPE SYSTEM --}}
                        <select id="discount_type" class="form-select" style="max-width: 135px;">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (Tk.)</option>
                        </select>
                        <input type="number" id="discount_value" class="form-control" min="0" max="100" step="0.01" value="0">
                    </div>
                    <div class="small text-muted mt-1">Discount amount: <span id="summary_discount">Tk. 0.00</span></div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label small">VAT (%)</label>
                        <input type="number" id="vat_percent" class="form-control form-control-sm" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">AIT (%)</label>
                        <input type="number" id="ait_percent" class="form-control form-control-sm" min="0" step="0.01">
                    </div>
                </div>

                <div class="mt-3 mb-3">
                    <label class="form-label small">Extra Charge (Tk.)</label>
                    <input type="number" id="extra_charge" class="form-control form-control-sm" min="0" step="0.01">
                </div>

                <div class="summary-row fw-bold text-secondary">
                    <span>Current Bill</span>
                    <strong id="summary_current_bill">Tk. 0.00</strong>
                </div>
                <div class="summary-row text-warning fw-bold bg-white border rounded p-2">
                    <span>Previous Due</span>
                    <strong id="summary_prev_due">Tk. 0.00</strong>
                </div>
                <div class="summary-row summary-total text-primary fw-bold">
                    <span>Total Payable</span>
                    <strong id="summary_total_payable">Tk. 0.00</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label text-success fw-bold">Received Amount (Tk.)</label>
                    <input type="number" id="received_amount" class="form-control border-success" value="0" min="0" step="0.01">
                </div>
                <div class="summary-row text-danger h5">
                    <span>Final Due</span>
                    <strong id="summary_due">Tk. 0.00</strong>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="button" id="save_invoice_btn" class="btn btn-success btn-lg" data-loading-text="Processing...">Save Invoice</button>
                    <button type="button" id="clear_all_btn" class="btn btn-outline-danger shadow-sm">Clear All</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const isAdmin = @json($userRole === 'admin');
        const productCatalog = @json($productCatalog);
        const csrfToken = '{{ csrf_token() }}';
        let items = [];
        let selectedProduct = null;

        function money(amount) {
            return `Tk. ${Number(amount || 0).toFixed(2)}`;
        }

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function(char) {
                return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char];
            });
        }

        // POS SEARCH IMPROVEMENT
        function scoreProduct(product, query) {
            const normalized = query.toLowerCase().trim();
            if (!normalized) {
                return 0;
            }

            const haystack = `${product.product_name} ${product.product_id}`.toLowerCase();
            const words = product.product_name.toLowerCase().split(/\s+/).filter(Boolean);
            const terms = normalized.split(/\s+/).filter(Boolean);
            let score = 0;

            if (words[0] && words[0].startsWith(terms[0])) {
                score += 120;
            }

            if (words[1] && terms.some(term => words[1].startsWith(term))) {
                score += 95;
            }

            if (haystack.includes(normalized)) {
                score += 70;
            }

            terms.forEach(function(term, index) {
                if (words[index] && words[index].startsWith(term)) {
                    score += 45;
                } else if (haystack.includes(term)) {
                    score += 25;
                }
            });

            return score;
        }

        function renderProductResults(query) {
            const ranked = productCatalog
                .map(product => ({ product, score: scoreProduct(product, query) }))
                .filter(row => row.score > 0)
                .sort((a, b) => b.score - a.score || a.product.product_name.localeCompare(b.product.product_name))
                .slice(0, 8);

            if (!ranked.length) {
                $('#product_results').html('<div class="product-result text-muted">No matching product found</div>').removeClass('d-none');
                return;
            }

            const html = ranked.map(function(row) {
                const product = row.product;
                return `
                    <div class="product-result" data-id="${product.id}">
                        <div class="fw-bold">${escapeHtml(product.product_name)}</div>
                        <div class="small text-muted">[${escapeHtml(product.product_id)}] Stock: ${product.stock_quantity} | Default: ${money(product.selling_price)}</div>
                    </div>
                `;
            }).join('');

            $('#product_results').html(html).removeClass('d-none');
        }

        $('#product_search').on('input focus', function() {
            selectedProduct = null;
            $('#product_select').val('');
            $('#selected_stock').val('-');
            renderProductResults($(this).val());
        });

        $(document).on('click', '.product-result[data-id]', function() {
            const productId = Number($(this).data('id'));
            selectedProduct = productCatalog.find(product => product.id === productId);
            if (!selectedProduct) {
                return;
            }

            $('#product_search').val(`[${selectedProduct.product_id}] ${selectedProduct.product_name}`);
            $('#product_select').val(selectedProduct.id);
            $('#selected_stock').val(selectedProduct.stock_quantity);
            $('#product_results').addClass('d-none');
        });

        $(document).on('click', function(event) {
            if (!$(event.target).closest('.product-search-wrap').length) {
                $('#product_results').addClass('d-none');
            }
        });

        $('#customer_select').change(function() {
            let option = $(this).find('option:selected');
            if (option.val()) {
                $('#customer_details').html(`
                    <strong>Mobile:</strong> ${escapeHtml(option.data('mobile') || 'N/A')}<br>
                    <strong>Address:</strong> ${escapeHtml(option.data('address') || 'N/A')}
                `);
                $('#summary_prev_due').text(money(parseFloat(option.data('due')) || 0));
                refreshItemPricesForCustomer(option.val());
            } else {
                $('#customer_details').text('Select a customer to see details...');
                $('#summary_prev_due').text(money(0));
                renderTable();
            }
        });

        // CUSTOMER PRICE MEMORY
        // DYNAMIC CUSTOMER PRICING
        function refreshItemPricesForCustomer(customerId) {
            if (!items.length || !customerId) {
                renderTable();
                return;
            }

            $.ajax({
                url: `{{ url('/pos/customer-prices') }}/${customerId}`,
                method: 'POST',
                data: JSON.stringify({
                    _token: csrfToken,
                    product_ids: items.map(item => item.product_id)
                }),
                contentType: 'application/json',
                success: function(response) {
                    items = items.map(function(item) {
                        const resolved = response.prices[item.product_id];
                        if (!resolved) {
                            return item;
                        }

                        return {
                            ...item,
                            price: Number(resolved.price),
                            price_source: resolved.source
                        };
                    });
                    renderTable();
                },
                error: function() {
                    renderTable();
                    window.THTradeUX.toast('Could not refresh customer price history.', 'error');
                }
            });
        }

        $('#add_item_btn').click(function() {
            const addButton = this;
            const customerId = $('#customer_select').val();
            let productId = $('#product_select').val();
            let qty = parseInt($('#product_qty').val(), 10);

            if (!customerId) {
                Swal.fire('Customer Required', 'Please select a customer before adding products.', 'warning');
                return;
            }

            if (!productId || qty < 1) {
                Swal.fire('Error', 'Please select a product and valid quantity', 'error');
                return;
            }

            window.THTradeUX.setButtonLoading(addButton, 'Adding...');

            let existing = items.find(i => i.product_id == productId);
            if (existing) {
                if (existing.quantity + qty > existing.stock_quantity) {
                    Swal.fire('Low Stock', `Only ${existing.stock_quantity} items available`, 'warning');
                    window.THTradeUX.resetButton(addButton);
                    return;
                }
                existing.quantity += qty;
                renderTable();
                window.THTradeUX.resetButton(addButton);
                return;
            }

            $.get(`{{ url('/pos/product') }}/${productId}`, { customer_id: customerId }, function(product) {
                if (product.stock_quantity < qty) {
                    Swal.fire('Low Stock', `Only ${product.stock_quantity} items available`, 'warning');
                    return;
                }

                items.push({
                    product_id: product.id,
                    name: product.product_name,
                    quantity: qty,
                    stock_quantity: Number(product.stock_quantity),
                    price: Number(product.customer_price ?? product.selling_price),
                    price_source: product.price_source || 'default'
                });
                renderTable();
            }).fail(function() {
                Swal.fire('Error', 'Could not load product details. Please try again.', 'error');
            }).always(function() {
                window.THTradeUX.resetButton(addButton);
            });
        });

        $(document).on('click', '.remove-item', function() {
            let index = $(this).data('index');
            items.splice(index, 1);
            renderTable();
        });

        $(document).on('input', '.item-price-input', function() {
            const index = Number($(this).data('index'));
            const value = Math.max(0, parseFloat($(this).val() || 0) || 0);
            if (!items[index] || !isAdmin) {
                return;
            }

            items[index].price = value;
            items[index].price_source = 'manual';
            renderTable();
        });

        function priceSourceLabel(source) {
            if (source === 'approved_special_price') {
                return '<span class="badge text-bg-success price-source">Approved</span>';
            }
            if (source === 'customer_history') {
                return '<span class="badge text-bg-info price-source">Last price</span>';
            }
            if (source === 'manual') {
                return '<span class="badge text-bg-warning price-source">Manual</span>';
            }
            return '<span class="badge text-bg-secondary price-source">Default</span>';
        }

        function renderTable() {
            let html = '';
            let subtotal = 0;

            if (!items.length) {
                $('#invoice_table tbody').html('<tr><td colspan="6" class="text-center text-muted py-4">No products added.</td></tr>');
                updateSummary(0);
                return;
            }

            items.forEach((item, index) => {
                let total = Number(item.price) * Number(item.quantity);
                subtotal += total;
                const priceField = isAdmin
                    ? `<input type="number" class="form-control form-control-sm text-end item-price-input" data-index="${index}" value="${Number(item.price).toFixed(2)}" min="0" step="0.01">`
                    : `<input type="number" class="form-control form-control-sm text-end" value="${Number(item.price).toFixed(2)}" readonly>`;

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>
                            <div class="fw-bold">${escapeHtml(item.name)}</div>
                            <div class="small text-muted">${priceSourceLabel(item.price_source)}</div>
                        </td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${priceField}</td>
                        <td class="text-end fw-bold">${money(total)}</td>
                        <td class="text-center">
                            <button class="btn btn-danger btn-sm remove-item" data-index="${index}" type="button">&times;</button>
                        </td>
                    </tr>
                `;
            });

            $('#invoice_table tbody').html(html);
            updateSummary(subtotal);
        }

        function calculateDiscount(subtotal) {
            // DISCOUNT TYPE SYSTEM
            let discountType = $('#discount_type').val();
            let value = Math.max(0, parseFloat($('#discount_value').val() || 0) || 0);

            if (discountType === 'fixed') {
                return Math.min(value, subtotal);
            }

            return (subtotal * Math.min(value, 100)) / 100;
        }

        function updateSummary(subtotal) {
            let vatP = parseFloat($('#vat_percent').val() || 0) || 0;
            let aitP = parseFloat($('#ait_percent').val() || 0) || 0;
            let extra = parseFloat($('#extra_charge').val() || 0) || 0;
            let received = parseFloat($('#received_amount').val() || 0) || 0;
            let prevDue = 0;
            let selectedOption = $('#customer_select').find('option:selected');

            if (selectedOption.val()) {
                prevDue = parseFloat(selectedOption.data('due') || 0) || 0;
            }

            let discount = Number(calculateDiscount(subtotal).toFixed(2));
            let vat = Number(((subtotal * vatP) / 100).toFixed(2));
            let ait = Number(((subtotal * aitP) / 100).toFixed(2));
            let currentBill = Number(((subtotal - discount) + vat + ait + extra).toFixed(2));
            let totalPayable = Number((currentBill + prevDue).toFixed(2));
            let due = Number(Math.max(0, totalPayable - received).toFixed(2));

            $('#summary_subtotal').text(money(subtotal));
            $('#summary_discount').text(money(discount));
            $('#summary_current_bill').text(money(currentBill));
            $('#summary_total_payable').text(money(totalPayable));
            $('#summary_due').text(money(due));
        }

        $('#discount_type').on('change', function() {
            const isPercentage = $(this).val() === 'percentage';
            $('#discount_value').attr('max', isPercentage ? '100' : null).val('0');
            updateSummary(items.reduce((sum, item) => sum + (item.price * item.quantity), 0));
        });

        $('#discount_value, #vat_percent, #ait_percent, #extra_charge, #received_amount').on('input', function() {
            let subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            updateSummary(subtotal);
        });

        $('#clear_all_btn').click(function() {
            items = [];
            selectedProduct = null;
            $('#product_search').val('');
            $('#product_select').val('');
            $('#selected_stock').val('-');
            renderTable();
        });

        $('#save_invoice_btn').click(function() {
            const saveButton = this;
            if (saveButton.disabled) {
                return;
            }

            if (items.length === 0) {
                Swal.fire('Error', 'Please add at least one product', 'error');
                return;
            }

            let customerId = $('#customer_select').val();
            if (!customerId) {
                Swal.fire('Error', 'Please select a customer', 'error');
                return;
            }

            window.THTradeUX.setButtonLoading(saveButton, 'Saving...');

            const discountType = $('#discount_type').val();
            const discountValue = $('#discount_value').val();
            let data = {
                _token: csrfToken,
                customer_id: customerId,
                items: items.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    price: item.price
                })),
                // DISCOUNT TYPE SYSTEM
                discount_type: discountType,
                discount_percent: discountType === 'percentage' ? discountValue : 0,
                discount_value: discountType === 'fixed' ? discountValue : 0,
                vat_percent: $('#vat_percent').val(),
                ait_percent: $('#ait_percent').val(),
                extra_charge: $('#extra_charge').val(),
                received_amount: $('#received_amount').val(),
                date: new Date().toISOString().slice(0, 10)
            };

            $.ajax({
                url: '{{ route("pos.store") }}',
                method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(response) {
                    window.THTradeUX.toast('Invoice Saved Successfully', 'success');
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Print Invoice',
                        cancelButtonText: 'Done'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(`{{ url('/pos/print') }}/${response.invoice_id}`, '_blank');
                        }
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
                    window.THTradeUX.toast(msg, 'error');
                    Swal.fire('Error', msg, 'error');
                },
                complete: function() {
                    window.THTradeUX.resetButton(saveButton);
                }
            });
        });
    });
</script>
@endsection
