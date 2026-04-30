<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $receiptNo ?? 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #e2e8f0; font-family: 'Inter', sans-serif; color: #111; }

        .receipt-page {
            /* // PRINT LAYOUT FIX */
            background: #fff;
            max-width: 210mm;
            margin: 20px auto;
            padding: 28px 36px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        .receipt-header {
            border-bottom: 2px solid #111;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }
        .company-name { font-size: 1.35rem; font-weight: 700; color: #111; letter-spacing: 1px; }
        .company-contact { font-size: 0.72rem; color: #333; line-height: 1.35; }
        .receipt-title {
            /* // PROFESSIONAL INVOICE STYLE */
            font-size: 1.2rem; font-weight: 700; color: #111;
            text-transform: uppercase; letter-spacing: 2px;
        }
        .receipt-meta { font-size: 0.76rem; color: #333; }

        .amount-box {
            background: #fff;
            border: 1px solid #111;
            border-radius: 0;
            padding: 12px;
            text-align: center;
            margin: 16px 0;
        }
        .amount-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1.5px; color: #333; font-weight: 600; }
        .amount-value { font-size: 1.45rem; font-weight: 700; color: #111; }

        .detail-grid {
            background: #fff;
            border: 0;
            border-radius: 0;
            padding: 0;
            margin: 16px 0;
        }
        .detail-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1.5px; color: #333; font-weight: 600; margin-bottom: 4px; }
        .detail-value { font-size: 0.9rem; font-weight: 600; color: #111; }

        .invoice-summary {
            background: #fff;
            border: 1px solid #111;
            border-radius: 0;
            padding: 12px;
        }
        .invoice-summary table { width: 100%; }
        .invoice-summary td { padding: 4px 0; font-size: 0.8rem; color: #111; font-weight: 400; }
        .invoice-summary .remaining td { border-top: 1px solid #111; font-weight: 700; padding-top: 7px; }

        .signature-area { margin-top: 38px; }
        .signature-line {
            border-top: 1px solid #111;
            width: 180px;
            padding-top: 5px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #111;
        }

        .footer-note {
            border-top: 1px solid #ddd;
            padding-top: 8px;
            margin-top: 20px;
            text-align: center;
            color: #333;
            font-size: 0.68rem;
        }

        .print-actions { text-align: center; margin: 20px 0; }

        @media print {
            /* // PRINT LAYOUT FIX */
            @page {
                size: A4;
                margin: 10mm;
            }

            body { background: #fff; margin: 0; padding: 0; color: #111; }
            .receipt-page { box-shadow: none; margin: 0; padding: 0; width: 100%; max-width: 100%; }
            .print-actions { display: none !important; }
            .page-break { page-break-before: avoid; }
            .receipt-header,
            .amount-box,
            .detail-grid,
            .invoice-summary,
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

    <div class="print-actions">
        {{-- // LOADING STATE FIX --}}
        <button onclick="handlePrintClick(this)" class="btn btn-primary btn-lg px-5 shadow">
            <i class="bi bi-printer me-2"></i> Print Receipt
        </button>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-lg px-4 shadow ms-2">Back to List</a>
    </div>

    <div class="receipt-page">
        <!-- Header -->
        <div class="receipt-header">
            <div class="row align-items-start">
                <div class="col-7">
                    {{-- // PAYMENT RECEIPT SYSTEM --}}
                    <div class="company-name">TH Trade Corporation</div>
                    <div class="company-contact mt-1">
                        Specialized Medical Equipment Supplier<br>
                        Dhaka, Bangladesh | +880-XXXX-XXXXXX
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="receipt-title">Money Receipt</div>
                    <div class="receipt-meta mt-2">
                        <strong>Receipt #:</strong> {{ $receiptNo ?? 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}<br>
                        <strong>Date & Time:</strong> {{ optional($payment->created_at)->format('d M, Y h:i A') ?? \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Box -->
        <div class="amount-box">
            <div class="amount-label">Amount Received</div>
            <div class="amount-value">৳ {{ number_format($payment->amount, 2) }}</div>
        </div>

        <!-- Details Grid -->
        <div class="detail-grid">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="detail-label">Customer Name</div>
                    <div class="detail-value">{{ $payment->customer->customer_name }}</div>
                    @if($payment->customer->hospital_name)
                        <div style="font-size: 0.8rem; color: #64748b;">{{ $payment->customer->hospital_name }}</div>
                    @endif
                    <div style="font-size: 0.8rem; color: #64748b;"><strong>Mobile:</strong> {{ $payment->customer->mobile }}</div>
                </div>
                <div class="col-md-3">
                    <div class="detail-label">Invoice No</div>
                    <div class="detail-value">
                        @if($payment->invoice_id)
                            {{ $payment->invoice->invoice_no }}
                        @else
                            Advance Payment
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="detail-label">Payment Method</div>
                    <div class="detail-value text-uppercase">{{ $payment->payment_method }}</div>
                </div>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="invoice-summary">
            {{-- // PAYMENT RECEIPT SYSTEM --}}
            {{-- // PROFESSIONAL INVOICE STYLE --}}
            <h6 class="fw-bold mb-3" style="font-size: 0.78rem; text-transform: uppercase; color: #111; letter-spacing: 1px;">Payment Summary</h6>
            <table>
                @if($payment->invoice_id && $payment->invoice)
                <tr>
                    <td class="text-muted">Invoice Total:</td>
                    <td class="text-end">৳ {{ number_format($payment->invoice->net_payable, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Previous Due:</td>
                    <td class="text-end">৳ {{ number_format($previousDue ?? (($payment->customer->current_due ?? 0) + $payment->amount), 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Amount Paid Now:</td>
                    <td class="text-end" style="color: #111;">- ৳ {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr class="remaining">
                    <td style="color: #111;">Remaining Balance:</td>
                    <td class="text-end" style="color: #111;">৳ {{ number_format($remainingDue ?? ($payment->customer->current_due ?? 0), 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Collected By:</td>
                    <td class="text-end fw-bold">{{ $payment->user->name ?? 'System' }}</td>
                </tr>
            </table>
        </div>

        @if($payment->note)
        <div class="mt-3 p-2" style="background: #fff; border: 1px solid #ddd; border-radius: 0;">
            <small class="text-muted"><strong>Note:</strong> {{ $payment->note }}</small>
        </div>
        @endif

        <!-- Signature -->
        <div class="signature-area">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">Collected by: {{ $payment->user->name ?? 'System' }}</small>
                </div>
                <div class="col-6 text-end">
                    <div class="signature-line d-inline-block text-center">
                        Authorized Signature
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            <p class="mb-1">Thank you for your payment!</p>
            <p class="mb-0">This is a computer-generated receipt. No signature is required.</p>
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
