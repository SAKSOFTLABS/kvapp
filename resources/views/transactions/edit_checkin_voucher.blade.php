@extends('layouts.app')

@section('title', 'Edit Check-In Voucher ' . $voucher->voucher_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Check-In Voucher</h3>
        <p class="text-muted small mb-0">Modify voucher details and scanned set top boxes for intake voucher {{ $voucher->voucher_number }}</p>
    </div>
    <a href="{{ route('stb-checkin.index') }}" class="btn btn-outline-secondary rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Back to Checkin List
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="kv-card">
            <form action="{{ route('stb-checkin.update', $voucher->id) }}" method="POST" id="editVoucherForm">
                @csrf
                @method('PUT')

                <!-- Voucher Header Meta Fields -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Voucher Number *</label>
                        </div>
                        <input type="text" class="form-control form-control-kv" value="{{ $voucher->voucher_number }}" readonly>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Intake Date *</label>
                        </div>
                        <input type="date" name="checkin_date" class="form-control form-control-kv" value="{{ old('checkin_date', $voucher->checkin_date) }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold small mb-0">Cable Operator *</label>
                            <a href="{{ route('operators.index') }}" target="_blank" class="small text-primary text-decoration-none fw-bold">+ Add</a>
                        </div>
                        <select name="operator_id" id="edit_voucher_operator_id" class="form-select form-select-kv" required>
                            <option value="">-- Choose Cable Operator --</option>
                            @foreach($operators as $op)
                            <option value="{{ $op->id }}" {{ $voucher->operator_id == $op->id ? 'selected' : '' }}>{{ $op->operator_name }} ({{ $op->operator_code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Voucher Remarks / Intake Batch Note</label>
                    <input type="text" name="remarks" class="form-control form-control-kv" value="{{ old('remarks', $voucher->remarks) }}">
                </div>

                <!-- Barcode Scan Field -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <label class="form-label fw-bold small text-dark"><i class="bi bi-qr-code-scan me-1 text-primary"></i> Scan Additional Barcode into Voucher Grid</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary px-3" type="button" onclick="startCameraScanner('#edit_scan_barcode_input')" title="Click to scan barcode using camera">
                            <i class="bi bi-camera-fill text-primary fs-5"></i>
                        </button>
                        <input type="text" id="edit_scan_barcode_input" class="form-control form-control-kv fs-5 fw-bold" placeholder="Scan barcode to add to grid...">
                        <button class="btn btn-primary btn-kv-primary px-4" type="button" id="btnEditAddScan">
                            <i class="bi bi-plus-lg me-1"></i> Add to Grid
                        </button>
                    </div>
                </div>

                <!-- Voucher Item Grid Table -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0"><i class="bi bi-list-check me-1 text-primary"></i> Scanned STB Boxes Grid</h6>
                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-7" id="editVoucherBoxCountBadge">{{ count($voucher->items) }} STB Units</span>
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
                            <tbody id="editVoucherGridBody">
                                @foreach($voucher->items as $index => $item)
                                <tr id="gridRow_{{ $item->set_top_box_id }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-primary"></i> {{ $item->barcode_number }}</div>
                                        <input type="hidden" name="box_ids[]" value="{{ $item->set_top_box_id }}">
                                    </td>
                                    <td>{{ $item->setTopBox->boxModel->model_name ?? ($item->setTopBox->box_name ?? 'N/A') }}</td>
                                    <td>
                                        <span class="badge {{ $item->setTopBox->status_badge_class ?? 'bg-warning-subtle text-warning' }} rounded-pill px-3 py-1 fw-bold fs-8">
                                            {{ $item->setTopBox->status_label ?? 'Complaint' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeEditBoxFromGrid({{ $item->set_top_box_id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Footer Submit -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('stb-checkin.index') }}" class="btn btn-light rounded-3">Cancel</a>
                    <button type="submit" class="btn btn-kv-primary py-2.5 px-4 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> Update Check-In Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let scannedBoxIds = new Set([
        @foreach($voucher->items as $item)
            {{ $item->set_top_box_id }},
        @endforeach
    ]);
    let gridCounter = {{ count($voucher->items) }};
    let editOperatorSelect = null;

    $(document).ready(function() {
        if (document.getElementById('edit_voucher_operator_id')) {
            editOperatorSelect = new TomSelect('#edit_voucher_operator_id', {
                create: false,
                placeholder: 'Type to search Cable Operator...',
                allowEmptyOption: true
            });
        }
    });

    $('#edit_scan_barcode_input').on('keydown keypress', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            processEditBarcodeLookup();
            return false;
        }
    });

    $('#btnEditAddScan').on('click', function(e) {
        e.preventDefault();
        processEditBarcodeLookup();
    });

    function processEditBarcodeLookup() {
        let barcode = $('#edit_scan_barcode_input').val().trim();
        if (!barcode) return;

        $.ajax({
            url: "{{ route('stb-checkin.lookup') }}",
            data: { barcode: barcode },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success && res.found) {
                    addEditBoxToGrid(res.box);
                    $('#edit_scan_barcode_input').val('').focus();
                } else {
                    alert('Barcode ' + barcode + ' not found in database.');
                }
            }
        });
    }

    $('#editVoucherForm').on('submit', function(e) {
        if (scannedBoxIds.size === 0) {
            e.preventDefault();
            alert('Please scan at least one STB box into the grid before updating the voucher.');
            $('#edit_scan_barcode_input').focus();
            return false;
        }
    });

    function addEditBoxToGrid(box) {
        if (scannedBoxIds.has(box.id)) {
            alert('Barcode ' + box.barcode_number + ' is already in this voucher!');
            return;
        }

        scannedBoxIds.add(box.id);
        gridCounter++;

        let rowHtml = `
        <tr id="gridRow_${box.id}" class="table-success">
            <td>${gridCounter}</td>
            <td>
                <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-primary"></i> ${box.barcode_number}</div>
                <input type="hidden" name="box_ids[]" value="${box.id}">
            </td>
            <td>${box.box_name}</td>
            <td><span class="badge ${box.status_badge_class || 'bg-warning-subtle text-warning'} rounded-pill px-3 py-1 fw-bold fs-8">${box.status_label || 'Complaint'}</span></td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeEditBoxFromGrid(${box.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#editVoucherGridBody').append(rowHtml);
        updateEditBoxCount();
    }

    function removeEditBoxFromGrid(boxId) {
        $(`#gridRow_${boxId}`).remove();
        scannedBoxIds.delete(boxId);
        updateEditBoxCount();
    }

    function updateEditBoxCount() {
        $('#editVoucherBoxCountBadge').text(scannedBoxIds.size + ' STB Units');
    }
</script>
@endpush
