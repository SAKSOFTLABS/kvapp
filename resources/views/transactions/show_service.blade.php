@extends('layouts.app')

@section('title', 'Service Entry ' . $service->service_code)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-tools text-primary me-2"></i> Service Ticket Details</h3>
        <p class="text-muted small mb-0">View set top box service record and spare parts deduction ledger.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('service.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Service Section
        </a>
        <form action="{{ route('service.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this service entry?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3">
                <i class="bi bi-trash me-1"></i> Delete Entry
            </button>
        </form>
    </div>
</div>

<div class="kv-card mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="text-muted small fw-bold">Service Code</div>
            <div class="fs-5 fw-extrabold text-primary">{{ $service->service_code }}</div>
        </div>
        <div class="col-12 col-md-4">
            <div class="text-muted small fw-bold">Service Date</div>
            <div class="fs-6 fw-bold">{{ \Carbon\Carbon::parse($service->service_date)->format('d M Y') }}</div>
        </div>
        <div class="col-12 col-md-4">
            <div class="text-muted small fw-bold">Technician</div>
            <div class="fs-6 fw-bold text-dark">{{ $service->technician->name ?? 'N/A' }}</div>
            <div class="text-muted fs-8">{{ $service->technician->designation ?? '' }}</div>
        </div>
    </div>

    <div class="row g-3 mt-3 pt-3 border-top">
        <div class="col-12 col-md-6">
            <div class="text-muted small fw-bold">Serviced STB Box</div>
            <div class="fs-6 fw-bold text-primary"><i class="bi bi-upc-scan me-1"></i> {{ $service->setTopBox->barcode_number ?? 'N/A' }}</div>
            <div>{{ $service->setTopBox->box_name ?? '' }} (Operator: {{ $service->setTopBox->operator->operator_name ?? 'N/A' }})</div>
        </div>
        <div class="col-12 col-md-6">
            <div class="text-muted small fw-bold">Service Action Result</div>
            @if($service->setTopBox && $service->setTopBox->stb_status === 'flash')
                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold fs-7">FLASHED (Dead Box)</span>
            @else
                <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold fs-7">COMPLETED (Service Done - QC Pending)</span>
            @endif
        </div>
    </div>

    @if($service->remarks)
    <div class="mt-3 p-3 bg-light rounded-3 border">
        <strong>Service Remarks:</strong> {{ $service->remarks }}
    </div>
    @endif
</div>

<div class="kv-card">
    <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-box-seam text-primary me-2"></i> Spare Parts Used ({{ $service->items->count() }})</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Item Code</th>
                    <th>Spare Part Description</th>
                    <th>Quantity Deducted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($service->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><span class="badge bg-secondary">{{ $item->item->item_code ?? 'N/A' }}</span></td>
                    <td class="fw-bold text-dark">{{ $item->item->item_name ?? 'Unknown Item' }}</td>
                    <td class="fw-bold text-primary">{{ (float)$item->quantity }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No spare parts were used for this STB servicing (0 stock deduction).</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
