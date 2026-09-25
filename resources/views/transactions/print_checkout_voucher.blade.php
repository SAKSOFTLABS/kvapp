<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STB Delivery Receipt #{{ $voucher->voucher_number }} - Kerala Vision</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #fff; color: #1e293b; padding: 20px; }
        .receipt-card { border: 2px solid #0f172a; border-radius: 12px; padding: 25px; max-width: 800px; margin: 0 auto; }
        .logo-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .brand-logo-icon { background: linear-gradient(135deg, #059669, #10b981); color: #fff; width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; }
        .summary-table th { background-color: #0f172a !important; color: #fff !important; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .receipt-card { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-success px-4 py-2 fw-bold shadow-sm">
            <i class="bi bi-printer me-1"></i> Print Delivery Receipt / Voucher
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary px-3 py-2 ms-2">Close Window</button>
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
                <span class="badge bg-success text-white fs-6 px-3 py-2">STB DELIVERY RECEIPT</span>
                <div class="fw-bold mt-1 text-dark fs-5">{{ $voucher->voucher_number }}</div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <div class="text-muted small uppercase fw-bold">Issued To Cable Operator:</div>
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

        <h6 class="fw-bold mb-2">Delivery Summary by STB Model:</h6>
        <table class="table table-bordered table-striped align-middle small text-center mb-4 summary-table">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th class="text-start">Set Top Box Model</th>
                    <th style="width: 180px;">Tested OK Qty</th>
                    <th style="width: 200px;">Flash Qty (Dead Box)</th>
                    <th style="width: 160px;">Total Delivered</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $modelSummary = [];
                    $totalTestedOk = 0;
                    $totalFlash = 0;
                    $grandTotal = 0;

                    foreach ($voucher->items as $item) {
                        $modelName = $item->setTopBox->boxModel->model_name ?? ($item->setTopBox->box_name ?? 'Unknown Model');
                        
                        $status = $item->stb_status;

                        // Fallback for vouchers saved when stb_status column was null or set to delivered
                        if (empty($status) || $status === 'delivered') {
                            $box = $item->setTopBox;
                            if ($box && in_array($box->stb_status, ['tested_ok', 'flash'])) {
                                $status = $box->stb_status;
                            } else {
                                // Check latest QC inspection record for this STB
                                $qc = \App\Models\QcCheck::where('set_top_box_id', $item->set_top_box_id)->latest()->first();
                                if ($qc && in_array($qc->qc_status, ['tested_ok', 'flash'])) {
                                    $status = $qc->qc_status;
                                }
                            }
                        }

                        if (!isset($modelSummary[$modelName])) {
                            $modelSummary[$modelName] = [
                                'tested_ok' => 0,
                                'flash' => 0,
                                'total' => 0,
                            ];
                        }

                        if ($status === 'flash') {
                            $modelSummary[$modelName]['flash']++;
                            $totalFlash++;
                        } else {
                            $modelSummary[$modelName]['tested_ok']++;
                            $totalTestedOk++;
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
                    <td><span class="badge bg-success-subtle text-success border border-success fs-7 px-3">{{ $counts['tested_ok'] }}</span></td>
                    <td>
                        @if($counts['flash'] > 0)
                            <span class="badge bg-danger-subtle text-danger border border-danger fs-7 px-3 fw-bold">{{ $counts['flash'] }}</span>
                        @else
                            <span class="text-muted">0</span>
                        @endif
                    </td>
                    <td><span class="badge bg-success fs-7 px-3">{{ $counts['total'] }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No items found in this delivery voucher.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light fw-bold">
                <tr>
                    <td colspan="2" class="text-end text-uppercase">Total Delivered:</td>
                    <td><span class="badge bg-success fs-7 px-3">{{ $totalTestedOk }}</span></td>
                    <td><span class="badge bg-danger fs-7 px-3">{{ $totalFlash }}</span></td>
                    <td><span class="badge bg-dark fs-6 px-3">{{ $grandTotal }} Units</span></td>
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
