@extends('layouts.app')

@section('title', 'Add Stock to Main Store')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-cart-plus-fill text-primary me-2"></i> Purchase Stock into Main Store</h3>
        <p class="text-muted small mb-0">Record fresh inventory purchases and increase Main Stock ledger balances.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Full Width Purchase Entry Form Card -->
    <div class="col-12">
        <div class="kv-card">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-pencil-square text-primary me-2"></i> Purchase Entry</h5>
            <form action="{{ route('add-stock.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <label class="form-label fw-bold small">Purchase Date *</label>
                        <input type="date" name="date" class="form-control form-control-kv" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label fw-bold small">Select Item *</label>
                        <select name="item_id" id="item_id" class="form-select form-select-kv" required>
                            <option value="">-- Choose Item --</option>
                            @foreach($items as $item)
                            <option value="{{ $item->id }}" data-price="{{ $item->purchase_price }}">
                                {{ $item->item_name }} ({{ $item->item_code }}) - Current Main Stock: {{ $item->main_stock_qty }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold small">Purchase Qty *</label>
                        <input type="number" step="0.01" name="quantity" class="form-control form-control-kv" placeholder="0.00" required>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label fw-bold small">Price / Unit (₹) *</label>
                        <input type="number" step="0.01" name="purchase_price" id="purchase_price" class="form-control form-control-kv" placeholder="0.00" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small">Supplier Name (Optional)</label>
                        <input type="text" name="supplier" class="form-control form-control-kv" placeholder="e.g. Broadband Supplies Pvt Ltd">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small">Remarks / Invoice Note</label>
                        <input type="text" name="remarks" class="form-control form-control-kv" placeholder="Invoice reference number or note...">
                    </div>

                    <div class="col-12 text-end mt-3">
                        <button type="submit" class="btn btn-kv-primary px-4 py-2 fs-6 rounded-3">
                            <i class="bi bi-check-circle me-1"></i> Save & Increase Main Stock
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Full Width Recent Main Stock Purchases Table Card -->
    <div class="col-12">
        <div class="kv-card">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i> Recent Main Stock Purchases</h5>
                
                <form action="{{ route('add-stock.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm form-control-kv" placeholder="Search item, supplier..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary btn-kv-primary">Filter</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Item Name</th>
                            <th>Qty Added</th>
                            <th>Unit Price</th>
                            <th>Supplier</th>
                            <th class="text-end text-nowrap" style="min-width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                            <td>
                                <div class="fw-bold text-primary">{{ $tx->item->item_name ?? 'N/A' }}</div>
                                <code class="text-muted">{{ $tx->item->item_code ?? '' }}</code>
                            </td>
                            <td><span class="badge bg-success-subtle text-success fs-7">+{{ number_format($tx->quantity, 2) }}</span></td>
                            <td>₹{{ number_format($tx->unit_price, 2) }}</td>
                            <td>{{ $tx->supplier ?? 'N/A' }}</td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-3" 
                                            onclick="openEditModal({{ $tx->id }}, '{{ $tx->date }}', {{ $tx->item_id }}, {{ $tx->quantity }}, {{ $tx->unit_price }}, '{{ addslashes($tx->supplier) }}', '{{ addslashes($tx->remarks) }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('add-stock.destroy', $tx->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this purchase entry? Main stock balance will be adjusted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete Entry">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No purchase records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Stock Purchase Modal -->
<div class="modal fade" id="editAddStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="editStockForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Stock Purchase Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Purchase Date *</label>
                        <input type="date" name="date" id="edit_date" class="form-control form-control-kv" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Item *</label>
                        <select name="item_id" id="edit_item_id" class="form-select form-select-kv" required>
                            <option value="">-- Choose Item --</option>
                            @foreach($items as $item)
                            <option value="{{ $item->id }}">
                                {{ $item->item_name }} ({{ $item->item_code }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Purchase Quantity *</label>
                            <input type="number" step="0.01" name="quantity" id="edit_quantity" class="form-control form-control-kv" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Purchase Price per Unit (₹) *</label>
                            <input type="number" step="0.01" name="purchase_price" id="edit_purchase_price" class="form-control form-control-kv" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Supplier Name</label>
                        <input type="text" name="supplier" id="edit_supplier" class="form-control form-control-kv">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Remarks</label>
                        <textarea name="remarks" id="edit_remarks" class="form-control form-control-kv" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">Update Stock Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $('#item_id').on('change', function() {
        let price = $(this).find(':selected').data('price');
        if (price) {
            $('#purchase_price').val(price);
        }
    });

    function openEditModal(id, date, itemId, quantity, price, supplier, remarks) {
        let url = "{{ url('/transactions/add-stock') }}/" + id;
        $('#editStockForm').attr('action', url);
        $('#edit_date').val(date);
        $('#edit_item_id').val(itemId);
        $('#edit_quantity').val(quantity);
        $('#edit_purchase_price').val(price);
        $('#edit_supplier').val(supplier);
        $('#edit_remarks').val(remarks);

        let modalEl = document.getElementById('editAddStockModal');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush
