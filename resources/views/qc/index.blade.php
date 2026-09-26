@extends('layouts.app')

@section('title', 'QC Inspection Module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-patch-check-fill text-success me-2"></i> Quality Control (QC) Inspection</h3>
        <p class="text-muted small mb-0">Verify serviced Set Top Boxes, perform testing checks, and mark boxes Tested OK or return to Complaint.</p>
    </div>
</div>

<!-- Search & Filter -->
<div class="kv-card mb-4 p-3">
    <form action="{{ route('qc.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-8">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-kv border-start-0" placeholder="Search by barcode, model, cable operator..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Filter</button>
            <a href="{{ route('qc.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<div class="row g-4 mb-4">
    <!-- Pending QC Verification Table (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <h5 class="fw-bold mb-3 border-bottom pb-2">
                <i class="bi bi-hourglass-split text-warning me-2"></i> Serviced Boxes Awaiting QC Testing
                <span class="badge bg-warning text-dark ms-2">{{ $pendingBoxes->total() }} Pending</span>
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Barcode & Model</th>
                            <th>Service Ticket #</th>
                            <th>Operator</th>
                            <th>Last Serviced By</th>
                            <th class="text-end">QC Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingBoxes as $box)
                        @php $lastService = $box->serviceHistory->first(); @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-primary"></i> {{ $box->barcode_number }}</div>
                                <div class="text-muted fs-8">{{ $box->box_name }}</div>
                            </td>
                            <td>
                                @if($lastService && $lastService->service_code)
                                    <span class="badge bg-light text-primary border fw-bold"><i class="bi bi-tools me-1"></i> {{ $lastService->service_code }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($box->operator)
                                <span class="badge bg-secondary-subtle text-secondary">{{ $box->operator->operator_name }}</span>
                                @else
                                <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <div><i class="bi bi-person me-1 text-muted"></i> {{ $lastService->technician->name ?? 'N/A' }}</div>
                                <span class="text-muted fs-8">{{ $lastService ? \Carbon\Carbon::parse($lastService->service_date)->format('d M Y') : '' }}</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-kv-accent rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#qcModal{{ $box->id }}">
                                    <i class="bi bi-patch-check me-1"></i> Perform QC
                                </button>
                            </td>
                        </tr>

                        <!-- QC Verification Modal -->
                        <div class="modal fade" id="qcModal{{ $box->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                    <form action="{{ route('qc.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="set_top_box_id" value="{{ $box->id }}">
                                        <input type="hidden" name="service_transaction_id" value="{{ $lastService->id ?? '' }}">
                                        
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-patch-check text-success me-2"></i> QC Test Verification</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                                <div class="row g-2 small">
                                                    <div class="col-6"><strong>STB Barcode:</strong> <code>{{ $box->barcode_number }}</code></div>
                                                    <div class="col-6"><strong>Model:</strong> {{ $box->box_name }}</div>
                                                    <div class="col-6"><strong>Service Ticket #:</strong> <span class="fw-bold text-primary">{{ $lastService->service_code ?? 'N/A' }}</span></div>
                                                    <div class="col-6"><strong>Operator:</strong> {{ $box->operator->operator_name ?? 'N/A' }}</div>
                                                    <div class="col-12"><strong>Serviced Technician:</strong> {{ $lastService->technician->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">QC Test Result *</label>
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="form-check p-3 border rounded-3 bg-success-subtle text-success">
                                                        <input class="form-check-input ms-0 me-2" type="radio" name="qc_status" id="qc_pass_{{ $box->id }}" value="tested_ok" checked>
                                                        <label class="form-check-label fw-bold" for="qc_pass_{{ $box->id }}">
                                                            <i class="bi bi-check-circle-fill me-1"></i> TESTED OK (Pass QC - Ready for Operator Return)
                                                        </label>
                                                    </div>
                                                    <div class="form-check p-3 border rounded-3 bg-warning-subtle text-warning">
                                                        <input class="form-check-input ms-0 me-2" type="radio" name="qc_status" id="qc_fail_{{ $box->id }}" value="complaint">
                                                        <label class="form-check-label fw-bold" for="qc_fail_{{ $box->id }}">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> REJECT / RE-COMPLAINT (Return to Technician for Reservice)
                                                        </label>
                                                    </div>
                                                    <div class="form-check p-3 border rounded-3 bg-danger-subtle text-danger">
                                                        <input class="form-check-input ms-0 me-2" type="radio" name="qc_status" id="qc_flash_{{ $box->id }}" value="flash">
                                                        <label class="form-check-label fw-bold" for="qc_flash_{{ $box->id }}">
                                                            <i class="bi bi-x-circle-fill me-1"></i> FLASH (Unrepairable Dead Box)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">QC Test Remarks / Inspection Notes</label>
                                                <textarea name="remarks" class="form-control form-control-kv" rows="2" placeholder="e.g. Signal quality test passed 100%, tuner working fine."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0">
                                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-kv-primary">Submit QC Result</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4"><i class="bi bi-check2-all fs-3 d-block mb-1 text-success opacity-50"></i> All serviced STBs have been tested and verified!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pendingBoxes->links() }}
            </div>
        </div>
    </div>

    <!-- Recent QC Audit Logs (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-journal-check text-primary me-2"></i> Recent QC Audit Log</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>QC Voucher #</th>
                            <th>STB Barcode & Model</th>
                            <th>Service Ticket #</th>
                            <th>QC Status</th>
                            <th>Inspector</th>
                            <th>Date</th>
                            <th class="text-end text-nowrap" style="min-width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($qcLogs as $log)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary"><i class="bi bi-patch-check-fill me-1 text-success"></i> {{ $log->voucher_number }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $log->setTopBox->barcode_number ?? 'N/A' }}</div>
                                <span class="text-muted fs-8">{{ $log->setTopBox->box_name ?? '' }} ({{ $log->setTopBox->operator->operator_name ?? 'Unassigned' }})</span>
                            </td>
                            <td>
                                @if($log->serviceTransaction && $log->serviceTransaction->service_code)
                                    <span class="badge bg-light text-dark border">{{ $log->serviceTransaction->service_code }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($log->qc_status === 'tested_ok')
                                    <span class="badge bg-success-subtle text-success">TESTED OK</span>
                                @elseif($log->qc_status === 'complaint')
                                    <span class="badge bg-warning-subtle text-warning">QC REJECTED</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">FLASH</span>
                                @endif
                            </td>
                            <td>{{ $log->inspector->name ?? 'N/A' }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->qc_date)->format('d M Y') }}</td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#editQcModal{{ $log->id }}" title="Edit QC Voucher">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </button>
                                    <form action="{{ route('qc.destroy', $log->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete QC Voucher {{ $log->voucher_number }}? The STB will return to Pending QC list.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete QC Voucher">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit QC Voucher Modal -->
                        <div class="modal fade" id="editQcModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                    <form action="{{ route('qc.update', $log->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i> Edit QC Voucher #{{ $log->voucher_number }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                                <div class="row g-2 small">
                                                    <div class="col-6"><strong>STB Barcode:</strong> <code>{{ $log->setTopBox->barcode_number ?? 'N/A' }}</code></div>
                                                    <div class="col-6"><strong>Model:</strong> {{ $log->setTopBox->box_name ?? 'N/A' }}</div>
                                                    <div class="col-6"><strong>QC Voucher #:</strong> <span class="fw-bold text-primary">{{ $log->voucher_number }}</span></div>
                                                    <div class="col-6"><strong>Inspector:</strong> {{ $log->inspector->name ?? 'N/A' }}</div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">QC Test Result *</label>
                                                <div class="d-flex flex-column gap-2">
                                                    <div class="form-check p-3 border rounded-3 bg-success-subtle text-success">
                                                        <input class="form-check-input ms-0 me-2" type="radio" name="qc_status" id="edit_qc_pass_{{ $log->id }}" value="tested_ok" {{ $log->qc_status === 'tested_ok' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold" for="edit_qc_pass_{{ $log->id }}">
                                                            <i class="bi bi-check-circle-fill me-1"></i> TESTED OK (Pass QC - Ready for Operator Return)
                                                        </label>
                                                    </div>
                                                    <div class="form-check p-3 border rounded-3 bg-warning-subtle text-warning">
                                                        <input class="form-check-input ms-0 me-2" type="radio" name="qc_status" id="edit_qc_fail_{{ $log->id }}" value="complaint" {{ $log->qc_status === 'complaint' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold" for="edit_qc_fail_{{ $log->id }}">
                                                            <i class="bi bi-arrow-counterclockwise me-1"></i> REJECT / RE-COMPLAINT (Return to Technician for Reservice)
                                                        </label>
                                                    </div>
                                                    <div class="form-check p-3 border rounded-3 bg-danger-subtle text-danger">
                                                        <input class="form-check-input ms-0 me-2" type="radio" name="qc_status" id="edit_qc_flash_{{ $log->id }}" value="flash" {{ $log->qc_status === 'flash' ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-bold" for="edit_qc_flash_{{ $log->id }}">
                                                            <i class="bi bi-x-circle-fill me-1"></i> FLASH (Unrepairable Dead Box)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">QC Test Remarks / Inspection Notes</label>
                                                <textarea name="remarks" class="form-control form-control-kv" rows="2" placeholder="e.g. Re-tested tuner, signal clear.">{{ $log->remarks }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0">
                                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-kv-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No QC audit logs recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $qcLogs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
