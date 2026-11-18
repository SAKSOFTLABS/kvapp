<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>STB Check-In Detailed Items - {{ $voucher->voucher_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; font-size: 13px; color: #1e293b; background: #fff; }
        .voucher-header { border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 16px; }
        .meta-table td { padding: 4px 8px; }
        .items-table th { background-color: #0f172a !important; color: #fff !important; }
        .barcode-text { font-family: 'Consolas', 'Courier New', monospace; font-weight: 700; letter-spacing: 0.5px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; }
            .items-table { font-size: 11px; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="p-4" onload="window.print()">

<div class="no-print mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
    <div>
        <span class="badge bg-primary fs-7 me-2"><i class="bi bi-card-checklist me-1"></i> Item-Wise STB Details Print</span>
        <a href="{{ route('stb-checkin.print', $voucher->id) }}" class="btn btn-outline-secondary btn-sm me-2">
            <i class="bi bi-file-text me-1"></i> Switch to Summary Print
        </a>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary btn-sm me-1"><i class="bi bi-printer me-1"></i> Print Details</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
    </div>
</div>

<div class="voucher-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="fw-bold m-0 text-uppercase">Kerala Vision Service Center</h4>
        <div class="text-muted small">STB Box Intake / Check-In Detailed Items Receipt</div>
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

<h6 class="fw-bold mb-2">Item-Wise Set Top Box Intake List:</h6>
<table class="table table-bordered table-striped align-middle small mb-3 items-table">
    <thead>
        <tr class="text-center">
            <th style="width: 45px;">#</th>
            <th style="width: 220px;" class="text-start">Barcode / Serial Number</th>
            <th class="text-start">Set Top Box Model</th>
            <th style="width: 190px;">Intake Status</th>
            <th style="width: 200px;" class="text-start">Remarks</th>
        </tr>
    </thead>
    <tbody>
        @php
            $regularCount = 0;
            $reserviceCount = 0;
        @endphp

        @forelse($voucher->items as $idx => $item)
            @php
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

                $isReservice = ($status === 'reservice');
                if ($isReservice) {
                    $reserviceCount++;
                } else {
                    $regularCount++;
                }

                $modelName = $item->setTopBox->boxModel->model_name ?? ($item->setTopBox->box_name ?? 'Unknown Model');
                $itemRemarks = $item->setTopBox->remarks ?? '';
            @endphp
            <tr>
                <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                <td>
                    <span class="barcode-text text-dark">{{ $item->barcode_number }}</span>
                </td>
                <td class="fw-semibold text-dark">{{ $modelName }}</td>
                <td class="text-center">
                    @if($isReservice)
                        <span class="badge bg-danger text-white px-2 py-1 fw-bold">Reservice (&lt; 30 Days)</span>
                    @else
                        <span class="badge bg-secondary-subtle text-dark border px-2 py-1">Regular Complaint</span>
                    @endif
                </td>
                <td class="small text-muted">{{ $itemRemarks ?: '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-3">No STB items found in this voucher.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot class="table-light fw-bold text-center">
        <tr>
            <td colspan="3" class="text-end text-uppercase">Summary of Boxes:</td>
            <td colspan="2" class="text-start">
                <span class="me-3">Regular Complaint: <span class="badge bg-dark px-2">{{ $regularCount }}</span></span>
                <span class="me-3">Reservice (&lt; 30 Days): <span class="badge bg-danger px-2">{{ $reserviceCount }}</span></span>
                <span>Grand Total: <span class="badge bg-primary px-3 fs-7">{{ count($voucher->items) }} Units</span></span>
            </td>
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
