@extends('layouts.app')

@section('title', 'Set Top Box Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-tv-fill text-primary me-2"></i> Set Top Box Registry</h3>
        <p class="text-muted small mb-0">Maintain customer set top box units, operator intake, barcode registration, and STB status lifecycle.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('stb-checkin.index') }}" class="btn btn-primary btn-kv-primary btn-sm">
            <i class="bi bi-box-arrow-in-down me-1"></i> STB Box Check-In
        </a>
        <a href="{{ route('operators.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-buildings me-1"></i> Cable Operators
        </a>
        <a href="{{ route('box-models.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-diagram-3 me-1"></i> Box Models
        </a>
        <button class="btn btn-outline-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#addBoxModal">
            <i class="bi bi-plus-lg me-1"></i> Add Box Unit
        </button>
    </div>
</div>

<!-- Search & Filters -->
<div class="kv-card mb-4 p-3">
    <form action="{{ route('set-top-boxes.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-kv border-start-0" placeholder="Search barcode, model, operator..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="operator_id" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Operators</option>
                @foreach($operators as $op)
                <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>{{ $op->operator_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="stb_status" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All STB Statuses</option>
                <option value="complaint" {{ request('stb_status') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                <option value="service_done" {{ request('stb_status') == 'service_done' ? 'selected' : '' }}>Service Done</option>
                <option value="tested_ok" {{ request('stb_status') == 'tested_ok' ? 'selected' : '' }}>Tested OK (QC Passed)</option>
                <option value="flash" {{ request('stb_status') == 'flash' ? 'selected' : '' }}>Flash (Dead Box)</option>
                <option value="send_to_pk" {{ request('stb_status') == 'send_to_pk' ? 'selected' : '' }}>Send to PK</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Filter</button>
            <a href="{{ route('set-top-boxes.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- STB Table -->
<div class="kv-table-wrapper">
    <table class="kv-table">
        <thead>
            <tr>
                <th>Barcode Number (Unique)</th>
                <th>Box Model</th>
                <th>Cable Operator (LCO)</th>
                <th>STB Lifecycle Status</th>
                <th>Service Records</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boxes as $box)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-upc-scan fs-4 text-primary"></i>
                        <code class="fw-bold fs-6 text-dark">{{ $box->barcode_number }}</code>
                    </div>
                </td>
                <td>
                    <div class="fw-bold">{{ $box->box_name }}</div>
                    @if($box->boxModel && $box->boxModel->model_code)
                    <span class="badge bg-secondary-subtle text-secondary fs-8">{{ $box->boxModel->model_code }}</span>
                    @endif
                </td>
                <td>
                    @if($box->operator)
                    <span class="badge bg-info-subtle text-info border border-info rounded-pill px-3">{{ $box->operator->operator_name }}</span>
                    @else
                    <span class="text-muted small">Unassigned</span>
                    @endif
                </td>
                <td>
                    <span class="badge {{ $box->status_badge_class }} rounded-pill px-3 py-1 fw-bold fs-8">
                        {{ $box->status_label }}
                    </span>
                </td>
                <td>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $box->service_history_count }} Services</span>
                </td>
                <td class="text-end">
                    <a href="{{ route('set-top-boxes.history', $box->id) }}" class="btn btn-sm btn-outline-primary rounded-3 me-1" title="Service & QC History">
                        <i class="bi bi-clock-history me-1"></i> History
                    </a>
                    <button class="btn btn-icon me-1" data-bs-toggle="modal" data-bs-target="#editBoxModal{{ $box->id }}" title="Edit Box">
                        <i class="bi bi-pencil-fill text-primary"></i>
                    </button>
                    <form action="{{ route('set-top-boxes.destroy', $box->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this STB?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon" title="Delete Box">
                            <i class="bi bi-trash-fill text-danger"></i>
                        </button>
                    </form>
                </td>
            </tr>

            <!-- Edit Box Modal -->
            <div class="modal fade" id="editBoxModal{{ $box->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                        <form action="{{ route('set-top-boxes.update', $box->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit Set Top Box</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Box Model (Item Group) *</label>
                                    <select name="box_model_id" class="form-select form-select-kv" required>
                                        @foreach($boxModels as $bm)
                                        <option value="{{ $bm->id }}" {{ ($box->box_model_id == $bm->id || $box->box_name == $bm->model_name) ? 'selected' : '' }}>
                                            {{ $bm->model_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Cable Operator (LCO / Franchise)</label>
                                    <select name="operator_id" class="form-select form-select-kv">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($operators as $op)
                                        <option value="{{ $op->id }}" {{ $box->operator_id == $op->id ? 'selected' : '' }}>
                                            {{ $op->operator_name }} ({{ $op->operator_code }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Barcode Number (Unique) *</label>
                                    <div class="input-group">
                                        <input type="text" name="barcode_number" id="edit_barcode_{{ $box->id }}" class="form-control form-control-kv" value="{{ $box->barcode_number }}" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="startCameraScanner('#edit_barcode_{{ $box->id }}')"><i class="bi bi-camera"></i></button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">STB Lifecycle Status *</label>
                                    <select name="stb_status" class="form-select form-select-kv" required>
                                        <option value="complaint" {{ $box->stb_status == 'complaint' ? 'selected' : '' }}>Complaint (Default)</option>
                                        <option value="service_done" {{ $box->stb_status == 'service_done' ? 'selected' : '' }}>Service Done</option>
                                        <option value="tested_ok" {{ $box->stb_status == 'tested_ok' ? 'selected' : '' }}>Tested OK (QC Passed)</option>
                                        <option value="flash" {{ $box->stb_status == 'flash' ? 'selected' : '' }}>Flash (Dead Box)</option>
                                        @if(Auth::user()->isAdmin())
                                        <option value="send_to_pk" {{ $box->stb_status == 'send_to_pk' ? 'selected' : '' }}>Send to PK (Admin Only)</option>
                                        @endif
                                    </select>
                                    @if(!Auth::user()->isAdmin())
                                    <div class="form-text small text-muted"><i class="bi bi-lock me-1"></i> 'Send to PK' status is restricted to Administrators.</div>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Remarks / Customer Info</label>
                                    <textarea name="remarks" class="form-control form-control-kv" rows="2">{{ $box->remarks }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">System Active Status *</label>
                                    <select name="status" class="form-select form-select-kv" required>
                                        <option value="active" {{ $box->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $box->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-kv-primary">Update Box</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No Set Top Boxes found matching criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $boxes->links() }}
</div>

<!-- Add Box Modal (Without Operator Field - Operator assignment happens in STB Check-In) -->
<div class="modal fade" id="addBoxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('set-top-boxes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-tv text-primary me-2"></i> Register New Box Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Box Model (Item Group) *</label>
                            <a href="{{ route('box-models.index') }}" target="_blank" class="small text-primary text-decoration-none">+ Manage Models</a>
                        </div>
                        <select name="box_model_id" class="form-select form-select-kv" required>
                            <option value="">-- Choose Box Model --</option>
                            @foreach($boxModels as $bm)
                            <option value="{{ $bm->id }}">{{ $bm->model_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Barcode Number (Unique) *</label>
                        <div class="input-group">
                            <input type="text" name="barcode_number" id="barcode_number" class="form-control form-control-kv" placeholder="Scan or type barcode" required>
                            <button class="btn btn-outline-primary" type="button" onclick="startCameraScanner('#barcode_number')">
                                <i class="bi bi-camera-fill me-1"></i> Camera Scan
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">STB Status *</label>
                        <select name="stb_status" class="form-select form-select-kv" required>
                            <option value="complaint" selected>Complaint (Default)</option>
                            <option value="service_done">Service Done</option>
                            <option value="tested_ok">Tested OK (QC Passed)</option>
                            <option value="flash">Flash (Dead Box)</option>
                            @if(Auth::user()->isAdmin())
                            <option value="send_to_pk">Send to PK (Admin Only)</option>
                            @endif
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Remarks / Customer Info</label>
                        <textarea name="remarks" class="form-control form-control-kv" rows="2" placeholder="e.g. Master stock unit entry"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">System Status *</label>
                        <select name="status" class="form-select form-select-kv" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kv-primary">Register STB Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
