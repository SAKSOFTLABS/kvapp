@extends('layouts.app')

@section('title', 'STB Service History')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('set-top-boxes.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 mb-2"><i class="bi bi-arrow-left me-1"></i> Back to STB Registry</a>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-clock-history text-primary me-2"></i> Service History Timeline</h3>
        <p class="text-muted small mb-0">Complete chronological audit trail of all repairs, component replacements, and technician services performed on this Set Top Box.</p>
    </div>
    <div>
        <a href="{{ route('set-top-boxes.print-report', $box->id) }}" target="_blank" class="btn btn-kv-primary btn-sm"><i class="bi bi-printer-fill me-1"></i> Print Service Report</a>
    </div>
</div>

<!-- STB Header Info Card -->
<div class="kv-card mb-4 bg-primary-subtle border-primary-subtle">
    <div class="row align-items-center">
        <div class="col-md-7">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-logo-icon bg-primary text-white fs-3" style="width: 54px; height: 54px;">
                    <i class="bi bi-tv"></i>
                </div>
                <div>
                    <h4 class="fw-extrabold text-dark mb-1">{{ $box->box_name }}</h4>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark font-monospace fs-6 px-3 py-1"><i class="bi bi-upc-scan me-1"></i> {{ $box->barcode_number }}</span>
                        @if($box->status == 'active')
                            <span class="badge bg-success-subtle text-success fs-7">Active Status</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger fs-7">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="text-muted small">Total Service Investment</div>
            <div class="fs-2 fw-extrabold text-primary">₹{{ number_format($totalServiceCost, 2) }}</div>
            <div class="text-muted small">{{ $box->serviceHistory->count() }} Lifetime Services Recorded</div>
        </div>
    </div>
    @if($box->remarks)
    <div class="mt-3 pt-3 border-top border-primary-subtle text-dark small">
        <strong>Remarks / Customer Info:</strong> {{ $box->remarks }}
    </div>
    @endif
</div>

<!-- Timeline Section -->
<div class="kv-card">
    <h5 class="fw-bold mb-4"><i class="bi bi-list-stars text-primary me-2"></i> Chronological Service Log</h5>

    @if($box->serviceHistory->count() > 0)
    <div class="timeline">
        @foreach($box->serviceHistory as $srv)
        <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="kv-card border shadow-sm p-3 mb-2">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <div>
                        <span class="fw-extrabold text-primary fs-6 me-2">{{ $srv->service_code }}</span>
                        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($srv->service_date)->format('d M Y') }}</span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success-subtle text-success fs-6 fw-bold">Total: ₹{{ number_format($srv->total_cost, 2) }}</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Technician Assigned</div>
                        <div class="fw-bold"><i class="bi bi-person-badge text-secondary me-1"></i> {{ $srv->technician->name ?? 'N/A' }} ({{ $srv->technician->designation ?? '' }})</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="small text-muted">Logged By User</div>
                        <div class="fw-semibold text-secondary">{{ $srv->creator->name ?? 'System' }}</div>
                    </div>
                </div>

                @if($srv->remarks)
                <div class="bg-light p-2 rounded-2 small text-muted mb-3 border">
                    <i class="bi bi-chat-left-text me-1 text-primary"></i> <strong>Work Done Remarks:</strong> {{ $srv->remarks }}
                </div>
                @endif

                <h6 class="fw-bold fs-7 text-uppercase text-muted mb-2"><i class="bi bi-boxes me-1"></i> Service Items & Spare Parts Used</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item Code</th>
                                <th>Item Description</th>
                                <th>Quantity</th>
                                <th>Unit Price (₹)</th>
                                <th>Subtotal (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($srv->items as $sItem)
                            <tr>
                                <td><code class="text-primary">{{ $sItem->item->item_code ?? 'N/A' }}</code></td>
                                <td class="fw-semibold">{{ $sItem->item->item_name ?? 'N/A' }}</td>
                                <td>{{ number_format($sItem->quantity, 2) }}</td>
                                <td>₹{{ number_format($sItem->unit_price, 2) }}</td>
                                <td class="fw-bold">₹{{ number_format($sItem->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center text-muted py-5">
        <i class="bi bi-tools fs-1 opacity-25 d-block mb-2"></i>
        No service history records found for this Set Top Box yet.
    </div>
    @endif
</div>
@endsection
