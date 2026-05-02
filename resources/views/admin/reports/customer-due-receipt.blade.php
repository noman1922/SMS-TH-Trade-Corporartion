<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Due Statement - {{ $customer->customer_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #e2e8f0; color: #111; font-family: Arial, sans-serif; }
        /* // PRINT LAYOUT FIX */
        .receipt-page { background: #fff; max-width: 210mm; margin: 20px auto; padding: 28px 36px; box-shadow: 0 4px 24px rgba(0,0,0,0.12); }
        .receipt-header { border-bottom: 2px solid #111; margin-bottom: 16px; padding-bottom: 14px; }
        /* // PROFESSIONAL INVOICE STYLE */
        .company-name { font-size: 1.35rem; font-weight: 700; color: #111; }
        .muted-label { color: #333; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; }
        .value { font-weight: 700; }
        .summary-table td { padding: 5px 0; color: #111; }
        .signature-area { margin-top: 38px; }
        .signature-line { border-top: 1px solid #111; width: 180px; padding-top: 5px; display: inline-block; text-align: center; }
        .print-actions { text-align: center; margin: 20px 0; }
        @media print {
            /* // PRINT LAYOUT FIX */
            @page {
                size: A4;
                margin: 10mm;
            }

            body { background: #fff; margin: 0; padding: 0; color: #111; }
            .receipt-page { box-shadow: none; margin: 0; max-width: 100%; width: 100%; padding: 0; }
            .print-actions { display: none !important; }
            .page-break { page-break-before: avoid; }
            .receipt-header,
            .summary-table,
            .signature-area {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            * {
                color: #111 !important;
                background: #fff !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    {{-- // PAYMENT RECEIPT SYSTEM --}}
    {{-- // ROW PRINT FIX --}}
    <div class="print-actions">
        {{-- // LOADING STATE FIX --}}
        <button onclick="handlePrintClick(this)" class="btn btn-primary px-4">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>

    <div class="receipt-page">
        <div class="receipt-header">
            <div class="row">
                <div class="col-7">
                    <div class="company-name">TH Trade Corporation</div>
                    <div class="text-muted small">Customer Due Statement</div>
                </div>
                <div class="col-5 text-end small">
                    <div><strong>Statement No:</strong> DUE-{{ str_pad($customer->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div><strong>Date & Time:</strong> {{ now()->format('d M, Y h:i A') }}</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="muted-label">Customer Name</div>
                <div class="value">{{ $customer->customer_name }}</div>
                @if($customer->hospital_name)
                    <div class="text-muted small">{{ $customer->hospital_name }}</div>
                @endif
            </div>
            <div class="col-6">
                <div class="muted-label">Customer Mobile</div>
                <div class="value">{{ $customer->mobile }}</div>
                <div class="text-muted small">{{ $customer->address }}</div>
            </div>
        </div>

        <table class="table summary-table">
            <tbody>
                <tr>
                    <td>Previous Due</td>
                    <td class="text-end">৳ {{ number_format($customer->previous_due, 2) }}</td>
                </tr>
                <tr>
                    <td>Total Purchase</td>
                    <td class="text-end">৳ {{ number_format($customer->total_purchased, 2) }}</td>
                </tr>
                <tr>
                    <td>Paid Amount</td>
                    <td class="text-end">৳ {{ number_format($customer->total_paid, 2) }}</td>
                </tr>
                <tr class="table-light">
                    <td class="fw-bold">Remaining Due</td>
                    <td class="text-end fw-bold">৳ {{ number_format($customer->current_due, 2) }}</td>
                </tr>
                <tr>
                    <td>Collected / Printed By</td>
                    <td class="text-end">{{ auth()->user()->name }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-area">
            <div class="row">
                <div class="col-6">
                    <div class="signature-line">Customer Signature</div>
                </div>
                <div class="col-6 text-end">
                    <div class="signature-line">Authorized Signature</div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // RESPONSIVENESS ROLLBACK
        // LOADING STATE FIX
        function handlePrintClick(button) {
            const original = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Processing...';
            window.setTimeout(function() {
                window.print();
                button.disabled = false;
                button.innerHTML = original;
            }, 120);
        }
    </script>
</body>
</html>
