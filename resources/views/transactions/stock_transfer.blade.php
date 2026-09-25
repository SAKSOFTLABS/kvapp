@extends('layouts.app')

@section('title', 'Stock Transfer to Staff')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-arrow-left-right text-primary me-2"></i> Stock Transfer to Technicians</h3>
        <p class="text-muted small mb-0">Issue spare parts and materials from Main Store to field technicians and service personnel.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Transfer Form Card (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-send-fill text-primary me-2"></i> Create Transfer</h5>
            <form action="{{ route('stock-transfer.store') }}" method="POST" id="transferForm">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold small">Transfer Date *</label>
                        <input type="date" name="transfer_date" class="form-control form-control-kv" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold small">From Stock Source</label>
                        <input type="text" class="form-control form-control-kv bg-light" value="Main Store Stock (Central Warehouse)" readonly>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold small">Recipient Technician / Staff *</label>
                        <select name="staff_id" id="staff_id" class="form-select form-select-kv" required>
                            <option value="">-- Select Technician --</option>
                            @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->name }} ({{ $staff->designation }}) - {{ $staff->mobile }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small">Select Item to Transfer *</label>
                        <select name="item_id" id="transfer_item_id" class="form-select form-select-kv" required>
                            <option value="">-- Choose Item --</option>
                            @foreach($items as $item)
                            <option value="{{ $item->id }}" data-stock="{{ (int)$item->main_stock_qty }}">
                                {{ $item->item_name }} ({{ $item->item_code }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small">Transfer Quantity *</label>
                        <input type="number" step="1" min="1" name="quantity" id="transfer_quantity" class="form-control form-control-kv" placeholder="0" required>
                        <div class="form-text text-danger d-none" id="qtyWarning">Quantity exceeds available main store stock!</div>
                    </div>
                </div>

                <!-- Main Stock Availability Indicator -->
                <div class="alert alert-secondary py-2 small mb-3 d-flex justify-content-between align-items-center" id="stockAlertBox">
                    <span><i class="bi bi-box me-1"></i> Available in Main Store:</span>
                    <strong class="fs-6" id="availableStockDisplay">Select an item</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Remarks</label>
                    <textarea name="remarks" class="form-control form-control-kv" rows="2" placeholder="e.g. Field kit monthly replenishment"></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-kv-accent py-2.5 px-4 fs-6" id="submitTransferBtn">
                        <i class="bi bi-arrow-right-circle me-1"></i> Complete Stock Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transfer History Card (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i> Transfer Audit History</h5>
                
                <form action="{{ route('stock-transfer.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm form-control-kv" placeholder="Search code, tech, item..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary btn-kv-primary">Filter</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Technician</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th class="text-end text-nowrap" style="min-width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $trf)
                        <tr>
                            <td><span class="fw-bold text-success">{{ $trf->transfer_code }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($trf->transfer_date)->format('d M Y') }}</td>
                            <td><i class="bi bi-person me-1 text-muted"></i> {{ $trf->staff->name ?? 'N/A' }}</td>
                            <td>{{ $trf->item->item_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-success-subtle text-success fs-7">{{ number_format($trf->quantity, 0) }}</span></td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-3"
                                            onclick="openEditTransferModal({{ $trf->id }}, '{{ $trf->transfer_date }}', {{ $trf->staff_id }}, {{ $trf->item_id }}, {{ (int)$trf->quantity }}, '{{ addslashes($trf->remarks) }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('stock-transfer.destroy', $trf->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete transfer {{ $trf->transfer_code }}? Stock balances will be reverted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete Transfer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No transfers recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Edit Transfer Modal -->
<div class="modal fade" id="editTransferModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="editTransferForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Edit Stock Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Transfer Date *</label>
                        <input type="date" name="transfer_date" id="edit_transfer_date" class="form-control form-control-kv" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Recipient Technician / Staff *</label>
                        <select name="staff_id" id="edit_staff_id" class="form-select form-select-kv" required>
                            <option value="">-- Select Technician --</option>
                            @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->name }} ({{ $staff->designation }})
                            </option>
                            @endforeach
                        </select>
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

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Transfer Quantity *</label>
                        <input type="number" step="1" min="1" name="quantity" id="edit_quantity" class="form-control form-control-kv" placeholder="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Remarks</label>
                        <textarea name="remarks" id="edit_remarks" class="form-control form-control-kv" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-4">Update Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentAvailable = 0;
    let staffSelectInstance = null;
    let itemSelectInstance = null;
    let editStaffSelectInstance = null;
    let editItemSelectInstance = null;

    $(document).ready(function() {
        // Initialize TomSelect for Staff Dropdown
        if (document.getElementById('staff_id')) {
            staffSelectInstance = new TomSelect('#staff_id', {
                create: false,
                placeholder: '-- Select Technician --',
                allowEmptyOption: true,
                dropdownParent: 'body'
            });
        }

        // Initialize TomSelect for Item Dropdown
        if (document.getElementById('transfer_item_id')) {
            itemSelectInstance = new TomSelect('#transfer_item_id', {
                create: false,
                placeholder: '-- Choose Item --',
                allowEmptyOption: true,
                dropdownParent: 'body',
                onChange: function(val) {
                    handleItemChange(val);
                }
            });
        }

        // Initialize TomSelect for Edit Modal Staff Dropdown
        if (document.getElementById('edit_staff_id')) {
            editStaffSelectInstance = new TomSelect('#edit_staff_id', {
                create: false,
                placeholder: '-- Select Technician --',
                allowEmptyOption: true,
                dropdownParent: 'body'
            });
        }

        // Initialize TomSelect for Edit Modal Item Dropdown
        if (document.getElementById('edit_item_id')) {
            editItemSelectInstance = new TomSelect('#edit_item_id', {
                create: false,
                placeholder: '-- Choose Item --',
                allowEmptyOption: true,
                dropdownParent: 'body'
            });
        }
    });

    function handleItemChange(itemId) {
        if (!itemId) {
            $('#availableStockDisplay').text('Select an item').removeClass('text-success text-danger');
            currentAvailable = 0;
            validateQty();
            return;
        }

        let selectedOpt = $('#transfer_item_id option[value="' + itemId + '"]');
        let optStock = parseInt(selectedOpt.attr('data-stock'));
        if (!isNaN(optStock)) {
            currentAvailable = optStock;
            updateStockDisplay(currentAvailable);
        }

        $.ajax({
            url: "{{ url('/transactions/stock-transfer/item-info') }}/" + itemId,
            success: function(res) {
                if (res.success) {
                    currentAvailable = parseInt(res.main_stock_qty) || 0;
                    updateStockDisplay(currentAvailable);
                }
            }
        });
    }

    function updateStockDisplay(qty) {
        let intQty = Math.floor(qty);
        $('#availableStockDisplay').text(intQty + ' Units');
        if (intQty > 0) {
            $('#availableStockDisplay').removeClass('text-danger').addClass('text-success');
        } else {
            $('#availableStockDisplay').removeClass('text-success').addClass('text-danger');
        }
        validateQty();
    }

    // Disallow decimal inputs
    $('#transfer_quantity, #edit_quantity').on('input keyup change', function() {
        let val = $(this).val();
        if (val && val.includes('.')) {
            $(this).val(Math.floor(parseFloat(val)) || '');
        }
        validateQty();
    });

    function validateQty() {
        let entered = parseInt($('#transfer_quantity').val()) || 0;
        if (entered > currentAvailable || currentAvailable <= 0) {
            if (entered > currentAvailable) {
                $('#qtyWarning').removeClass('d-none');
            } else {
                $('#qtyWarning').addClass('d-none');
            }
            $('#submitTransferBtn').prop('disabled', true);
        } else {
            $('#qtyWarning').addClass('d-none');
            $('#submitTransferBtn').prop('disabled', false);
        }
    }

    function openEditTransferModal(id, date, staffId, itemId, quantity, remarks) {
        let url = "{{ url('/transactions/stock-transfer') }}/" + id;
        $('#editTransferForm').attr('action', url);
        $('#edit_transfer_date').val(date);
        
        if (editStaffSelectInstance) {
            editStaffSelectInstance.setValue(staffId);
        } else {
            $('#edit_staff_id').val(staffId);
        }

        if (editItemSelectInstance) {
            editItemSelectInstance.setValue(itemId);
        } else {
            $('#edit_item_id').val(itemId);
        }

        $('#edit_quantity').val(Math.floor(quantity));
        $('#edit_remarks').val(remarks);

        let modalEl = document.getElementById('editTransferModal');
        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
@endpush
