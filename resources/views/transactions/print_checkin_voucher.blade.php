<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>STB Check-In Voucher {{ $voucher->voucher_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; font-size: 13px; color: #1e293b; background: #fff; }
        .voucher-header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .meta-table td { padding: 4px 8px; }
        .summary-table th { background-color: #0f172a !important; color: #fff !important; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body class="p-4" onload="window.print()">

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="bi bi-printer"></i> Print Voucher</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
</div>

<div class="voucher-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold m-0 text-uppercase">Kerala Vision Service Center</h4>
        <div class="text-muted small">STB Box Intake / Check-In Summary Receipt</div>
    </div>
    <div class="text-end">
        <h5 class="fw-extrabold text-primary m-0">{{ $voucher->voucher_number }}</h5>
        <div class="text-muted small">Date: {{ \Carbon\Carbon::parse($voucher->checkin_date)->format('d M Y') }}</div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-6">
        <table class="meta-table table table-sm table-bordered">
            <tr>
                <td class="bg-light fw-bold" style="width: 35%;">Cable Operator:</td>
                <td><strong>{{ $voucher->operator->operator_name }}</strong> ({{ $voucher->operator->operator_code }})</td>
            </tr>
            <tr>
                <td class="bg-light fw-bold">Contact Person:</td>
                <td>{{ $voucher->operator->contact_person ?? 'N/A' }} ({{ $voucher->operator->mobile ?? 'N/A' }})</td>
            </tr>
        </table>
    </div>
    <div class="col-6">
        <table class="meta-table table table-sm table-bordered">
            <tr>
                <td class="bg-light fw-bold" style="width: 40%;">Total STB Count:</td>
                <td><span class="badge bg-primary fs-6">{{ $voucher->total_boxes }} Units</span></td>
            </tr>
            <tr>
                <td class="bg-light fw-bold">Checked In By:</td>
                <td>{{ $voucher->creator->name ?? 'System' }}</td>
            </tr>
        </table>
    </div>
</div>

@if($voucher->remarks)
<div class="alert alert-secondary py-2 small mb-3">
    <strong>Intake Remarks:</strong> {{ $voucher->remarks }}
</div>
@endif

<h6 class="fw-bold mb-2">Check-In Summary by STB Model:</h6>
<table class="table table-bordered table-striped align-middle small text-center mb-3 summary-table">
    <thead>
        <tr>
            <th style="width: 50px;">#</th>
            <th class="text-start">Set Top Box Model</th>
            <th style="width: 180px;">Regular Check-In Qty</th>
            <th style="width: 220px;">Reservice Box Qty (&lt; 30 Days)</th>
            <th style="width: 140px;">Total Quantity</th>
        </tr>
    </thead>
    <tbody>
                @php
                    $modelSummary = [];
                    $totalRegular = 0;
                    $totalReservice = 0;
                    $grandTotal = 0;

                    foreach ($voucher->items as $item) {
                        $modelName = $item->setTopBox->boxModel->model_name ?? ($item->setTopBox->box_name ?? 'Unknown Model');
                        
                        $status = $item->stb_status;

                        if (empty($status)) {
                            $box = $item->setTopBox;
                            if ($box && $box->stb_status === 'reservice') {
                                $status = 'reservice';
                            } else {
                                $latestCheckoutDate = \Illuminate\Support\Facades\DB::table('checkout_voucher_items')
                                    ->join('checkout_vouchers', 'checkout_voucher_items.checkout_voucher_id', '=', 'checkout_vouchers.id')
                                    ->where('checkout_voucher_items.set_top_box_id', $item->set_top_box_id)
                                    ->where('checkout_vouchers.checkout_date', '<=', $voucher->checkin_date)
                                    ->max('checkout_vouchers.checkout_date');

                                if ($latestCheckoutDate) {
                                    $diffDays = \Carbon\Carbon::parse($latestCheckoutDate)->diffInDays(\Carbon\Carbon::parse($voucher->checkin_date), false);
                                    if ($diffDays >= 0 && $diffDays <= 30) {
                                        $status = 'reservice';
                                    }
                                }
                            }
                        }

                        if (!isset($modelSummary[$modelName])) {
                            $modelSummary[$modelName] = [
                                'regular' => 0,
                                'reservice' => 0,
                                'total' => 0,
                            ];
                        }

                        if ($status === 'reservice') {
                            $modelSummary[$modelName]['reservice']++;
                            $totalReservice++;
                        } else {
                            $modelSummary[$modelName]['regular']++;
                            $totalRegular++;
                        }
                        $modelSummary[$modelName]['total']++;
                        $grandTotal++;
                    }
                    $rowNum = 1;
                @endphp

        @forelse($modelSummary as $modelName => $counts)
        <tr>
            <td class="fw-bold">{{ $rowNum++ }}</td>
            <td class="text-start fw-bold text-dark">{{ $modelName }}</td>
            <td><span class="badge bg-secondary-subtle text-dark fs-7 px-3">{{ $counts['regular'] }}</span></td>
            <td>
                @if($counts['reservice'] > 0)
                    <span class="badge bg-danger text-white fs-7 px-3 fw-bold">{{ $counts['reservice'] }}</span>
                @else
                    <span class="text-muted">0</span>
                @endif
            </td>
            <td><span class="badge bg-primary fs-7 px-3">{{ $counts['total'] }}</span></td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center text-muted">No items found in this voucher.</td>
        </tr>
        @endforelse
    </tbody>
    <tfoot class="table-light fw-bold">
        <tr>
            <td colspan="2" class="text-end text-uppercase">Total Boxes Received:</td>
            <td><span class="badge bg-dark fs-7 px-3">{{ $totalRegular }}</span></td>
            <td><span class="badge bg-danger fs-7 px-3">{{ $totalReservice }}</span></td>
            <td><span class="badge bg-primary fs-6 px-3">{{ $grandTotal }} Units</span></td>
        </tr>
    </tfoot>
</table>

<div class="small text-muted mb-4 border-start border-3 border-danger ps-2">
    <strong>NB:</strong> <em>Reservice Box</em> refers to Set Top Boxes re-checked in for servicing within 30 days of their previous checkout date.
</div>

<div class="row mt-5 pt-4">
    <div class="col-6 text-center">
        <div class="border-top pt-2 fw-bold small">Operator Representative Signature</div>
    </div>
    <div class="col-6 text-center">
        <div class="border-top pt-2 fw-bold small">Authorized Service Receiver Signature</div>
    </div>
</div>

</body>
</html>
