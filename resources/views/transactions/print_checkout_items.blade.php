<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STB Delivery Detailed Receipt #{{ $voucher->voucher_number }} - Kerala Vision</title>
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
        .logo-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .brand-logo-icon {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.95rem;
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
        .badge-status {
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
    <div class="no-print d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success fs-7"><i class="bi bi-card-checklist me-1"></i> Item-Wise STB Delivery Details</span>
            <a href="{{ route('stb-checkout.print', $voucher->id) }}" class="btn btn-outline-secondary btn-sm py-1">
                <i class="bi bi-file-text me-1"></i> Switch to Summary Print
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-success btn-sm py-1 me-1"><i class="bi bi-printer me-1"></i> Print Details</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm py-1">Close</button>
        </div>
    </div>

    <!-- Header -->
    <div class="logo-header">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-logo-icon">KV</div>
            <div>
                <h5 class="fw-bold m-0 text-uppercase tracking-wider" style="font-size: 15px; letter-spacing: 0.5px;">Kerala Vision</h5>
                <div class="text-muted fw-bold" style="font-size: 10px;">Service & Technical Support Center</div>
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-success text-white px-2 py-1" style="font-size: 10px;">STB DELIVERY RECEIPT (ITEM DETAILS)</span>
            <div class="fw-bold mt-1 text-dark" style="font-size: 15px;">{{ $voucher->voucher_number }}</div>
        </div>
    </div>

    <!-- Meta Info Box -->
    <div class="row g-2 mb-2">
        <div class="col-6">
            <table class="meta-table table table-sm table-bordered m-0">
                <tr>
                    <td class="bg-light fw-bold" style="width: 32%;">Cable Operator:</td>
                    <td><strong>{{ $voucher->operator->operator_name ?? 'N/A' }}</strong> ({{ $voucher->operator->operator_code ?? 'N/A' }})</td>
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
                    <td class="bg-light fw-bold" style="width: 36%;">Delivery Date:</td>
                    <td><strong>{{ \Carbon\Carbon::parse($voucher->checkout_date)->format('d M Y') }}</strong></td>
                </tr>
                <tr>
                    <td class="bg-light fw-bold">Total Delivered:</td>
                    <td><span class="badge bg-success px-2 py-1" style="font-size: 10.5px;">{{ $voucher->total_boxes }} Units</span> (Issued by: {{ $voucher->creator->name ?? 'System' }})</td>
                </tr>
            </table>
        </div>
    </div>

    @if($voucher->remarks)
    <div class="alert alert-light border py-1 px-2 small mb-2" style="font-size: 10px; line-height: 1.3;">
        <strong>Delivery Remarks:</strong> {{ $voucher->remarks }}
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-1">
        <strong style="font-size: 11px;">Delivered STB Item Details:</strong>
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
                    <th style="width: 155px;">Delivery Status</th>
                    <th style="width: 130px;" class="text-start">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $testedOkCount = 0;
                    $flashCount = 0;
                    $softwareIssueCount = 0;
                @endphp

                @forelse($voucher->items as $index => $item)
                    @php
                        $modelName = $item->setTopBox->boxModel->model_name ?? ($item->setTopBox->box_name ?? 'Unknown Model');
                        $status = $item->stb_status;

                        // Fallback for vouchers saved when stb_status column was null or set to delivered
                        if (empty($status) || $status === 'delivered') {
                            $box = $item->setTopBox;
                            if ($box && in_array($box->stb_status, ['tested_ok', 'flash', 'software_issue'])) {
                                $status = $box->stb_status;
                            } else {
                                $qc = \App\Models\QcCheck::where('set_top_box_id', $item->set_top_box_id)->latest()->first();
                                if ($qc && in_array($qc->qc_status, ['tested_ok', 'flash', 'software_issue'])) {
                                    $status = $qc->qc_status;
                                }
                            }
                        }

                        if ($status === 'flash') {
                            $flashCount++;
                        } elseif ($status === 'software_issue') {
                            $softwareIssueCount++;
                        } else {
                            $testedOkCount++;
                        }

                        $itemRemarks = $item->setTopBox->remarks ?? '';
                    @endphp
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <span class="barcode-text text-dark">{{ $item->barcode_number }}</span>
                        </td>
                        <td class="model-text text-dark">{{ $modelName }}</td>
                        <td class="text-center">
                            @if($status === 'flash')
                                <span class="badge-status bg-danger text-white border border-danger">Flash (Dead Box)</span>
                            @elseif($status === 'software_issue')
                                <span class="badge-status bg-danger-subtle text-danger border border-danger">Software Issue</span>
                            @else
                                <span class="badge-status bg-success-subtle text-success border border-success">Tested OK (QC Passed)</span>
                            @endif
                        </td>
                        <td class="text-muted" style="max-width: 140px; overflow: hidden; text-overflow: ellipsis;">{{ $itemRemarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-2">No items found in this delivery voucher.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-uppercase text-muted" style="font-size: 10px;">Summary of Delivered STBs:</td>
                    <td colspan="3" class="text-end" style="font-size: 10.5px;">
                        <span class="me-3">Tested OK: <span class="badge bg-success text-white px-2 py-1">{{ $testedOkCount }}</span></span>
                        @if($flashCount > 0)
                            <span class="me-3">Flash: <span class="badge bg-danger text-white px-2 py-1">{{ $flashCount }}</span></span>
                        @endif
                        @if($softwareIssueCount > 0)
                            <span class="me-3">Software Issue: <span class="badge bg-warning text-dark px-2 py-1">{{ $softwareIssueCount }}</span></span>
                        @endif
                        <span>Total Delivered: <span class="badge bg-dark text-white px-2 py-1">{{ count($voucher->items) }} Units</span></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatures -->
    <div class="row signature-section">
        <div class="col-6 text-center">
            <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold" style="font-size: 10.5px;">Operator Representative Signature</div>
        </div>
        <div class="col-6 text-center">
            <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold" style="font-size: 10.5px;">Authorized Service Center Signature</div>
        </div>
    </div>
</div>

</body>
</html>
