@extends('layouts.app')

@section('title', 'STB Box History Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i> STB Box Full History Report</h3>
    </div>
    @if(isset($selectedBox) && $selectedBox)
    <div class="d-flex gap-2">
        <a href="{{ route('reports.stb-history.print', ['id' => $selectedBox->id, 'sort' => $sortOrder]) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-3">
            <i class="bi bi-printer me-1"></i> Print History Report
        </a>
        <a href="{{ route('reports.stb-history.export', ['stb_id' => $selectedBox->id]) }}" class="btn btn-success btn-sm rounded-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
        </a>
    </div>
    @endif
</div>

<!-- STB Barcode Search & Scan Filter Card (Identical to STB Check-In) -->
<div class="kv-card mb-4">
    <form action="{{ route('reports.stb-history') }}" method="GET" id="stbHistoryForm">
        <input type="hidden" name="sort" id="sortInput" value="{{ $sortOrder }}">
        
        <div class="p-3 bg-light rounded-3 border">
            <label class="form-label fw-bold small text-dark d-flex flex-wrap justify-content-between align-items-center mb-2">
                <span><i class="bi bi-qr-code-scan me-1 text-primary"></i> Search STB Barcode History (Scan or Type Barcode)</span>
                <span class="text-muted small">Point laser barcode scanner or camera at STB label</span>
            </label>
            <div class="input-group">
                <button class="btn btn-outline-secondary px-3" type="button" onclick="startCameraScanner('#stbBarcodeScanInput')" title="Click to scan barcode using camera">
                    <i class="bi bi-camera-fill text-primary fs-5"></i>
                </button>
                <input type="text" name="search" id="stbBarcodeScanInput" class="form-control form-control-kv fs-5 fw-bold" placeholder="Scan barcode with laser scanner or camera..." value="{{ $search ?? '' }}" autofocus autocomplete="off">
                <button class="btn btn-primary btn-kv-primary px-4 fw-bold" type="submit">
                    <i class="bi bi-search me-1"></i> Search History
                </button>
                <a href="{{ route('reports.stb-history') }}" class="btn btn-outline-secondary px-3" title="Reset Search">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

@if(isset($selectedBox) && $selectedBox)
<!-- Clean 2-Line Left-Aligned STB Summary Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-3 p-3" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff;">
    <div class="d-flex align-items-center gap-3">
        <div class="brand-logo-icon rounded-3 bg-primary text-white flex-shrink-0" style="width: 42px; height: 42px; font-size: 1.1rem;">
            <i class="bi bi-tv-fill"></i>
        </div>
        <div class="text-start">
            <!-- Line 1: Barcode | Model | Operator -->
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <span class="fw-bold fs-5 text-white font-monospace">{{ $selectedBox->barcode_number }}</span>
                <span class="text-white-50">|</span>
                <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-2 py-1 fs-7 fw-bold">{{ $selectedBox->boxModel->model_name ?? $selectedBox->box_name }}</span>
                <span class="text-white-50">|</span>
                <span class="text-white-50 small">Operator: <strong class="text-white">{{ $selectedBox->operator->operator_name ?? 'Unassigned' }}</strong></span>
            </div>

            <!-- Line 2: Status | Delivered | Events -->
            <div class="d-flex align-items-center gap-2 flex-wrap small">
                <span class="text-white-50">Status: <span class="badge {{ $selectedBox->status_badge_class }} ms-1">{{ $selectedBox->status_label }}</span></span>

                @if(isset($latestCheckout) && $latestCheckout && ($latestCheckout->checkoutVoucher || $latestCheckout->voucher))
                @php $lVoucher = $latestCheckout->checkoutVoucher ?? $latestCheckout->voucher; @endphp
                <span class="text-white-50">|</span>
                <span class="text-white-50">Delivered: <span class="badge bg-success text-white ms-1"><i class="bi bi-box-arrow-up-right me-1"></i> {{ $lVoucher->operator->operator_name ?? 'Operator' }}</span></span>
                @endif

                <span class="text-white-50">|</span>
                <span class="text-white-50">Events: <strong class="text-white badge bg-white bg-opacity-10 ms-1">{{ count($events) }}</strong></span>
            </div>
        </div>
    </div>
</div>

<!-- Excel View History Table -->
<div class="kv-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="bi bi-file-earmark-excel-fill text-success me-2"></i> Date & Time-Wise STB History Timeline (Excel Grid View)
        </h5>
        
        <!-- Sorting Controls inside Report Header -->
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-muted"><i class="bi bi-sort-down text-primary me-1"></i> Sort Report:</span>
            <select class="form-select form-select-sm fw-bold border-primary" style="width: 220px;" onchange="document.getElementById('sortInput').value=this.value; document.getElementById('stbHistoryForm').submit();">
                <option value="desc" {{ $sortOrder === 'desc' ? 'selected' : '' }}>Newest First (Desc ⬇)</option>
                <option value="asc" {{ $sortOrder === 'asc' ? 'selected' : '' }}>Oldest First (Asc ⬆)</option>
            </select>
        </div>
    </div>

    @if(count($events) > 0)
    <div class="table-responsive border rounded-3 overflow-hidden shadow-sm">
        <table class="table table-bordered table-striped align-middle small text-center mb-0">
            <thead class="table-dark text-nowrap">
                <tr>
                    <th style="width: 45px;">#</th>
                    <th style="width: 170px;">Date & Time</th>
                    <th style="width: 160px;">Event Category</th>
                    <th style="width: 190px;">Operator / User</th>
                    <th style="width: 160px;">Status / Result</th>
                    <th class="text-start">Voucher / Code # & Spare Parts Details</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $index => $event)
                <tr>
                    <td class="fw-bold">{{ $index + 1 }}</td>
                    <td class="text-nowrap fw-bold text-dark">
                        <div><i class="bi bi-calendar-event text-primary me-1"></i> {{ \Carbon\Carbon::parse($event['date_time'])->format('d M Y') }}</div>
                        <div class="small text-muted font-monospace"><i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($event['date_time'])->format('h:i:s A') }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $event['badge_class'] }} px-2.5 py-1 fs-7 fw-bold">
                            <i class="bi {{ $event['icon'] }} me-1"></i> {{ $event['title'] }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $event['operator_name'] ?? 'N/A' }}</div>
                        <div class="small text-muted"><i class="bi bi-person me-1"></i> {{ $event['user_name'] }}</div>
                    </td>
                    <td>
                        @if(!empty($event['status_badge_class']))
                        <span class="badge {{ $event['status_badge_class'] }} px-2.5 py-1 fw-bold fs-7">
                            {{ $event['status'] ?? 'N/A' }}
                        </span>
                        @elseif(($event['status'] ?? '') === 'Complaint')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning px-2.5 py-1 fw-bold fs-7">
                            {{ $event['status'] }}
                        </span>
                        @else
                        <span class="badge bg-secondary-subtle text-dark border border-secondary px-2.5 py-1 fw-bold fs-7">
                            {{ $event['status'] ?? 'N/A' }}
                        </span>
                        @endif
                    </td>
                    <td class="text-start">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <code class="fw-bold text-primary fs-6">{{ $event['voucher_number'] ?? 'N/A' }}</code>
                            @if($event['event_type'] === 'SERVICE' && !empty($event['details']['Total Repair Cost']))
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning fw-bold">{{ $event['details']['Total Repair Cost'] }}</span>
                            @endif
                        </div>

                        <!-- Issues & Action Summary -->
                        @if(!empty($event['details']['Issues Reported']) && $event['details']['Issues Reported'] !== 'N/A')
                        <div class="small text-muted">
                            <strong>Issue:</strong> {{ $event['details']['Issues Reported'] }}
                        </div>
                        @endif
                        @if(!empty($event['details']['Action / Repair Done']) && $event['details']['Action / Repair Done'] !== 'N/A')
                        <div class="small text-muted">
                            <strong>Action:</strong> {{ $event['details']['Action / Repair Done'] }}
                        </div>
                        @endif
                        @if(!empty($event['details']['Remarks']) && $event['details']['Remarks'] !== 'N/A')
                        <div class="small text-muted fst-italic">
                            <strong>Remarks:</strong> {{ $event['details']['Remarks'] }}
                        </div>
                        @endif

                        <!-- Spare Parts Used Below Voucher Code -->
                        @if(count($event['spare_parts']) > 0)
                        <div class="mt-2 pt-2 border-top">
                            <div class="fw-bold small text-warning-emphasis mb-1">
                                <i class="bi bi-cpu text-warning me-1"></i> Spare Parts Used:
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered align-middle mb-0 text-center" style="font-size: 0.8rem; background: #fff;">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">#</th>
                                            <th class="text-start">Spare Part Code & Name</th>
                                            <th style="width: 60px;">Qty</th>
                                            <th style="width: 80px;">Unit Price</th>
                                            <th style="width: 90px;">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($event['spare_parts'] as $idx => $part)
                                        <tr>
                                            <td class="fw-bold">{{ $idx + 1 }}</td>
                                            <td class="text-start">
                                                <span class="badge bg-secondary-subtle text-dark me-1">{{ $part['item_code'] }}</span>
                                                <strong>{{ $part['item_name'] }}</strong>
                                            </td>
                                            <td><span class="badge bg-info-subtle text-info px-2">{{ $part['quantity'] }}</span></td>
                                            <td>₹{{ number_format($part['unit_cost'], 2) }}</td>
                                            <td class="fw-bold">₹{{ number_format($part['total_cost'], 2) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox fs-1 opacity-25 d-block mb-2"></i>
        No history events recorded yet for STB Barcode <strong>{{ $selectedBox->barcode_number }}</strong>.
    </div>
    @endif
</div>
@else
<!-- Empty State Prompt -->
<div class="kv-card text-center py-5">
    <div class="py-4">
        <i class="bi bi-search fs-1 text-primary opacity-50 d-block mb-3"></i>
        <h4 class="fw-bold text-dark">Scan or Type STB Barcode</h4>
        <p class="text-muted max-w-md mx-auto small">Use the barcode scanner field above to enter an STB Barcode number to generate its full Excel grid history report.</p>
    </div>
</div>
@endif

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto focus barcode scanner input on page load
        $('#stbBarcodeScanInput').focus();

        // Auto submit when camera scanner populates barcode
        $('#stbBarcodeScanInput').on('change', function() {
            if ($(this).val().trim().length > 0) {
                $('#stbHistoryForm').submit();
            }
        });
    });
</script>
@endpush
@endsection
