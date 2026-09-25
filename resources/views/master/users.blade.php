@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-shield-lock-fill text-primary me-2"></i> User Management</h3>
        <p class="text-muted small mb-0">Create and manage system user accounts, login credentials, and role permissions.</p>
    </div>
    <div>
        <button type="button" class="btn btn-kv-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="bi bi-person-plus-fill me-1"></i> Add New User
        </button>
    </div>
</div>

<div class="kv-card">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
        <form action="{{ route('users.index') }}" method="GET" class="d-flex flex-wrap gap-2 w-100">
            <div class="flex-grow-1" style="min-width: 200px;">
                <input type="text" name="search" class="form-control form-control-sm form-control-kv" placeholder="Search name, username, email..." value="{{ request('search') }}">
            </div>
            <div style="min-width: 150px;">
                <select name="role" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="front_office" {{ request('role') == 'front_office' ? 'selected' : '' }}>Front Office</option>
                    <option value="qc" {{ request('role') == 'qc' ? 'selected' : '' }}>QC Inspector</option>
                    <option value="service" {{ request('role') == 'service' ? 'selected' : '' }}>Service Tech</option>
                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>General Staff</option>
                </select>
            </div>
            <div style="min-width: 130px;">
                <select name="status" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary btn-kv-primary px-3">
                <i class="bi bi-funnel-fill me-1"></i> Filter
            </button>
            @if(request()->anyFilled(['search', 'role', 'status']))
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-light border px-3">Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>User Details</th>
                    <th>Username</th>
                    <th>Role / Designation Level</th>
                    <th>Linked Staff</th>
                    <th>Status</th>
                    <th class="text-end" style="min-width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $usr)
                <tr>
                    <td class="text-muted fw-bold">{{ $usr->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle bg-primary-subtle text-primary fw-bold" style="width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                                {{ strtoupper(substr($usr->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $usr->name }}</div>
                                <div class="text-muted fs-8">{{ $usr->email ?? 'No email' }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code class="fw-bold text-primary fs-7">{{ $usr->username }}</code></td>
                    <td>
                        @if($usr->role === 'admin')
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-shield-check me-1"></i> Admin</span>
                        @elseif($usr->role === 'front_office')
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="bi bi-building me-1"></i> Front Office</span>
                        @elseif($usr->role === 'qc')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-patch-check me-1"></i> QC Inspector</span>
                        @elseif($usr->role === 'service')
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-1"><i class="bi bi-tools me-1"></i> Service Tech</span>
                        @else
                            <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle px-2 py-1"><i class="bi bi-person me-1"></i> Field Staff</span>
                        @endif
                    </td>
                    <td>
                        @if($usr->staff)
                            <span class="text-dark font-monospace"><i class="bi bi-person-badge text-muted me-1"></i>{{ $usr->staff->name }}</span>
                        @else
                            <span class="text-muted fst-italic">None</span>
                        @endif
                    </td>
                    <td>
                        @if($usr->status === 'active')
                            <span class="badge bg-success-subtle text-success fs-7">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger fs-7">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex align-items-center justify-content-end gap-1">
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-3"
                                    onclick="openEditUserModal({{ $usr->id }}, '{{ addslashes($usr->name) }}', '{{ addslashes($usr->username) }}', '{{ addslashes($usr->email) }}', '{{ $usr->role }}', '{{ $usr->staff_id }}', '{{ $usr->status }}')">
                                <i class="bi bi-pencil"></i>
                            </button>

                            @if($usr->id != Auth::id())
                            <form action="{{ route('users.destroy', $usr->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete user {{ $usr->username }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete User">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" disabled title="Logged-in User">
                                <i class="bi bi-lock-fill"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Create New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Full Name *</label>
                        <input type="text" name="name" class="form-control form-control-kv" placeholder="e.g. System Admin" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Username *</label>
                            <input type="text" name="username" class="form-control form-control-kv" placeholder="e.g. admin2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Password *</label>
                            <input type="password" name="password" class="form-control form-control-kv" placeholder="Min 6 chars" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Email Address</label>
                        <input type="email" name="email" class="form-control form-control-kv" placeholder="user@keralavision.com">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">System Role *</label>
                            <select name="role" class="form-select form-select-kv" required>
                                <option value="admin">Admin / Super Admin</option>
                                <option value="front_office">Front Office</option>
                                <option value="qc">QC Inspector</option>
                                <option value="service" selected>Service Tech</option>
                                <option value="staff">General Staff</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Account Status *</label>
                            <select name="status" class="form-select form-select-kv" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Associated Staff Profile (Optional)</label>
                        <select name="staff_id" class="form-select form-select-kv">
                            <option value="">-- None (Standalone Account) --</option>
                            @foreach($staffList as $stf)
                            <option value="{{ $stf->id }}">{{ $stf->name }} ({{ $stf->designation }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-kv-primary fw-bold px-4">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit User Credentials</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Full Name *</label>
                        <input type="text" name="name" id="edit_name" class="form-control form-control-kv" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Username *</label>
                            <input type="text" name="username" id="edit_username" class="form-control form-control-kv" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">New Password (Optional)</label>
                            <input type="password" name="password" class="form-control form-control-kv" placeholder="Leave blank to keep same">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Email Address</label>
                        <input type="email" name="email" id="edit_email" class="form-control form-control-kv">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">System Role *</label>
                            <select name="role" id="edit_role" class="form-select form-select-kv" required>
                                <option value="admin">Admin / Super Admin</option>
                                <option value="front_office">Front Office</option>
                                <option value="qc">QC Inspector</option>
                                <option value="service">Service Tech</option>
                                <option value="staff">General Staff</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Account Status *</label>
                            <select name="status" id="edit_status" class="form-select form-select-kv" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Associated Staff Profile (Optional)</label>
                        <select name="staff_id" id="edit_staff_id" class="form-select form-select-kv">
                            <option value="">-- None (Standalone Account) --</option>
                            @foreach($staffList as $stf)
                            <option value="{{ $stf->id }}">{{ $stf->name }} ({{ $stf->designation }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditUserModal(id, name, username, email, role, staffId, status) {
        let url = "{{ url('/master/users') }}/" + id;
        $('#editUserForm').attr('action', url);
        $('#edit_name').val(name);
        $('#edit_username').val(username);
        $('#edit_email').val(email);
        $('#edit_role').val(role);
        $('#edit_staff_id').val(staffId ? staffId : '');
        $('#edit_status').val(status);

        let modalEl = document.getElementById('editUserModal');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush
