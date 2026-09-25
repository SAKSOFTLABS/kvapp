@extends('layouts.app')

@section('title', 'Cable Operators Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-buildings-fill text-primary me-2"></i> Cable Operators (LCO / Franchises)</h3>
        <p class="text-muted small mb-0">Register and manage cable operators bringing bulk Set Top Boxes for servicing and intake.</p>
    </div>
    <div class="d-flex gap-2">
        @if(Auth::user()->isAdmin())
        <button class="btn btn-outline-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#importOperatorModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
        </button>
        @endif
        <button class="btn btn-kv-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOperatorModal">
            <i class="bi bi-plus-lg me-1"></i> Add Cable Operator
        </button>
    </div>
</div>

<!-- Search Bar -->
<div class="kv-card mb-4 p-3">
    <form action="{{ route('operators.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-8">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-kv border-start-0" placeholder="Search by operator name, code, contact person, mobile..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Filter</button>
            <a href="{{ route('operators.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Operators Table -->
<div class="kv-table-wrapper">
    <table class="kv-table">
        <thead>
            <tr>
                <th>Operator Name & Code</th>
                <th>Contact Details</th>
                <th>Location</th>
                <th>Total STBs Received</th>
                <th>Pending Service</th>
                <th>Tested OK</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operators as $op)
            <tr>
                <td>
                    <div class="fw-bold fs-6 text-primary">{{ $op->operator_name }}</div>
                    <code class="badge bg-secondary-subtle text-secondary fs-8">{{ $op->operator_code }}</code>
                </td>
                <td>
                    <div><i class="bi bi-person me-1 text-muted"></i> {{ $op->contact_person ?? 'N/A' }}</div>
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $op->mobile ?? 'N/A' }}</div>
                </td>
                <td>{{ $op->location ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 fs-7">{{ $op->boxes_count }} Boxes</span>
                </td>
                <td>
                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3 fs-7">{{ $op->complaint_boxes_count }} Complaint</span>
                </td>
                <td>
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 fs-7">{{ $op->tested_ok_boxes_count }} Ready</span>
                </td>
                <td>
                    @if($op->status == 'active')
                        <span class="kv-badge kv-badge-active"><i class="bi bi-circle-fill fs-8"></i> Active</span>
                    @else
                        <span class="kv-badge kv-badge-inactive"><i class="bi bi-circle-fill fs-8"></i> Inactive</span>
                    @endif
                </td>
                <td class="text-end">
                    <button class="btn btn-icon me-1" data-bs-toggle="modal" data-bs-target="#editOperatorModal{{ $op->id }}" title="Edit Operator">
                        <i class="bi bi-pencil-fill text-primary"></i>
                    </button>
                    <form action="{{ route('operators.destroy', $op->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Cable Operator?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon" title="Delete Operator">
                            <i class="bi bi-trash-fill text-danger"></i>
                        </button>
                    </form>
                </td>
            </tr>

            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No Cable Operators found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Edit Operator Modals -->
@foreach($operators as $op)
<div class="modal fade" id="editOperatorModal{{ $op->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('operators.update', $op->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Cable Operator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Operator Name *</label>
                        <input type="text" name="operator_name" class="form-control form-control-kv" value="{{ $op->operator_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Operator Code *</label>
                        <input type="text" name="operator_code" class="form-control form-control-kv" value="{{ $op->operator_code }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control form-control-kv" value="{{ $op->contact_person }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mobile Number</label>
                        <input type="text" name="mobile" class="form-control form-control-kv" value="{{ $op->mobile }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Location / Branch</label>
                        <input type="text" name="location" class="form-control form-control-kv" value="{{ $op->location }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Status *</label>
                        <select name="status" class="form-select form-select-kv" required>
                            <option value="active" {{ $op->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $op->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kv-primary">Update Operator</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="d-flex justify-content-center mt-4">
    {{ $operators->links() }}
</div>

<!-- Add Operator Modal -->
<div class="modal fade" id="addOperatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('operators.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-buildings text-primary me-2"></i> Register Cable Operator (LCO)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Operator Name *</label>
                        <input type="text" name="operator_name" class="form-control form-control-kv" placeholder="e.g. Kaloor Cable Network" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Operator Code *</label>
                        <input type="text" name="operator_code" class="form-control form-control-kv" placeholder="e.g. LCO-KLR-001" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control form-control-kv" placeholder="e.g. Suresh Kumar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mobile Number</label>
                        <input type="text" name="mobile" class="form-control form-control-kv" placeholder="e.g. 9847012345">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Location / Branch</label>
                        <input type="text" name="location" class="form-control form-control-kv" placeholder="e.g. Kaloor Junction, Ernakulam">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Status *</label>
                        <select name="status" class="form-select form-select-kv" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kv-primary">Register Operator</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin())
<!-- Import Operator Modal (Super Admin Only) -->
<div class="modal fade" id="importOperatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('operators.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Cable Operators from CSV (Super Admin)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold small mb-1"><i class="bi bi-info-circle text-primary me-1"></i> CSV File Format & Guidelines:</div>
                        <ul class="small mb-2 ps-3 text-muted">
                            <li>CSV Columns: <code>operator_name, operator_code, contact_person, mobile, location, status</code></li>
                            <li>Existing operators with matching <code>operator_code</code> will be updated automatically.</li>
                        </ul>
                        <a href="{{ route('operators.sample-csv') }}" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="bi bi-download me-1"></i> Download Sample CSV Template
                        </a>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Select CSV File *</label>
                        <input type="file" name="csv_file" class="form-control form-control-kv" accept=".csv, .txt" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-kv-primary fw-bold px-4">Upload & Import Operators</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
