<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STB Check-In Detailed Items - {{ $voucher->voucher_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 8mm 6mm 8mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            font-size: 11px;
            color: #0f172a;
            background: #fff;
            line-height: 1.25;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .print-container {
            max-width: 820px;
            margin: 0 auto;
            width: 100%;
        }
        .voucher-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .meta-table {
            margin-bottom: 6px !important;
            font-size: 11px;
        }
        .meta-table td {
            padding: 2.5px 6px !important;
            line-height: 1.25;
        }
        .items-table {
            font-size: 11px !important;
            margin-bottom: 6px !important;
            width: 100%;
        }
        .items-table th {
            background-color: #0f172a !important;
            color: #fff !important;
            padding: 3.5px 6px !important;
            font-size: 10.5px !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap !important;
            vertical-align: middle;
            border: 1px solid #0f172a;
        }
        .items-table td {
            padding: 2.5px 6px !important;
            vertical-align: middle;
            white-space: nowrap !important;
            line-height: 1.2;
            border: 1px solid #cbd5e1;
        }
        .barcode-text {
            font-family: 'Consolas', 'Courier New', monospace;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: 0.5px;
            white-space: nowrap !important;
        }
        .model-text {
            white-space: nowrap !important;
            font-weight: 600;
        }
        .badge-intake {
            font-size: 9.5px !important;
            padding: 1.5px 6px !important;
            font-weight: 600;
            border-radius: 3px;
            display: inline-block;
            white-space: nowrap !important;
            line-height: 1.2;
        }
        .signature-section {
            margin-top: 18px;
            padding-top: 4px;
        }
        @media screen {
            body {
                background-color: #f1f5f9;
                padding: 16px 8px;
            }
            .print-container {
                background: #fff;
                padding: 20px 24px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }
        }
        @media print {
            .no-print { display: none !important; }
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body onload="window.print()">

<div class="print-container">
    <div class="no-print mb-3 d-flex justify-content-between align-items-center border-bottom pb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary fs-7"><i class="bi bi-card-checklist me-1"></i> Item-Wise STB Details</span>
            <a href="{{ route('stb-checkin.print', $voucher->id) }}" class="btn btn-outline-secondary btn-sm py-1">
                <i class="bi bi-file-text me-1"></i> Switch to Summary Print
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm py-1 me-1"><i class="bi bi-printer me-1"></i> Print Details</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm py-1">Close</button>
        </div>
    </div>

    <!-- Header -->
    <div class="voucher-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold m-0 text-uppercase tracking-wide" style="font-size: 15px; letter-spacing: 0.5px;">Kerala Vision Service Center</h5>
            <div class="text-muted" style="font-size: 10.5px;">STB Box Intake / Check-In Detailed Items Receipt</div>
        </div>
        <div class="text-end">
            <div class="fw-extrabold text-primary m-0" style="font-size: 15px;">{{ $voucher->voucher_number }}</div>
            <div class="text-muted" style="font-size: 10.5px;">Date: {{ \Carbon\Carbon::parse($voucher->checkin_date)->format('d M Y') }}</div>
        </div>
    </div>

    <!-- Meta Info Box -->
    <div class="row g-2 mb-2">
        <div class="col-6">
            <table class="meta-table table table-sm table-bordered m-0">
                <tr>
                    <td class="bg-light fw-bold" style="width: 32%;">Cable Operator:</td>
                    <td><strong>{{ $voucher->operator->operator_name }}</strong> ({{ $voucher->operator->operator_code }})</td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Contact Person:</td>
                    <td>{{ $voucher->operator->contact_person ?: 'N/A' }} {{ $voucher->operator->mobile ? '(' . $voucher->operator->mobile . ')' : '' }}</td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <table class="meta-table table table-sm table-bordered m-0">
                <tr>
                    <td class="bg-light fw-bold" style="width: 36%;">Total STB Count:</td>
                    <td><span class="badge bg-primary px-2 py-1" style="font-size: 10.5px;">{{ $voucher->total_boxes }} Units</span></td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Checked In By:</td>
                    <td>{{ $voucher->creator->name ?? 'System' }}</td>
                </tr>
            </table>
        </div>
    </div>

    @if($voucher->remarks)
    <div class="alert alert-secondary py-1 px-2 small mb-2" style="font-size: 10px; line-height: 1.3;">
        <strong>Intake Remarks:</strong> {{ $voucher->remarks }}
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-1">
        <strong style="font-size: 11px;">Item-Wise Set Top Box Intake List:</strong>
        <span class="text-muted" style="font-size: 10px;">{{ count($voucher->items) }} items</span>
    </div>

    <!-- Item Details Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle items-table">
            <thead>
                <tr class="text-center">
                    <th style="width: 34px;">#</th>
                    <th style="width: 145px;" class="text-start">Barcode / Serial Number</th>
                    <th class="text-start" style="min-width: 160px;">Set Top Box Model</th>
                    <th style="width: 155px;">Intake Status</th>
                    <th style="width: 130px;" class="text-start">Remarks</th>
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
                        <td class="text-center fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td>
                            <span class="barcode-text text-dark">{{ $item->barcode_number }}</span>
                        </td>
                        <td class="model-text text-dark">{{ $modelName }}</td>
                        <td class="text-center">
                            @if($isReservice)
                                <span class="badge-intake bg-danger text-white border border-danger">Reservice (&lt; 30 Days)</span>
                            @else
                                <span class="badge-intake bg-secondary-subtle text-dark border border-secondary-subtle">Regular Complaint</span>
                            @endif
                        </td>
                        <td class="text-muted" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis;">{{ $itemRemarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-2">No STB items found in this voucher.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-uppercase text-muted" style="font-size: 10px;">Summary of Boxes:</td>
                    <td colspan="3" class="text-end" style="font-size: 10.5px;">
                        <span class="me-3">Regular Complaint: <span class="badge bg-secondary text-white px-2 py-1">{{ $regularCount }}</span></span>
                        <span class="me-3">Reservice (&lt; 30 Days): <span class="badge bg-danger text-white px-2 py-1">{{ $reserviceCount }}</span></span>
                        <span>Grand Total: <span class="badge bg-primary text-white px-2 py-1">{{ count($voucher->items) }} Units</span></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="text-muted mb-2 border-start border-3 border-danger ps-2" style="font-size: 9.5px; line-height: 1.2;">
        <strong>NB:</strong> <em>Reservice Box</em> refers to Set Top Boxes re-checked in for servicing within 30 days of their previous checkout date.
    </div>

    <!-- Signatures -->
    <div class="row signature-section">
        <div class="col-6 text-center">
            <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold" style="font-size: 10.5px;">Operator Representative Signature</div>
        </div>
        <div class="col-6 text-center">
            <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold" style="font-size: 10.5px;">Authorized Service Receiver Signature</div>
        </div>
    </div>
</div>

</body>
</html>
