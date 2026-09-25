<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>STB History Report - {{ $selectedBox->barcode_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; font-size: 12px; color: #0f172a; background: #fff; padding: 20px; }
        .receipt-card { border: 2px solid #0f172a; border-radius: 10px; padding: 20px; max-width: 900px; margin: 0 auto; }
        .logo-header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt-card { border: none; padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print mb-3 text-center">
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> Print History Report</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">Close Window</button>
</div>

<div class="receipt-card">
    <div class="logo-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold m-0 text-uppercase">Kerala Vision Service Center</h4>
            <div class="text-muted small">STB Box Lifecycle History Report</div>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 px-3 py-1">STB HISTORY</span>
            <div class="fw-bold mt-1 fs-5">{{ $selectedBox->barcode_number }}</div>
            <div class="text-muted small">Printed: {{ date('d M Y, h:i A') }}</div>
        </div>
    </div>

    <!-- Box Summary Table -->
    <table class="table table-sm table-bordered mb-4 align-middle">
        <tr class="table-light">
            <th style="width: 20%;">Barcode Number:</th>
            <td style="width: 30%;"><strong class="fs-6 text-primary">{{ $selectedBox->barcode_number }}</strong></td>
            <th style="width: 20%;">Box Model:</th>
            <td style="width: 30%;">{{ $selectedBox->boxModel->model_name ?? $selectedBox->box_name }}</td>
        </tr>
        <tr>
            <th>Current Cable Operator:</th>
            <td>{{ $selectedBox->operator->operator_name ?? 'Unassigned' }}</td>
            <th>Current Status:</th>
            <td><span class="badge bg-dark">{{ $selectedBox->status_label }}</span></td>
        </tr>
    </table>

    <h6 class="fw-bold mb-3 border-bottom pb-2">Timeline Events (Sorted: {{ $sortOrder === 'asc' ? 'Oldest First' : 'Newest First' }}):</h6>

    @foreach($events as $idx => $event)
    <div class="card mb-3 border">
        <div class="card-header bg-light py-1.5 px-3 d-flex justify-content-between align-items-center">
            <strong class="text-dark">#{{ $idx + 1 }} - {{ $event['title'] }}</strong>
            <span class="text-muted small">{{ \Carbon\Carbon::parse($event['date_time'])->format('d M Y, h:i A') }} (User: {{ $event['user_name'] }})</span>
        </div>
        <div class="card-body py-2 px-3">
            <div class="row g-2 small">
                @foreach($event['details'] as $key => $val)
                <div class="col-4">
                    <span class="text-muted">{{ $key }}:</span> <strong>{{ $val }}</strong>
                </div>
                @endforeach
            </div>

            @if(count($event['spare_parts']) > 0)
            <div class="mt-2 pt-2 border-top">
                <div class="fw-bold small mb-1">Spare Parts Used:</div>
                <table class="table table-sm table-bordered align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>Part Code</th>
                            <th class="text-start">Part Name</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($event['spare_parts'] as $part)
                        <tr>
                            <td>{{ $part['item_code'] }}</td>
                            <td class="text-start">{{ $part['item_name'] }}</td>
                            <td>{{ $part['quantity'] }}</td>
                            <td>₹{{ number_format($part['unit_cost'], 2) }}</td>
                            <td><strong>₹{{ number_format($part['total_cost'], 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="row pt-4 mt-4 border-top">
        <div class="col-6 text-center">
            <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold small">Service Manager Signature</div>
        </div>
        <div class="col-6 text-center">
            <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold small">Authorized Center Stamp</div>
        </div>
    </div>
</div>

</body>
</html>
