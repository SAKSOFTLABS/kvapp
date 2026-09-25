<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service Report - {{ $box->barcode_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 2rem; background: #fff; color: #000; }
        .invoice-box { max-width: 800px; margin: auto; border: 1px solid #ddd; padding: 2rem; border-radius: 12px; }
        @media print {
            .no-print { display: none !important; }
            .invoice-box { border: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <h2 class="fw-bold mb-0 text-primary">KERALA VISION</h2>
                <div class="text-muted small">Digital Service Management & Support System</div>
            </div>
            <div class="text-end">
                <button onclick="window.print()" class="btn btn-primary btn-sm no-print mb-1">Print Report</button>
                <div class="small text-muted">Generated: {{ date('d M Y, h:i A') }}</div>
            </div>
        </div>

        <h4 class="fw-bold text-center mb-4">SET TOP BOX SERVICE HISTORY REPORT</h4>

        <div class="row bg-light p-3 rounded mb-4 border">
            <div class="col-6">
                <div><strong>Box Model:</strong> {{ $box->box_name }}</div>
                <div><strong>Barcode Number:</strong> <code class="fs-6 text-danger">{{ $box->barcode_number }}</code></div>
            </div>
            <div class="col-6 text-end">
                <div><strong>Status:</strong> {{ strtoupper($box->status) }}</div>
                <div><strong>Total Service Cost:</strong> <span class="fw-bold fs-5 text-success">₹{{ number_format($totalServiceCost, 2) }}</span></div>
            </div>
            @if($box->remarks)
            <div class="col-12 mt-2 pt-2 border-top small text-muted">
                <strong>Remarks / Customer:</strong> {{ $box->remarks }}
            </div>
            @endif
        </div>

        @foreach($box->serviceHistory as $srv)
        <div class="card mb-4 border">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold text-primary">{{ $srv->service_code }}</span>
                <span class="small text-muted">Date: {{ \Carbon\Carbon::parse($srv->service_date)->format('d M Y') }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-2 small">
                    <div class="col-6"><strong>Technician:</strong> {{ $srv->technician->name ?? 'N/A' }}</div>
                    <div class="col-6 text-end"><strong>Service Cost:</strong> ₹{{ number_format($srv->total_cost, 2) }}</div>
                </div>
                @if($srv->remarks)
                <p class="small text-muted mb-3"><strong>Work Remarks:</strong> {{ $srv->remarks }}</p>
                @endif
                <table class="table table-sm table-bordered small">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($srv->items as $sItem)
                        <tr>
                            <td>{{ $sItem->item->item_name ?? 'N/A' }}</td>
                            <td>{{ number_format($sItem->quantity, 2) }}</td>
                            <td>₹{{ number_format($sItem->unit_price, 2) }}</td>
                            <td>₹{{ number_format($sItem->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach

        <div class="mt-5 pt-4 border-top d-flex justify-content-between small text-muted">
            <div>Authorised Signatory: __________________</div>
            <div>Kerala Vision Digital Services</div>
        </div>
    </div>
</body>
</html>
