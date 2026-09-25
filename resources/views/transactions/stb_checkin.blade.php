@extends('layouts.app')

@section('title', 'STB Box Checkin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0"><i class="bi bi-box-arrow-in-down text-primary me-2"></i> STB Box Checkin</h3>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Check-In Voucher Entry Form (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <form action="{{ route('stb-checkin.store-voucher') }}" method="POST" id="voucherForm">
                @csrf

                <!-- Voucher Header Meta Fields (Equal Width col-md-4 & Uniform 46px Height & Styling) -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Voucher Number *</label>
                            <span class="small text-muted">&nbsp;</span>
                        </div>
                        <input type="text" name="voucher_number" class="form-control form-control-kv" value="{{ $nextVoucherNumber }}" required readonly>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Intake Date *</label>
                            <span class="small text-muted">&nbsp;</span>
                        </div>
                        <input type="date" name="checkin_date" class="form-control form-control-kv" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Cable Operator *</label>
                            <a href="{{ route('operators.index') }}" target="_blank" class="small text-primary text-decoration-none fw-bold">+ Add</a>
                        </div>
                        <select name="operator_id" id="voucher_operator_id" class="form-select form-select-kv" required>
                            <option value=""></option>
                            @foreach($operators as $op)
                            <option value="{{ $op->id }}">{{ $op->operator_name }} ({{ $op->operator_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Voucher Remarks / Intake Batch Note</label>
                    <input type="text" name="remarks" class="form-control form-control-kv" placeholder="e.g. Batch intake of STB boxes brought by Suresh Kumar for servicing.">
                </div>

                <!-- Barcode Scan Field -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <label class="form-label fw-bold small text-dark"><i class="bi bi-qr-code-scan me-1 text-primary"></i> Bulk Barcode Scanner (Scan & Add to Voucher Grid)</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary px-3" type="button" onclick="startCameraScanner('#scan_barcode_input')" title="Click to scan barcode using camera">
                            <i class="bi bi-camera-fill text-primary fs-5"></i>
                        </button>
                        <input type="text" id="scan_barcode_input" class="form-control form-control-kv fs-5 fw-bold" placeholder="Scan barcode with laser scanner or camera..." autofocus>
                        <button class="btn btn-primary btn-kv-primary px-4" type="button" id="btnAddScanToVoucher">
                            <i class="bi bi-plus-lg me-1"></i> Add to Voucher
                        </button>
                    </div>
                    <div class="form-text small"><i class="bi bi-info-circle me-1"></i> Point laser barcode scanner at STB labels. Items will automatically add into the grid below.</div>
                </div>

                <!-- Live Status Alert Box -->
                <div id="scanStatusAlert" class="alert d-none py-2 rounded-3 small mb-3">
                    <i class="me-1" id="scanStatusIcon"></i> <span id="scanStatusText"></span>
                </div>

                <!-- Voucher Item Grid Table -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0"><i class="bi bi-list-check me-1 text-primary"></i> Scanned STB Boxes Grid</h6>
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-7" id="voucherBoxCountBadge">0 STB Units</span>
                    </div>

                    <div class="table-responsive border rounded-3 overflow-hidden">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Barcode Number</th>
                                    <th>Box Model</th>
                                    <th>Intake Status</th>
                                    <th class="text-end" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="voucherGridTableBody">
                                <tr id="emptyGridRow">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-upc-scan fs-2 d-block mb-1 text-secondary opacity-50"></i>
                                        No STB boxes scanned into this voucher yet. Scan barcodes above to add items.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Submit -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <button type="button" class="btn btn-light rounded-3" onclick="resetVoucherForm()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Voucher Form
                    </button>
                    <button type="submit" class="btn btn-kv-primary py-2.5 px-4 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> Save & Finalize Check-In Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Past Intake Vouchers History List -->
<div class="kv-card">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i> Recent Intake Vouchers</h5>
        
        <form action="{{ route('stb-checkin.index') }}" method="GET" class="d-flex gap-2">
            <select name="operator_id" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Operators</option>
                @foreach($operators as $op)
                <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>{{ $op->operator_name }}</option>
                @endforeach
            </select>
            <input type="text" name="search" class="form-control form-control-sm form-control-kv" placeholder="Search voucher #, barcode..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-primary btn-kv-primary">Filter</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Voucher #</th>
                    <th>Date</th>
                    <th>Cable Operator</th>
                    <th>Boxes Count</th>
                    <th>Created By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $v)
                <tr>
                    <td>
                        <a href="{{ route('stb-checkin.show', $v->id) }}" class="fw-bold text-primary text-decoration-none">
                            {{ $v->voucher_number }}
                        </a>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($v->checkin_date)->format('d M Y') }}</td>
                    <td>
                        <span class="badge bg-info-subtle text-info border border-info rounded-pill px-3">{{ $v->operator->operator_name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $v->total_boxes }} Boxes</span>
                    </td>
                    <td>{{ $v->creator->name ?? 'System' }}</td>
                    <td class="text-end">
                        <a href="{{ route('stb-checkin.show', $v->id) }}" class="btn btn-sm btn-outline-secondary rounded-3 me-1">
                            <i class="bi bi-eye me-1"></i> View
                        </a>
                        <a href="{{ route('stb-checkin.edit', $v->id) }}" class="btn btn-sm btn-outline-warning rounded-3 me-1">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('stb-checkin.print', $v->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 me-1">
                            <i class="bi bi-printer me-1"></i> Print
                        </a>
                        <form action="{{ route('stb-checkin.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete intake voucher {{ $v->voucher_number }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete Voucher">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No check-in vouchers recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $vouchers->links() }}
    </div>
</div>

<!-- Auto Quick Registration Modal for Unregistered Barcodes -->
<div class="modal fade" id="autoRegisterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <form id="quickRegisterForm" onsubmit="return false;">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill me-2"></i> Register New STB Unit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="closeRegisterModalBtn"></button>
                </div>
                <div class="modal-body text-start">
                    <div class="alert alert-warning py-2 small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Barcode not found in database. Complete registration to add into active voucher.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Scanned Barcode Number (Auto-Filled) *</label>
                        <input type="text" name="barcode_number" id="auto_barcode_number" class="form-control form-control-kv bg-light fw-bold text-primary fs-5" readonly required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Select Box Model (Item Group) *</label>
                        <select name="box_model_id" id="auto_box_model_id" class="form-select form-select-kv" required style="cursor: pointer;">
                            <option value="">-- Select Box Model --</option>
                            @foreach($boxModels as $bm)
                            <option value="{{ $bm->id }}">{{ $bm->model_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Remarks / Issue Details</label>
                        <textarea name="remarks" id="auto_remarks" class="form-control form-control-kv" rows="2" placeholder="e.g. Complaint: No signal / Power issue"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-kv-primary" id="btnSubmitQuickRegister">Register & Add to Voucher Grid</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let scannedBoxIds = new Set();
    let gridCounter = 0;
    let operatorSelect = null;

    $(document).ready(function() {
        // Initialize Tom Select for Cable Operator (Searchable Inline Input)
        if (document.getElementById('voucher_operator_id')) {
            operatorSelect = new TomSelect('#voucher_operator_id', {
                create: false,
                placeholder: 'Type to search Cable Operator...',
                allowEmptyOption: true,
                dropdownParent: 'body'
            });
        }
    });

    // Prevent form submit on Enter key inside barcode scanner input and trigger lookup
    $('#scan_barcode_input').on('keydown keypress', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            processBarcodeLookup();
            return false;
        }
    });

    $('#btnAddScanToVoucher').on('click', function(e) {
        e.preventDefault();
        processBarcodeLookup();
    });

    function processBarcodeLookup() {
        let barcode = $('#scan_barcode_input').val().trim();
        let operatorId = $('#voucher_operator_id').val();

        if (!operatorId) {
            alert('Please select a Cable Operator first before scanning barcodes.');
            $('#voucher_operator_id').focus();
            return;
        }

        if (!barcode) return;

        // Perform AJAX Barcode Lookup
        $.ajax({
            url: "{{ route('stb-checkin.lookup') }}",
            data: { barcode: barcode },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success) {
                    if (res.found) {
                        // Existing Box -> Add to Voucher Grid
                        addBoxToVoucherGrid(res.box);
                        $('#scan_barcode_input').val('').focus();
                    } else {
                        // Unregistered Box -> Open Auto Registration Modal
                        showScanAlert('warning', 'bi-exclamation-triangle-fill', 'Unregistered Barcode (' + res.barcode + ')! Please select box model to register.');
                        $('#auto_barcode_number').val(res.barcode);
                        $('#scan_barcode_input').val('');
                        $('#auto_box_model_id').val('');
                        $('#auto_remarks').val('');
                        
                        // Open Bootstrap 5 Modal
                        let modalEl = document.getElementById('autoRegisterModal');
                        let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.show();
                    }
                }
            },
            error: function() {
                showScanAlert('danger', 'bi-x-circle-fill', 'Failed to perform barcode lookup.');
            }
        });
    }

    // Prevent voucher submission if no boxes are scanned into grid
    $('#voucherForm').on('submit', function(e) {
        if (scannedBoxIds.size === 0) {
            e.preventDefault();
            showScanAlert('danger', 'bi-exclamation-octagon-fill', 'Please scan at least one STB box into the grid before saving the check-in voucher.');
            alert('Please scan at least one STB box into the grid before saving the check-in voucher.');
            $('#scan_barcode_input').focus();
            return false;
        }
    });

    // Handle Quick Register Form Submit on Enter key inside modal
    $('#quickRegisterForm').on('submit', function(e) {
        e.preventDefault();
        $('#btnSubmitQuickRegister').click();
        return false;
    });

    // Handle Quick Register Form Submit via AJAX
    $('#btnSubmitQuickRegister').on('click', function() {
        let modelId = $('#auto_box_model_id').val();
        let barcode = $('#auto_barcode_number').val();
        let remarks = $('#auto_remarks').val();

        if (!modelId) {
            alert('Please select a Box Model.');
            $('#auto_box_model_id').focus();
            return;
        }

        $.ajax({
            url: "{{ route('stb-checkin.register') }}",
            type: "POST",
            headers: { 'Accept': 'application/json' },
            data: {
                _token: "{{ csrf_token() }}",
                box_model_id: modelId,
                barcode_number: barcode,
                remarks: remarks
            },
            success: function(res) {
                if (res.success && res.box) {
                    // Close Bootstrap 5 Modal reliably
                    let modalEl = document.getElementById('autoRegisterModal');
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    } else {
                        $('#autoRegisterModal').modal('hide');
                    }

                    // Remove lingering backdrop & overflow lock
                    setTimeout(function() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('overflow', '');
                    }, 300);

                    // Reset modal fields
                    $('#auto_box_model_id').val('');
                    $('#auto_remarks').val('');

                    // Add box to voucher table grid automatically
                    addBoxToVoucherGrid(res.box);
                    showScanAlert('success', 'bi-check-circle-fill', 'New STB ' + res.box.barcode_number + ' registered and added to voucher grid!');
                    
                    // Return focus to barcode scan input for next scan
                    setTimeout(() => {
                        $('#scan_barcode_input').focus();
                    }, 400);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to register STB unit.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert(errorMsg);
            }
        });
    });

    function addBoxToVoucherGrid(box) {
        if (scannedBoxIds.has(box.id)) {
            showScanAlert('danger', 'bi-exclamation-octagon-fill', 'Barcode ' + box.barcode_number + ' is already added to this voucher!');
            return;
        }

        scannedBoxIds.add(box.id);
        gridCounter++;

        $('#emptyGridRow').addClass('d-none');

        let rowHtml = `
        <tr id="gridRow_${box.id}" class="table-success align-middle">
            <td class="fw-bold">${gridCounter}</td>
            <td>
                <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-primary"></i> ${box.barcode_number}</div>
                <input type="hidden" name="box_ids[]" value="${box.id}">
            </td>
            <td>${box.box_name}</td>
            <td><span class="badge ${box.status_badge_class || 'bg-warning-subtle text-warning'} rounded-pill px-3 py-1 fw-bold fs-8">${box.status_label || 'Complaint'}</span></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-3" onclick="removeBoxFromVoucherGrid(${box.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#voucherGridTableBody').append(rowHtml);
        updateVoucherBoxCount();
        showScanAlert('success', 'bi-check-circle-fill', 'STB Barcode ' + box.barcode_number + ' added to grid.');
    }

    function removeBoxFromVoucherGrid(boxId) {
        $(`#gridRow_${boxId}`).remove();
        scannedBoxIds.delete(boxId);
        updateVoucherBoxCount();

        if (scannedBoxIds.size === 0) {
            $('#emptyGridRow').removeClass('d-none');
        }
    }

    function updateVoucherBoxCount() {
        let count = scannedBoxIds.size;
        $('#voucherBoxCountBadge').text(count + ' STB Units');
    }

    function showScanAlert(type, icon, message) {
        $('#scanStatusAlert')
            .removeClass('d-none alert-success alert-warning alert-danger alert-info')
            .addClass(`alert-${type}`);
        
        $('#scanStatusIcon')
            .removeClass()
            .addClass(`bi ${icon}`);

        $('#scanStatusText').text(message);
    }

    function resetVoucherForm() {
        if (confirm('Are you sure you want to reset the current voucher form and clear scanned grid?')) {
            $('#voucherForm')[0].reset();
            $('#voucherGridTableBody').empty().append(`
                <tr id="emptyGridRow">
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="bi bi-upc-scan fs-2 d-block mb-1 text-secondary opacity-50"></i>
                        No STB boxes scanned into this voucher yet. Scan barcodes above to add items.
                    </td>
                </tr>
            `);
            scannedBoxIds.clear();
            gridCounter = 0;
            updateVoucherBoxCount();
            if (operatorSelect) {
                operatorSelect.clear();
            }
            $('#scanStatusAlert').addClass('d-none');
        }
    }
</script>
@endpush
