@extends('layouts.app')

@section('title', 'View STB Delivery Voucher')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0"><i class="bi bi-file-earmark-text text-success me-2"></i> Delivery Voucher Details</h3>
        <p class="text-muted small mb-0">STB Checkout Voucher #{{ $voucher->voucher_number }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stb-checkout.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Back to History
        </a>
        <a href="{{ route('stb-checkout.print', $voucher->id) }}" target="_blank" class="btn btn-success btn-sm rounded-3">
            <i class="bi bi-printer me-1"></i> Print Delivery Receipt
        </a>
    </div>
</div>

<div class="kv-card mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-3">
            <span class="text-muted small d-block">Voucher Number</span>
            <strong class="fs-5 text-success">{{ $voucher->voucher_number }}</strong>
        </div>
        <div class="col-12 col-md-3">
            <span class="text-muted small d-block">Delivery Date</span>
            <strong>{{ \Carbon\Carbon::parse($voucher->checkout_date)->format('d M Y') }}</strong>
        </div>
        <div class="col-12 col-md-3">
            <span class="text-muted small d-block">Cable Operator</span>
            <strong class="text-dark">{{ $voucher->operator->operator_name ?? 'N/A' }}</strong>
            <div class="small text-muted">({{ $voucher->operator->operator_code ?? '' }})</div>
        </div>
        <div class="col-12 col-md-3">
            <span class="text-muted small d-block">Total STBs Delivered</span>
            <span class="badge bg-success-subtle text-success fs-6 px-3 py-1">{{ $voucher->total_boxes }} Units</span>
        </div>
    </div>

    @if($voucher->remarks)
    <div class="mt-3 pt-3 border-top">
        <span class="text-muted small d-block">Voucher Remarks</span>
        <div class="fst-italic text-dark">{{ $voucher->remarks }}</div>
    </div>
    @endif
</div>

<div class="kv-card">
    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-box-seam me-2 text-success"></i> Delivered STB Units Grid</h5>

    <div class="table-responsive border rounded-3 overflow-hidden">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Barcode Number</th>
                    <th>Box Model</th>
                    <th>Current STB Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voucher->items as $index => $item)
                <tr>
                    <td class="fw-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-success"></i> {{ $item->barcode_number }}</div>
                    </td>
                    <td>{{ $item->setTopBox->box_name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-info-subtle text-info border border-info rounded-pill px-3 py-1">Delivered</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
