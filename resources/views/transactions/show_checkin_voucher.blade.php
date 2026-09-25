@extends('layouts.app')

@section('title', 'Voucher ' . $voucher->voucher_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-receipt-cutoff text-primary me-2"></i> Intake Voucher Details</h3>
        <p class="text-muted small mb-0">View set top box intake voucher records and print intake slip.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stb-checkin.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Check-In
        </a>
        <a href="{{ route('stb-checkin.edit', $voucher->id) }}" class="btn btn-outline-warning btn-sm rounded-3">
            <i class="bi bi-pencil me-1"></i> Edit Voucher
        </a>
        <a href="{{ route('stb-checkin.print', $voucher->id) }}" target="_blank" class="btn btn-primary btn-kv-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Print Voucher Slip
        </a>
        <form action="{{ route('stb-checkin.destroy', $voucher->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete intake voucher {{ $voucher->voucher_number }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </form>
    </div>
</div>

<div class="kv-card mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-3">
            <div class="text-muted small fw-bold">Voucher Number</div>
            <div class="fs-5 fw-extrabold text-primary">{{ $voucher->voucher_number }}</div>
        </div>
        <div class="col-12 col-md-3">
            <div class="text-muted small fw-bold">Intake Date</div>
            <div class="fs-6 fw-bold">{{ \Carbon\Carbon::parse($voucher->checkin_date)->format('d M Y') }}</div>
        </div>
        <div class="col-12 col-md-3">
            <div class="text-muted small fw-bold">Cable Operator</div>
            <div class="fs-6 fw-bold text-dark">{{ $voucher->operator->operator_name }}</div>
            <div class="text-muted fs-8">{{ $voucher->operator->operator_code }}</div>
        </div>
        <div class="col-12 col-md-3">
            <div class="text-muted small fw-bold">Total STB Count</div>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 fs-6">{{ $voucher->total_boxes }} Units</span>
        </div>
    </div>
    @if($voucher->remarks)
    <div class="mt-3 p-3 bg-light rounded-3 border">
        <strong>Voucher Remarks:</strong> {{ $voucher->remarks }}
    </div>
    @endif
</div>

<div class="kv-card">
    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-boxes text-primary me-2"></i> Checked-In STB List ({{ $voucher->items->count() }})</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Barcode Number</th>
                    <th>Box Model</th>
                    <th>STB Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voucher->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold text-dark"><i class="bi bi-upc-scan text-primary me-1"></i> {{ $item->barcode_number }}</div>
                    </td>
                    <td>{{ $item->setTopBox->box_name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $item->setTopBox->status_badge_class ?? 'bg-warning-subtle text-warning' }} rounded-pill px-3 py-1 fw-bold fs-8">
                            {{ $item->setTopBox->status_label ?? 'Complaint' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('set-top-boxes.history', $item->set_top_box_id) }}" class="btn btn-sm btn-outline-primary rounded-3">
                            <i class="bi bi-clock-history me-1"></i> History
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
