<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #e2e8f0; font-family: 'Inter', sans-serif; color: #1e293b; }

        .invoice-page {
            background: #fff;
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 40px 50px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
            position: relative;
        }

        /* Header */
        .invoice-header {
            border-bottom: 3px solid #1e293b;
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .company-name { font-size: 1.75rem; font-weight: 700; color: #1e293b; letter-spacing: 1px; }
        .company-tagline { color: #64748b; font-size: 0.85rem; margin-top: 2px; }
        .company-contact { font-size: 0.8rem; color: #64748b; line-height: 1.6; }
        .invoice-title { font-size: 1.5rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 2px; }
        .invoice-meta { font-size: 0.85rem; color: #475569; }
        .invoice-meta strong { color: #1e293b; }

        /* Billing Section */
        .billing-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 32px;
        }
        .billing-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; font-weight: 600; margin-bottom: 8px; }
        .billing-name { font-size: 1rem; font-weight: 600; color: #1e293b; }
        .billing-detail { font-size: 0.85rem; color: #64748b; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 32px; }
        .items-table thead th {
            background: #1e293b;
            color: #fff;
            padding: 12px 16px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .items-table thead th:first-child { border-radius: 6px 0 0 0; }
        .items-table thead th:last-child { border-radius: 0 6px 0 0; }
        .items-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }
        .items-table tbody tr:nth-child(even) { background: #fafbfc; }
        .items-table tbody tr:last-child td { border-bottom: 2px solid #e2e8f0; }

        /* Totals */
        .totals-section { margin-left: auto; width: 320px; }
        .totals-table { width: 100%; }
        .totals-table td { padding: 6px 0; font-size: 0.875rem; color: #64748b; }
        .totals-table td:last-child { text-align: right; font-weight: 500; color: #1e293b; }
        .totals-table .grand-total td {
            border-top: 2px solid #1e293b;
            font-size: 1.15rem;
            font-weight: 700;
            padding-top: 12px;
            color: #1e293b;
        }
        .totals-table .paid td { color: #16a34a; }
        .totals-table .due td { color: #dc2626; font-weight: 600; }

        /* Footer */
        .invoice-footer {
            position: absolute;
            bottom: 40px;
            left: 50px;
            right: 50px;
        }
        .signature-line {
            border-top: 2px solid #1e293b;
            width: 200px;
            padding-top: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
        }
        .footer-note {
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
            margin-top: 48px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.75rem;
        }

        /* Print Actions */
        .print-actions { text-align: center; margin: 20px 0; }

        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .invoice-page {
                box-shadow: none;
                margin: 0;
                padding: 30px 40px;
                width: 100%;
                max-width: 100%;
                min-height: auto;
            }
            .print-actions { display: none !important; }
            .invoice-footer { position: relative; bottom: auto; left: auto; right: auto; margin-top: 60px; }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5 shadow">
            <i class="bi bi-printer me-2"></i> Print Invoice
        </button>
        <a href="{{ route('pos.index') }}" class="btn btn-secondary btn-lg px-4 shadow ms-2">Back to POS</a>
    </div>

    <div class="invoice-page">
        <!-- Header -->
        <div class="invoice-header">
            <div class="row align-items-start">
                <div class="col-7">
                    <div class="company-name">TH TRADE CORPORATION</div>
                    <div class="company-tagline">Specialized Medical Equipment Supplier</div>
                    <div class="company-contact mt-2">
                        Dhaka, Bangladesh<br>
                        Phone: +880-XXXX-XXXXXX<br>
                        Email: info@thtradecorp.com
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="invoice-title">Invoice</div>
                    <div class="invoice-meta mt-2">
                        <strong>Invoice #:</strong> {{ $invoice->invoice_no }}<br>
                        <strong>Date:</strong> {{ date('d M, Y', strtotime($invoice->date)) }}<br>
                        <strong>Prepared by:</strong> {{ $invoice->user->name }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        <div class="billing-section">
            <div class="row">
                <div class="col-6">
                    <div class="billing-label">Bill To</div>
                    <div class="billing-name">{{ $invoice->customer->customer_name }}</div>
                    @if($invoice->customer->hospital_name)
                        <div class="billing-detail">{{ $invoice->customer->hospital_name }}</div>
                    @endif
                    <div class="billing-detail">{{ $invoice->customer->mobile }}</div>
                    <div class="billing-detail">{{ $invoice->customer->address }}</div>
                </div>
                <div class="col-6 text-end">
                    <div class="billing-label">Payment Status</div>
                    @if($invoice->due_amount <= 0)
                        <span style="color: #16a34a; font-weight: 700; font-size: 1.25rem;">PAID</span>
                    @elseif($invoice->received_amount > 0)
                        <span style="color: #d97706; font-weight: 700; font-size: 1.25rem;">PARTIAL</span>
                    @else
                        <span style="color: #dc2626; font-weight: 700; font-size: 1.25rem;">UNPAID</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="50">SL</th>
                    <th>Product Description</th>
                    <th class="text-center" width="80">Qty</th>
                    <th class="text-end" width="130">Unit Price</th>
                    <th class="text-end" width="130">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->product_name }}</strong>
                        @if($item->product->model_no)
                            <br><small style="color: #94a3b8;">Model: {{ $item->product->model_no }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">৳ {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end fw-bold">৳ {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td>৳ {{ number_format($invoice->sub_total, 2) }}</td>
                </tr>
                @if($invoice->discount_percent > 0)
                <tr>
                    <td>Discount ({{ $invoice->discount_percent }}%):</td>
                    <td style="color: #dc2626;">- ৳ {{ number_format(($invoice->sub_total * $invoice->discount_percent) / 100, 2) }}</td>
                </tr>
                @endif
                @if($invoice->vat_percent > 0)
                <tr>
                    <td>VAT ({{ $invoice->vat_percent }}%):</td>
                    <td>+ ৳ {{ number_format(($invoice->sub_total * $invoice->vat_percent) / 100, 2) }}</td>
                </tr>
                @endif
                @if($invoice->ait_percent > 0)
                <tr>
                    <td>AIT ({{ $invoice->ait_percent }}%):</td>
                    <td>+ ৳ {{ number_format(($invoice->sub_total * $invoice->ait_percent) / 100, 2) }}</td>
                </tr>
                @endif
                @if($invoice->extra_charge > 0)
                <tr>
                    <td>Extra Charge:</td>
                    <td>+ ৳ {{ number_format($invoice->extra_charge, 2) }}</td>
                </tr>
                @endif
                @php
                    $final_due = $invoice->customer->current_due;
                    $current_invoice = $invoice->net_payable;
                    $paid_amount = $invoice->received_amount;
                    $previous_due = $final_due - ($current_invoice - $paid_amount);
                    $total_payable = $previous_due + $current_invoice;
                @endphp
                <tr class="grand-total">
                    <td style="font-size: 1rem;">Current Invoice:</td>
                    <td style="font-size: 1rem;">৳ {{ number_format($current_invoice, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-top: 8px; color: #d97706; font-weight: 600;">Previous Due:</td>
                    <td style="padding-top: 8px; color: #d97706; font-weight: 600;">+ ৳ {{ number_format($previous_due, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-top: 8px; font-weight: 700; color: #1e293b; font-size: 1.1rem; border-top: 1px dashed #cbd5e1;">Total Payable:</td>
                    <td style="padding-top: 8px; font-weight: 700; color: #1e293b; font-size: 1.1rem; border-top: 1px dashed #cbd5e1;">৳ {{ number_format($total_payable, 2) }}</td>
                </tr>
                <tr class="paid">
                    <td style="padding-top: 8px;">Paid Amount:</td>
                    <td style="padding-top: 8px;">- ৳ {{ number_format($paid_amount, 2) }}</td>
                </tr>
                <tr class="due">
                    <td style="padding-top: 12px; font-size: 1.15rem;">Final Due:</td>
                    <td style="padding-top: 12px; font-size: 1.15rem;">৳ {{ number_format($final_due, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="invoice-footer">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">Issued by: {{ $invoice->user->name }}</small>
                </div>
                <div class="col-6 text-end">
                    <div class="signature-line d-inline-block text-center">
                        Authorized Signature
                    </div>
                </div>
            </div>
            <div class="footer-note">
                <p class="mb-1">Thank you for your business!</p>
                <p class="mb-0">This is a computer-generated invoice. No signature is required for amounts below ৳10,000.</p>
            </div>
        </div>
    </div>

</body>
</html>
