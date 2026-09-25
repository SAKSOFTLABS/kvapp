@extends('layouts.app')

@section('title', 'STB Delivery (Checkout)')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0"><i class="bi bi-box-arrow-up-right text-success me-2"></i> STB Delivery (Checkout)</h3>
        <p class="text-muted small mb-0">Issue QC Passed ('Tested OK') Set Top Boxes back to Cable Operators with delivery vouchers.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Checkout Voucher Entry Form (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <form action="{{ route('stb-checkout.store-voucher') }}" method="POST" id="checkoutVoucherForm">
                @csrf

                <!-- Voucher Header Meta Fields -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Delivery Voucher # *</label>
                            <span class="small text-muted">&nbsp;</span>
                        </div>
                        <input type="text" name="voucher_number" class="form-control form-control-kv" value="{{ $nextVoucherNumber }}" required readonly>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Delivery Date *</label>
                            <span class="small text-muted">&nbsp;</span>
                        </div>
                        <input type="date" name="checkout_date" class="form-control form-control-kv" value="{{ date('Y-m-d') }}" required>
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
                    <label class="form-label fw-bold small">Voucher Remarks / Delivery Note</label>
                    <input type="text" name="remarks" class="form-control form-control-kv" placeholder="e.g. Delivery of 15 QC Passed STB units handed over to Cable Operator representative.">
                </div>

                <!-- Barcode Scan Field -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <label class="form-label fw-bold small text-dark"><i class="bi bi-qr-code-scan me-1 text-success"></i> Bulk Delivery Barcode Scanner (Scan Tested OK / Flash STBs)</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary px-3" type="button" onclick="startCameraScanner('#scan_barcode_input')" title="Click to scan barcode using camera">
                            <i class="bi bi-camera-fill text-success fs-5"></i>
                        </button>
                        <input type="text" id="scan_barcode_input" class="form-control form-control-kv fs-5 fw-bold" placeholder="Scan barcode of STB (Tested OK / Flash)..." autofocus>
                        <button class="btn btn-success btn-kv-accent px-4" type="button" id="btnAddScanToVoucher">
                            <i class="bi bi-plus-lg me-1"></i> Add to Delivery Grid
                        </button>
                    </div>
                    <div class="form-text small"><i class="bi bi-shield-check me-1 text-success"></i> Only STBs with <strong>Tested OK (QC Passed)</strong> or <strong>Flash (Dead Box)</strong> status matching the selected Cable Operator can be delivered.</div>
                </div>

                <!-- Live Status Alert Box -->
                <div id="scanStatusAlert" class="alert d-none py-2 rounded-3 small mb-3">
                    <i class="me-1" id="scanStatusIcon"></i> <span id="scanStatusText"></span>
                </div>

                <!-- Voucher Item Grid Table -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0"><i class="bi bi-list-check me-1 text-success"></i> Delivery STB Boxes Grid</h6>
                        <span class="badge bg-success rounded-pill px-3 py-2 fs-7" id="voucherBoxCountBadge">0 STB Units</span>
                    </div>

                    <div class="table-responsive border rounded-3 overflow-hidden">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Barcode Number</th>
                                    <th>Box Model</th>
                                    <th>Operator</th>
                                    <th>QC Status</th>
                                    <th class="text-end" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="voucherGridTableBody">
                                <tr id="emptyGridRow">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-upc-scan fs-2 d-block mb-1 text-secondary opacity-50"></i>
                                        No eligible STB boxes (Tested OK / Flash) added to this delivery voucher yet. Scan barcodes above.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Submit -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <button type="button" class="btn btn-light rounded-3" onclick="resetVoucherForm()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Form
                    </button>
                    <button type="submit" class="btn btn-success py-2.5 px-4 fs-6 fw-bold">
                        <i class="bi bi-check-circle-fill me-1"></i> Save & Issue Delivery Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Past Delivery Vouchers History List -->
<div class="kv-card">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-success me-2"></i> Recent Delivery Vouchers (Checkout History)</h5>
        
        <form action="{{ route('stb-checkout.index') }}" method="GET" class="d-flex gap-2">
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
                    <th>Boxes Delivered</th>
                    <th>Issued By</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $v)
                <tr>
                    <td>
                        <a href="{{ route('stb-checkout.show', $v->id) }}" class="fw-bold text-success text-decoration-none">
                            {{ $v->voucher_number }}
                        </a>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($v->checkout_date)->format('d M Y') }}</td>
                    <td>
                        <span class="badge bg-info-subtle text-info border border-info rounded-pill px-3">{{ $v->operator->operator_name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3">{{ $v->total_boxes }} Boxes</span>
                    </td>
                    <td>{{ $v->creator->name ?? 'System' }}</td>
                    <td class="text-end">
                        <a href="{{ route('stb-checkout.show', $v->id) }}" class="btn btn-sm btn-outline-secondary rounded-3 me-1">
                            <i class="bi bi-eye me-1"></i> View
                        </a>
                        <a href="{{ route('stb-checkout.edit', $v->id) }}" class="btn btn-sm btn-outline-warning rounded-3 me-1">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('stb-checkout.print', $v->id) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-3 me-1">
                            <i class="bi bi-printer me-1"></i> Print Receipt
                        </a>
                        <form action="{{ route('stb-checkout.destroy', $v->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete delivery voucher {{ $v->voucher_number }}? Box statuses will revert to Tested OK.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Delete Delivery Voucher">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No delivery vouchers recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $vouchers->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    let scannedBoxIds = new Set();
    let gridCounter = 0;
    let operatorSelect = null;

    $(document).ready(function() {
        // Initialize Tom Select for Cable Operator
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
            processCheckoutBarcodeLookup();
            return false;
        }
    });

    $('#btnAddScanToVoucher').on('click', function(e) {
        e.preventDefault();
        processCheckoutBarcodeLookup();
    });

    function processCheckoutBarcodeLookup() {
        let barcode = $('#scan_barcode_input').val().trim();
        let operatorId = $('#voucher_operator_id').val();

        if (!operatorId) {
            alert('Please select a Cable Operator first before scanning barcodes.');
            $('#voucher_operator_id').focus();
            return;
        }

        if (!barcode) return;

        // Perform AJAX Barcode Lookup & Validation
        $.ajax({
            url: "{{ route('stb-checkout.lookup') }}",
            data: { 
                barcode: barcode,
                operator_id: operatorId
            },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success) {
                    if (!res.found) {
                        showScanAlert('danger', 'bi-x-circle-fill', res.message);
                    } else if (!res.valid) {
                        // Validation failed (e.g. Not QC passed OR Operator mismatch)
                        showScanAlert('danger', 'bi-exclamation-triangle-fill', res.message);
                    } else {
                        // QC Passed & Operator Match -> Add to Delivery Grid
                        addBoxToVoucherGrid(res.box);
                        $('#scan_barcode_input').val('').focus();
                    }
                }
            },
            error: function() {
                showScanAlert('danger', 'bi-x-circle-fill', 'Failed to perform barcode lookup.');
            }
        });
    }

    // Prevent delivery voucher submission if no boxes are added to grid
    $('#checkoutVoucherForm').on('submit', function(e) {
        if (scannedBoxIds.size === 0) {
            e.preventDefault();
            showScanAlert('danger', 'bi-exclamation-octagon-fill', 'Please scan at least one eligible STB box (Tested OK or Flash) into the delivery grid before saving.');
            alert('Please scan at least one eligible STB box (Tested OK or Flash) into the delivery grid before saving.');
            $('#scan_barcode_input').focus();
            return false;
        }
    });

    function addBoxToVoucherGrid(box) {
        if (scannedBoxIds.has(box.id)) {
            showScanAlert('warning', 'bi-exclamation-octagon-fill', 'Barcode ' + box.barcode_number + ' is already added to this delivery voucher!');
            return;
        }

        scannedBoxIds.add(box.id);
        gridCounter++;

        $('#emptyGridRow').addClass('d-none');

        let rowHtml = `
        <tr id="gridRow_${box.id}" class="table-success align-middle">
            <td class="fw-bold">${gridCounter}</td>
            <td>
                <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-success"></i> ${box.barcode_number}</div>
                <input type="hidden" name="box_ids[]" value="${box.id}">
            </td>
            <td>${box.box_name}</td>
            <td><span class="badge bg-secondary-subtle text-secondary">${box.operator_name}</span></td>
            <td><span class="badge ${box.status_badge_class || 'bg-success-subtle text-success'} rounded-pill px-3 py-1 fw-bold fs-8">${box.status_label || 'Tested OK'}</span></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-3" onclick="removeBoxFromVoucherGrid(${box.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#voucherGridTableBody').append(rowHtml);
        updateVoucherBoxCount();
        let statusLabel = box.status_label || 'Tested OK';
        showScanAlert('success', 'bi-check-circle-fill', 'STB Barcode ' + box.barcode_number + ' (' + statusLabel + ') added to delivery grid.');
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
        if (confirm('Are you sure you want to reset the current delivery voucher form and clear grid?')) {
            $('#checkoutVoucherForm')[0].reset();
            $('#voucherGridTableBody').empty().append(`
                <tr id="emptyGridRow">
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="bi bi-upc-scan fs-2 d-block mb-1 text-secondary opacity-50"></i>
                        No QC Passed STB boxes added to this delivery voucher yet. Scan barcodes above.
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
