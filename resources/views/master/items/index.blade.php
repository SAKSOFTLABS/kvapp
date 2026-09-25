@extends('layouts.app')

@section('title', 'Item Creation & Master Catalog')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-box-seam-fill text-primary me-2"></i> Service Item Creation</h3>
        <p class="text-muted small mb-0">Manage service items, spare parts, and opening stock balances used by field technicians.</p>
    </div>
    <div class="d-flex gap-2">
        @if(Auth::user()->isAdmin())
        <button class="btn btn-outline-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#importItemModal">
            <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
        </button>
        @endif
        <a href="{{ route('items.export-csv') }}" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-file-earmark-excel me-1"></i> Export Excel (CSV)</a>
        <button class="btn btn-outline-dark btn-sm rounded-3 no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print Catalog</button>
        <button class="btn btn-kv-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addItemModal"><i class="bi bi-plus-lg me-1"></i> Add New Item</button>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="kv-card mb-4 p-3">
    <form action="{{ route('items.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control form-control-kv border-start-0" placeholder="Search by item name, code, description..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <div class="col-6 col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Filter</button>
            <a href="{{ route('items.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Items Table -->
<div class="kv-table-wrapper">
    <table class="kv-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Item Name</th>
                <th>Opening Stock</th>
                <th>Main Stock Qty</th>
                <th>Purchase Price</th>
                <th>Sales Price</th>
                <th>Status</th>
                <th class="text-end no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td><span class="badge bg-secondary-subtle text-secondary font-monospace fs-7">{{ $item->item_code }}</span></td>
                <td>
                    <div class="fw-bold">{{ $item->item_name }}</div>
                    @if($item->description)
                    <div class="text-muted small">{{ Str::limit($item->description, 45) }}</div>
                    @endif
                </td>
                <td>{{ number_format($item->opening_stock, 2) }}</td>
                <td>
                    @if($item->main_stock_qty <= 10)
                        <span class="badge bg-danger-subtle text-danger fw-bold fs-7">{{ number_format($item->main_stock_qty, 2) }} (Low)</span>
                    @else
                        <span class="badge bg-success-subtle text-success fw-bold fs-7">{{ number_format($item->main_stock_qty, 2) }}</span>
                    @endif
                </td>
                <td>₹{{ number_format($item->purchase_price, 2) }}</td>
                <td>₹{{ number_format($item->sales_price, 2) }}</td>
                <td>
                    @if($item->status == 'active')
                        <span class="kv-badge kv-badge-active"><i class="bi bi-circle-fill fs-8"></i> Active</span>
                    @else
                        <span class="kv-badge kv-badge-inactive"><i class="bi bi-circle-fill fs-8"></i> Inactive</span>
                    @endif
                </td>
                <td class="text-end no-print">
                    <button class="btn btn-icon me-1" data-bs-toggle="modal" data-bs-target="#editItemModal{{ $item->id }}" title="Edit Item">
                        <i class="bi bi-pencil-fill text-primary"></i>
                    </button>
                    <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this item?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-icon" title="Delete Item">
                            <i class="bi bi-trash-fill text-danger"></i>
                        </button>
                    </form>
                </td>
            </tr>

            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No items found matching criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Edit Item Modals -->
@foreach($items as $item)
<div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('items.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Item: {{ $item->item_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Item Name</label>
                        <input type="text" name="item_name" class="form-control form-control-kv" value="{{ $item->item_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Item Code (Unique)</label>
                        <input type="text" name="item_code" class="form-control form-control-kv" value="{{ $item->item_code }}" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Purchase Price (₹)</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control form-control-kv" value="{{ $item->purchase_price }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Sales Price (₹)</label>
                            <input type="number" step="0.01" name="sales_price" class="form-control form-control-kv" value="{{ $item->sales_price }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" class="form-control form-control-kv" rows="2">{{ $item->description }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Status</label>
                        <select name="status" class="form-select form-select-kv" required>
                            <option value="active" {{ $item->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $item->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-kv-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<div class="d-flex justify-content-center mt-4">
    {{ $items->links() }}
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('items.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle text-primary me-2"></i> Add New Service Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Item Name *</label>
                        <input type="text" name="item_name" class="form-control form-control-kv" placeholder="e.g. HDMI Cable 1.5m" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Item Code (Unique) *</label>
                        <input type="text" name="item_code" class="form-control form-control-kv" placeholder="e.g. ITM-HDMI-01" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label fw-bold small">Opening Stock *</label>
                            <input type="number" step="0.01" name="opening_stock" class="form-control form-control-kv" value="0" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold small">Purchase Price (₹) *</label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control form-control-kv" placeholder="0.00" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold small">Sales Price (₹) *</label>
                            <input type="number" step="0.01" name="sales_price" class="form-control form-control-kv" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="bi bi-info-circle me-1"></i> Entering Opening Stock will automatically update the Main Stock ledger upon creation.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="description" class="form-control form-control-kv" rows="2" placeholder="Item technical specifications or remarks..."></textarea>
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
                    <button type="submit" class="btn btn-kv-primary">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin())
<!-- Import Item Modal (Super Admin Only) -->
<div class="modal fade" id="importItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form action="{{ route('items.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Items from CSV (Super Admin)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold small mb-1"><i class="bi bi-info-circle text-primary me-1"></i> CSV File Format & Guidelines:</div>
                        <ul class="small mb-2 ps-3 text-muted">
                            <li>CSV Columns: <code>item_name, item_code, opening_stock, purchase_price, sales_price, description, status</code></li>
                            <li>Existing items with matching <code>item_code</code> will be updated automatically.</li>
                            <li>New items will be created and main store ledger initialized with <code>opening_stock</code>.</li>
                        </ul>
                        <a href="{{ route('items.sample-csv') }}" class="btn btn-sm btn-outline-primary fw-bold">
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
                    <button type="submit" class="btn btn-primary btn-kv-primary fw-bold px-4">Upload & Import Items</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
