<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eee; font-family: sans-serif; }
        .invoice-card { background: #fff; max-width: 800px; margin: 30px auto; padding: 40px; border-radius: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .invoice-header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .table thead th { background: #333; color: #fff; border: none; }
        .total-section { margin-top: 30px; }
        .total-section table { width: 300px; margin-left: auto; }
        .total-section td { padding: 5px 0; }
        .footer { margin-top: 50px; text-align: center; color: #777; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        @media print {
            body { background: #fff; margin: 0; padding: 0; }
            .invoice-card { box-shadow: none; margin: 0; width: 100%; max-width: 100%; border: none; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="container text-center mt-4 btn-print">
        <button onclick="window.print()" class="btn btn-primary btn-lg shadow">Print Invoice</button>
        <a href="{{ route('pos.index') }}" class="btn btn-secondary btn-lg shadow">Back to POS</a>
    </div>

    <div class="invoice-card">
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div>
                <h1 class="fw-bold mb-0">TH TRADE CORPORATION</h1>
                <p class="text-muted mb-0">High-Quality Accounts, Billing & Stock Management</p>
            </div>
            <div class="text-end">
                <h2 class="h4 text-uppercase text-muted">Invoice</h2>
                <h3 class="h5 mb-0"># {{ $invoice->invoice_no }}</h3>
                <p class="mb-0">Date: {{ date('d M Y', strtotime($invoice->date)) }}</p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-6">
                <h5 class="fw-bold">Billing To:</h5>
                <p class="mb-0"><strong>{{ $invoice->customer->name }}</strong></p>
                <p class="mb-0">{{ $invoice->customer->mobile }}</p>
                <p class="mb-0">{{ $invoice->customer->address }}</p>
            </div>
            <div class="col-6 text-end">
                <h5 class="fw-bold">Issued By:</h5>
                <p class="mb-0">User: {{ $invoice->user->name }}</p>
                <p class="mb-0">Role: {{ ucfirst($invoice->user->role) }}</p>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th width="50">SL</th>
                    <th>Product Description</th>
                    <th class="text-center" width="100">Qty</th>
                    <th class="text-end" width="120">Unit Price</th>
                    <th class="text-end" width="120">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">৳ {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-end">৳ {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <table class="table table-borderless">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-end fw-bold">৳ {{ number_format($invoice->sub_total, 2) }}</td>
                </tr>
                @if($invoice->discount_percent > 0)
                <tr>
                    <td>Discount ({{ $invoice->discount_percent }}%):</td>
                    <td class="text-end text-danger">- ৳ {{ number_format(($invoice->sub_total * $invoice->discount_percent) / 100, 2) }}</td>
                </tr>
                @endif
                @if($invoice->vat_percent > 0)
                <tr>
                    <td>VAT ({{ $invoice->vat_percent }}%):</td>
                    <td class="text-end">+ ৳ {{ number_format(($invoice->sub_total * $invoice->vat_percent) / 100, 2) }}</td>
                </tr>
                @endif
                @if($invoice->ait_percent > 0)
                <tr>
                    <td>AIT ({{ $invoice->ait_percent }}%):</td>
                    <td class="text-end">+ ৳ {{ number_format(($invoice->sub_total * $invoice->ait_percent) / 100, 2) }}</td>
                </tr>
                @endif
                @if($invoice->extra_charge > 0)
                <tr>
                    <td>Extra Charge:</td>
                    <td class="text-end">+ ৳ {{ number_format($invoice->extra_charge, 2) }}</td>
                </tr>
                @endif
                <tr class="border-top">
                    <td class="h5 fw-bold">Net Payable:</td>
                    <td class="h5 text-end fw-bold text-primary">৳ {{ number_format($invoice->net_payable, 2) }}</td>
                </tr>
                <tr>
                    <td>Received Amount:</td>
                    <td class="text-end fw-bold text-success">৳ {{ number_format($invoice->received_amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Due Amount:</td>
                    <td class="text-end fw-bold text-danger">৳ {{ number_format($invoice->due_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p class="mb-1">Thank you for your business!</p>
            <p class="mb-0Small">Software Developed by Antigravity AI</p>
        </div>
    </div>

</body>
</html>
