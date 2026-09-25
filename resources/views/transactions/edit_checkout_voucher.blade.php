@extends('layouts.app')

@section('title', 'Edit STB Delivery Voucher')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0"><i class="bi bi-pencil-square text-warning me-2"></i> Edit STB Delivery Voucher</h3>
        <p class="text-muted small mb-0">Voucher Number: <strong>{{ $voucher->voucher_number }}</strong></p>
    </div>
    <a href="{{ route('stb-checkout.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Back to History
    </a>
</div>

<div class="kv-card mb-4">
    <form action="{{ route('stb-checkout.update', $voucher->id) }}" method="POST" id="editCheckoutVoucherForm">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold small">Voucher Number</label>
                <input type="text" class="form-control form-control-kv bg-light" value="{{ $voucher->voucher_number }}" readonly>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold small">Delivery Date *</label>
                <input type="date" name="checkout_date" class="form-control form-control-kv" value="{{ $voucher->checkout_date }}" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold small">Cable Operator *</label>
                <select name="operator_id" id="edit_operator_id" class="form-select form-select-kv" required>
                    @foreach($operators as $op)
                    <option value="{{ $op->id }}" {{ $voucher->operator_id == $op->id ? 'selected' : '' }}>{{ $op->operator_name }} ({{ $op->operator_code }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold small">Voucher Remarks</label>
            <input type="text" name="remarks" class="form-control form-control-kv" value="{{ $voucher->remarks }}">
        </div>

        <!-- Barcode Scan Input for Edit -->
        <div class="p-3 bg-light rounded-3 border mb-3">
            <label class="form-label fw-bold small text-dark"><i class="bi bi-qr-code-scan me-1 text-success"></i> Add Additional QC Passed STB Barcodes</label>
            <div class="input-group">
                <input type="text" id="scan_barcode_input" class="form-control form-control-kv fs-5 fw-bold" placeholder="Scan or type QC Passed barcode...">
                <button class="btn btn-success px-4" type="button" id="btnAddScanToEditVoucher">
                    <i class="bi bi-plus-lg me-1"></i> Add to Grid
                </button>
            </div>
        </div>

        <!-- Status Alert -->
        <div id="scanStatusAlert" class="alert d-none py-2 rounded-3 small mb-3">
            <i class="me-1" id="scanStatusIcon"></i> <span id="scanStatusText"></span>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold m-0"><i class="bi bi-list-check me-1 text-success"></i> Delivered Items Grid</h6>
                <span class="badge bg-success rounded-pill px-3 py-2 fs-7" id="voucherBoxCountBadge">{{ count($voucher->items) }} STB Units</span>
            </div>

            <div class="table-responsive border rounded-3 overflow-hidden">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Barcode Number</th>
                            <th>Box Model</th>
                            <th>Operator</th>
                            <th class="text-end" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="editVoucherGridTableBody">
                        @foreach($voucher->items as $index => $item)
                        <tr id="gridRow_{{ $item->set_top_box_id }}" class="align-middle">
                            <td class="fw-bold item-counter">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-success"></i> {{ $item->barcode_number }}</div>
                                <input type="hidden" name="box_ids[]" value="{{ $item->set_top_box_id }}">
                            </td>
                            <td>{{ $item->setTopBox->box_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $voucher->operator->operator_name ?? 'N/A' }}</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-3" onclick="removeBoxFromVoucherGrid({{ $item->set_top_box_id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
            <a href="{{ route('stb-checkout.index') }}" class="btn btn-light rounded-3">Cancel</a>
            <button type="submit" class="btn btn-warning text-dark fw-bold px-4">Update Delivery Voucher</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let scannedBoxIds = new Set([@foreach($voucher->items as $item) {{ $item->set_top_box_id }}, @endforeach]);

    $('#btnAddScanToEditVoucher, #scan_barcode_input').on('click keyup change', function(e) {
        if (e.type === 'keyup' && e.key !== 'Enter') return;
        
        let barcode = $('#scan_barcode_input').val().trim();
        let operatorId = $('#edit_operator_id').val();

        if (!barcode) return;

        $.ajax({
            url: "{{ route('stb-checkout.lookup') }}",
            data: { barcode: barcode, operator_id: operatorId },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success) {
                    if (!res.found) {
                        showScanAlert('danger', 'bi-x-circle-fill', res.message);
                    } else if (!res.valid) {
                        showScanAlert('danger', 'bi-exclamation-triangle-fill', res.message);
                    } else {
                        addBoxToVoucherGrid(res.box);
                        $('#scan_barcode_input').val('').focus();
                    }
                }
            }
        });
    });

    function addBoxToVoucherGrid(box) {
        if (scannedBoxIds.has(box.id)) {
            showScanAlert('warning', 'bi-exclamation-octagon-fill', 'Barcode ' + box.barcode_number + ' is already in this voucher!');
            return;
        }

        scannedBoxIds.add(box.id);

        let rowCount = $('#editVoucherGridTableBody tr').length + 1;
        let rowHtml = `
        <tr id="gridRow_${box.id}" class="table-success align-middle">
            <td class="fw-bold item-counter">${rowCount}</td>
            <td>
                <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-success"></i> ${box.barcode_number}</div>
                <input type="hidden" name="box_ids[]" value="${box.id}">
            </td>
            <td>${box.box_name}</td>
            <td><span class="badge bg-secondary-subtle text-secondary">${box.operator_name}</span></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger rounded-3" onclick="removeBoxFromVoucherGrid(${box.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#editVoucherGridTableBody').append(rowHtml);
        updateVoucherBoxCount();
        showScanAlert('success', 'bi-check-circle-fill', 'STB Barcode ' + box.barcode_number + ' added.');
    }

    function removeBoxFromVoucherGrid(boxId) {
        $(`#gridRow_${boxId}`).remove();
        scannedBoxIds.delete(boxId);
        updateVoucherBoxCount();
    }

    function updateVoucherBoxCount() {
        let count = scannedBoxIds.size;
        $('#voucherBoxCountBadge').text(count + ' STB Units');
    }

    function showScanAlert(type, icon, message) {
        $('#scanStatusAlert')
            .removeClass('d-none alert-success alert-warning alert-danger alert-info')
            .addClass(`alert-${type}`);
        $('#scanStatusIcon').removeClass().addClass(`bi ${icon}`);
        $('#scanStatusText').text(message);
    }
</script>
@endpush
