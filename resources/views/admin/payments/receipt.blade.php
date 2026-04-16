<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #e2e8f0; font-family: 'Inter', sans-serif; color: #1e293b; }

        .receipt-page {
            background: #fff;
            max-width: 210mm;
            margin: 20px auto;
            padding: 40px 50px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.12);
        }

        .receipt-header {
            border-bottom: 3px solid #1e293b;
            padding-bottom: 24px;
            margin-bottom: 32px;
        }
        .company-name { font-size: 1.5rem; font-weight: 700; color: #1e293b; letter-spacing: 1px; }
        .company-contact { font-size: 0.8rem; color: #64748b; line-height: 1.6; }
        .receipt-title {
            font-size: 1.5rem; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: 2px;
        }
        .receipt-meta { font-size: 0.85rem; color: #475569; }

        .amount-box {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 2px solid #22c55e;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 24px 0;
        }
        .amount-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; color: #64748b; font-weight: 600; }
        .amount-value { font-size: 2rem; font-weight: 700; color: #16a34a; }

        .detail-grid {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .detail-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; font-weight: 600; margin-bottom: 4px; }
        .detail-value { font-size: 0.95rem; font-weight: 600; color: #1e293b; }

        .invoice-summary {
            background: #fafbfc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }
        .invoice-summary table { width: 100%; }
        .invoice-summary td { padding: 6px 0; font-size: 0.875rem; }
        .invoice-summary .remaining td { border-top: 2px solid #e2e8f0; font-weight: 600; padding-top: 10px; }

        .signature-area { margin-top: 60px; }
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
            margin-top: 32px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.75rem;
        }

        .print-actions { text-align: center; margin: 20px 0; }

        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .receipt-page { box-shadow: none; margin: 0; padding: 30px 40px; width: 100%; max-width: 100%; }
            .print-actions { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5 shadow">
            <i class="bi bi-printer me-2"></i> Print Receipt
        </button>
        <a href="{{ route('payments.index') }}" class="btn btn-secondary btn-lg px-4 shadow ms-2">Back to List</a>
    </div>

    <div class="receipt-page">
        <!-- Header -->
        <div class="receipt-header">
            <div class="row align-items-start">
                <div class="col-7">
                    <div class="company-name">TH TRADE CORPORATION</div>
                    <div class="company-contact mt-1">
                        Specialized Medical Equipment Supplier<br>
                        Dhaka, Bangladesh | +880-XXXX-XXXXXX
                    </div>
                </div>
                <div class="col-5 text-end">
                    <div class="receipt-title">Money Receipt</div>
                    <div class="receipt-meta mt-2">
                        <strong>Receipt #:</strong> REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}<br>
                        <strong>Date:</strong> {{ \Carbon\Carbon::parse($payment->date)->format('d M, Y') }}
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
                    <div class="detail-label">Received From</div>
                    <div class="detail-value">{{ $payment->customer->customer_name }}</div>
                    @if($payment->customer->hospital_name)
                        <div style="font-size: 0.8rem; color: #64748b;">{{ $payment->customer->hospital_name }}</div>
                    @endif
                    <div style="font-size: 0.8rem; color: #64748b;">{{ $payment->customer->mobile }}</div>
                </div>
                <div class="col-md-3">
                    <div class="detail-label">Payment For</div>
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

        <!-- Invoice Summary (if linked to invoice) -->
        @if($payment->invoice_id && $payment->invoice)
        <div class="invoice-summary">
            <h6 class="fw-bold mb-3" style="font-size: 0.85rem; text-transform: uppercase; color: #64748b; letter-spacing: 1px;">Invoice Summary</h6>
            <table>
                <tr>
                    <td class="text-muted">Invoice Total:</td>
                    <td class="text-end fw-bold">৳ {{ number_format($payment->invoice->net_payable, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Amount Paid Now:</td>
                    <td class="text-end fw-bold" style="color: #16a34a;">- ৳ {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr class="remaining">
                    <td style="color: #dc2626;">Remaining Balance:</td>
                    <td class="text-end" style="color: #dc2626;">৳ {{ number_format($payment->invoice->remaining_due, 2) }}</td>
                </tr>
            </table>
        </div>
        @endif

        @if($payment->note)
        <div class="mt-3 p-3" style="background: #fefce8; border: 1px solid #fde68a; border-radius: 6px;">
            <small class="text-muted"><strong>Note:</strong> {{ $payment->note }}</small>
        </div>
        @endif

        <!-- Signature -->
        <div class="signature-area">
            <div class="row">
                <div class="col-6">
                    <small class="text-muted">Processed by: {{ $payment->user->name ?? 'System' }}</small>
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

</body>
</html>
