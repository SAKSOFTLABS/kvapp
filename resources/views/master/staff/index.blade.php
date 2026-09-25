@extends('layouts.app')

@section('title', 'Create Staff & Technicians')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-people-fill text-primary me-2"></i> Staff & Technician Management</h3>
        <p class="text-muted small mb-0">Maintain service personnel records and manage their login credentials & permission levels.</p>
    </div>
    <div>
        <button class="btn btn-kv-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal"><i class="bi bi-person-plus-fill me-1"></i> Create New Staff</button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="kv-card mb-4 p-3">
    <form action="{{ route('staff.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-kv border-start-0" placeholder="Search by staff name, mobile, username..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="designation" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Designations</option>
                <option value="Front Office" {{ request('designation') == 'Front Office' ? 'selected' : '' }}>Front Office</option>
                <option value="QC" {{ request('designation') == 'QC' ? 'selected' : '' }}>QC</option>
                <option value="Service" {{ request('designation') == 'Service' ? 'selected' : '' }}>Service</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Filter</button>
            <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Staff Table -->
<div class="kv-table-wrapper">
    <table class="kv-table">
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Designation / Permission Level</th>
                <th>Mobile Number</th>
                <th>Username</th>
                <th>Address</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staffList as $staff)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="brand-logo-icon rounded-circle" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </div>
                        <div class="fw-bold">{{ $staff->name }}</div>
                    </div>
                </td>
                <td>
                    @if($staff->designation == 'Front Office')
                        <span class="badge bg-info-subtle text-info border border-info-subtle fw-bold fs-7"><i class="bi bi-building me-1"></i> Front Office</span>
                    @elseif($staff->designation == 'QC')
                        <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold fs-7"><i class="bi bi-patch-check me-1"></i> QC</span>
                    @elseif($staff->designation == 'Service')
                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle fw-bold fs-7"><i class="bi bi-tools me-1"></i> Service</span>
                    @else
                        <span class="badge bg-primary-subtle text-primary fw-semibold fs-7">{{ $staff->designation }}</span>
                    @endif
                </td>
                <td><i class="bi bi-telephone text-muted me-1"></i> {{ $staff->mobile }}</td>
                <td><code class="text-dark">{{ $staff->username }}</code></td>
                <td>{{ Str::limit($staff->address ?? 'N/A', 35) }}</td>
                <td>
                    @if($staff->status == 'active')
                        <span class="kv-badge kv-badge-active"><i class="bi bi-circle-fill fs-8"></i> Active</span>
                    @else
                        <span class="kv-badge kv-badge-inactive"><i class="bi bi-circle-fill fs-8"></i> Inactive</span>
                    @endif
                </td>
                <td class="text-end">
                    <button class="btn btn-icon me-1" data-bs-toggle="modal" data-bs-target="#editStaffModal{{ $staff->id }}" title="Edit Staff">
                        <i class="bi bi-pencil-fill text-primary"></i>
                    </button>
                    <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon" title="Delete Staff">
                            <i class="bi bi-trash-fill text-danger"></i>
                        </button>
                    </form>
                </td>
            </tr>

            <!-- Edit Staff Modal -->
            <div class="modal fade" id="editStaffModal{{ $staff->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                        <form action="{{ route('staff.update', $staff->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit Staff: {{ $staff->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Staff Name *</label>
                                    <input type="text" name="name" class="form-control form-control-kv" value="{{ $staff->name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Designation / User Level *</label>
                                    <select name="designation" class="form-select form-select-kv" required>
                                        <option value="">-- Select Designation --</option>
                                        <option value="Front Office" {{ $staff->designation == 'Front Office' ? 'selected' : '' }}>1. Front Office</option>
                                        <option value="QC" {{ $staff->designation == 'QC' ? 'selected' : '' }}>2. QC</option>
                                        <option value="Service" {{ $staff->designation == 'Service' ? 'selected' : '' }}>3. Service</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Mobile Number *</label>
                                    <input type="text" name="mobile" class="form-control form-control-kv" value="{{ $staff->mobile }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Username *</label>
                                    <input type="text" name="username" class="form-control form-control-kv" value="{{ $staff->username }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">New Password (leave blank to keep current)</label>
                                    <input type="password" name="password" class="form-control form-control-kv" placeholder="******">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Address</label>
                                    <textarea name="address" class="form-control form-control-kv" rows="2">{{ $staff->address }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Status *</label>
                                    <select name="status" class="form-select form-select-kv" required>
                                        <option value="active" {{ $staff->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $staff->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-kv-primary">Update Staff</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No staff members found matching criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $staffList->links() }}
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('staff.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus text-primary me-2"></i> Create Staff Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Staff Name *</label>
                        <input type="text" name="name" class="form-control form-control-kv" placeholder="e.g. Rajesh Kumar" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Designation / User Level *</label>
                        <select name="designation" class="form-select form-select-kv" required>
                            <option value="">-- Select Designation --</option>
                            <option value="Front Office">1. Front Office</option>
                            <option value="QC">2. QC</option>
                            <option value="Service">3. Service</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Mobile Number *</label>
                        <input type="text" name="mobile" class="form-control form-control-kv" placeholder="98460XXXXX" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Username *</label>
                            <input type="text" name="username" class="form-control form-control-kv" placeholder="rajesh" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Password *</label>
                            <input type="password" name="password" class="form-control form-control-kv" placeholder="******" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Address</label>
                        <textarea name="address" class="form-control form-control-kv" rows="2" placeholder="Full residential or office address..."></textarea>
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
                    <button type="submit" class="btn btn-kv-primary">Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
