@extends('layouts.app')

@section('title', 'STB Box Models (Item Groups)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i> STB Box Models (Item Groups)</h3>
        <p class="text-muted small mb-0">Define STB hardware models and item groups used during Set Top Box unit registrations.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('set-top-boxes.index') }}" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-tv me-1"></i> Register STB Units</a>
        <button class="btn btn-kv-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModelModal"><i class="bi bi-plus-lg me-1"></i> Create Box Model</button>
    </div>
</div>

<!-- Search Card -->
<div class="kv-card mb-4 p-3">
    <form action="{{ route('box-models.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-kv border-start-0" placeholder="Search model name, code, description..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Filter</button>
            <a href="{{ route('box-models.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Models Table -->
<div class="kv-table-wrapper">
    <table class="kv-table">
        <thead>
            <tr>
                <th>Model Code</th>
                <th>Box Model / Item Group Name</th>
                <th>Description</th>
                <th>Registered STB Units</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($models as $m)
            <tr>
                <td><code class="text-primary font-monospace fs-7">{{ $m->model_code ?? 'N/A' }}</code></td>
                <td><div class="fw-bold">{{ $m->model_name }}</div></td>
                <td>{{ Str::limit($m->description ?? 'N/A', 45) }}</td>
                <td>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $m->set_top_boxes_count }} Units</span>
                </td>
                <td>
                    @if($m->status == 'active')
                        <span class="kv-badge kv-badge-active"><i class="bi bi-circle-fill fs-8"></i> Active</span>
                    @else
                        <span class="kv-badge kv-badge-inactive"><i class="bi bi-circle-fill fs-8"></i> Inactive</span>
                    @endif
                </td>
                <td class="text-end">
                    <button class="btn btn-icon me-1" data-bs-toggle="modal" data-bs-target="#editModelModal{{ $m->id }}" title="Edit Model">
                        <i class="bi bi-pencil-fill text-primary"></i>
                    </button>
                    <form action="{{ route('box-models.destroy', $m->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Box Model?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon" title="Delete Model">
                            <i class="bi bi-trash-fill text-danger"></i>
                        </button>
                    </form>
                </td>
            </tr>

            <!-- Edit Model Modal -->
            <div class="modal fade" id="editModelModal{{ $m->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                        <form action="{{ route('box-models.update', $m->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">Edit Box Model: {{ $m->model_name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-start">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Model Name / Item Group *</label>
                                    <input type="text" name="model_name" class="form-control form-control-kv" value="{{ $m->model_name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Model Code</label>
                                    <input type="text" name="model_code" class="form-control form-control-kv" value="{{ $m->model_code }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Description</label>
                                    <textarea name="description" class="form-control form-control-kv" rows="2">{{ $m->description }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Status *</label>
                                    <select name="status" class="form-select form-select-kv" required>
                                        <option value="active" {{ $m->status == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ $m->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer border-top-0">
                                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-kv-primary">Update Model</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="6" class="text-center text-muted py-4">No Box Models found matching criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $models->links() }}
</div>

<!-- Add Model Modal -->
<div class="modal fade" id="addModelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('box-models.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i> Create STB Box Model</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Model Name / Item Group *</label>
                        <input type="text" name="model_name" class="form-control form-control-kv" placeholder="e.g. KV HD Smart Box 4K Model A1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Model Code</label>
                        <input type="text" name="model_code" class="form-control form-control-kv" placeholder="e.g. MDL-KV-4KA1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" class="form-control form-control-kv" rows="2" placeholder="Hardware specifications, chipset, tuner type..."></textarea>
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
                    <button type="submit" class="btn btn-kv-primary">Save Box Model</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
