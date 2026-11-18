<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STB Delivery Detailed Receipt #{{ $voucher->voucher_number }} - Kerala Vision</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1e293b; padding: 20px; }
        .receipt-card { border: 2px solid #0f172a; border-radius: 12px; padding: 25px; max-width: 900px; margin: 0 auto; }
        .logo-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .brand-logo-icon { background: linear-gradient(135deg, #059669, #10b981); color: #fff; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
        .items-table th { background-color: #0f172a !important; color: #fff !important; }
        .barcode-text { font-family: 'Consolas', 'Courier New', monospace; font-weight: 700; letter-spacing: 0.5px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; }
            .receipt-card { border: none !important; padding: 0 !important; max-width: 100% !important; }
            .items-table { font-size: 11px; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print d-flex justify-content-between align-items-center mb-4 max-w-900 mx-auto" style="max-width: 900px;">
        <div>
            <span class="badge bg-success fs-7 me-2"><i class="bi bi-card-checklist me-1"></i> Item-Wise STB Delivery Details</span>
            <a href="{{ route('stb-checkout.print', $voucher->id) }}" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-file-text me-1"></i> Switch to Summary Print
            </a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-success px-4 py-2 fw-bold shadow-sm me-2">
                <i class="bi bi-printer me-1"></i> Print Detailed Receipt
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2">Close</button>
        </div>
    </div>

    <div class="receipt-card">
        <div class="logo-header">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-logo-icon">KV</div>
                <div>
                    <h4 class="fw-bold m-0 text-uppercase tracking-wider">Kerala Vision</h4>
                    <div class="small text-muted fw-bold">Service & Technical Support Center</div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-success text-white fs-6 px-3 py-2">STB DELIVERY RECEIPT (ITEM DETAILS)</span>
                <div class="fw-bold mt-1 text-dark fs-5">{{ $voucher->voucher_number }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="text-muted small text-uppercase fw-bold">Issued To Cable Operator:</div>
                <h5 class="fw-bold m-0 text-dark">{{ $voucher->operator->operator_name ?? 'N/A' }}</h5>
                <div>Operator Code: <strong>{{ $voucher->operator->operator_code ?? 'N/A' }}</strong></div>
                <div>Contact Person: {{ $voucher->operator->contact_person ?? 'N/A' }}</div>
                <div>Mobile: {{ $voucher->operator->mobile ?? 'N/A' }}</div>
            </div>
            <div class="col-6 text-end">
                <div><strong>Delivery Date:</strong> {{ \Carbon\Carbon::parse($voucher->checkout_date)->format('d M Y') }}</div>
                <div><strong>Total STB Units Delivered:</strong> <span class="fs-5 fw-bold text-success">{{ $voucher->total_boxes }}</span></div>
                <div><strong>Issued By:</strong> {{ $voucher->creator->name ?? 'System Admin' }}</div>
            </div>
        </div>

        @if($voucher->remarks)
        <div class="alert alert-light border small mb-4">
            <strong>Delivery Remarks:</strong> {{ $voucher->remarks }}
        </div>
        @endif

        <h6 class="fw-bold mb-2">Delivered STB Item Details:</h6>
        <table class="table table-bordered table-striped align-middle small mb-4 items-table">
            <thead>
                <tr class="text-center">
                    <th style="width: 45px;">#</th>
                    <th style="width: 220px;" class="text-start">Barcode / Serial Number</th>
                    <th class="text-start">Set Top Box Model</th>
                    <th style="width: 190px;">Delivery Status</th>
                    <th style="width: 200px;" class="text-start">Remarks</th>
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
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td>
                            <span class="barcode-text text-dark">{{ $item->barcode_number }}</span>
                        </td>
                        <td class="fw-semibold text-dark">{{ $modelName }}</td>
                        <td class="text-center">
                            @if($status === 'flash')
                                <span class="badge bg-danger text-white border border-danger px-2 py-1 fw-bold">Flash (Dead Box)</span>
                            @elseif($status === 'software_issue')
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1 fw-bold">Software Issue</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1 fw-bold">Tested OK (QC Passed)</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $itemRemarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No items found in this delivery voucher.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold text-center">
                <tr>
                    <td colspan="3" class="text-end text-uppercase">Summary of Delivered STBs:</td>
                    <td colspan="2" class="text-start">
                        <span class="me-3">Tested OK: <span class="badge bg-success px-2">{{ $testedOkCount }}</span></span>
                        @if($flashCount > 0)
                            <span class="me-3">Flash: <span class="badge bg-danger px-2">{{ $flashCount }}</span></span>
                        @endif
                        @if($softwareIssueCount > 0)
                            <span class="me-3">Software Issue: <span class="badge bg-warning text-dark px-2">{{ $softwareIssueCount }}</span></span>
                        @endif
                        <span>Grand Total: <span class="badge bg-dark px-3 fs-7">{{ count($voucher->items) }} Units</span></span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="row pt-5 mt-4 border-top">
            <div class="col-6 text-center">
                <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold small">Operator Representative Signature</div>
            </div>
            <div class="col-6 text-center">
                <div class="border-top border-dark pt-1 w-75 mx-auto fw-bold small">Authorized Service Center Signature</div>
            </div>
        </div>
    </div>
</body>
</html>
