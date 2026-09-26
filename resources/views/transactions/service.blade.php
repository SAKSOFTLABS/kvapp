@extends('layouts.app')

@section('title', 'Service Section')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-0"><i class="bi bi-tools text-primary me-2"></i> Service Section</h3>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Active Service Form (Full Width) -->
    <div class="col-12">
        <div class="kv-card">
            <form action="{{ route('service.store') }}" method="POST" id="serviceForm">
                @csrf

                <!-- Section 1: Service Header (Code, Date, Technician) -->
                <div class="row g-3 mb-4 border-bottom pb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold small mb-1">Service Code # *</label>
                        <input type="text" class="form-control form-control-kv" value="{{ $nextServiceCode }}" readonly>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold small mb-1">Service Date *</label>
                        <input type="date" name="service_date" class="form-control form-control-kv" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-bold small mb-1">Technician / Staff *</label>
                        <select name="staff_id" id="service_staff_id" class="form-select form-select-kv" required>
                            <option value=""></option>
                            @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}" {{ (Auth::user()->isStaff() && Auth::user()->staff_id == $staff->id) ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->designation }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 2: Barcode Scanner ONLY (Removed Dropdown) -->
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-qr-code-scan text-primary me-2"></i> Scan STB Box Barcode *</h6>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary px-3" type="button" onclick="startCameraScanner('#service_barcode_scan_input')" title="Scan barcode with camera">
                            <i class="bi bi-camera-fill text-primary fs-5"></i>
                        </button>
                        <input type="text" id="service_barcode_scan_input" class="form-control form-control-kv fs-5 fw-bold" placeholder="Scan barcode with laser scanner or camera..." autofocus>
                        <button class="btn btn-primary btn-kv-primary px-4" type="button" id="btnLookupStb">
                            <i class="bi bi-search me-1"></i> Scan Barcode
                        </button>
                    </div>

                    <!-- Hidden Input for Selected STB Box ID -->
                    <input type="hidden" name="set_top_box_id" id="selected_set_top_box_id" required>

                    <!-- Selected STB Box Live Summary Card -->
                    <div id="stbSummaryCard" class="d-none mt-3 p-3 bg-white border rounded-3 shadow-sm">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-4">
                                <div class="text-muted small fw-bold">Barcode Number</div>
                                <div class="fs-5 fw-extrabold text-primary" id="cardBarcode">--</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="text-muted small fw-bold">Box Model</div>
                                <div class="fs-6 fw-bold text-dark" id="cardBoxName">--</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="text-muted small fw-bold">Cable Operator</div>
                                <div class="fs-6 fw-bold text-secondary" id="cardOperator">--</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Spare Parts Used Grid (Type & Search Dropdown) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <h6 class="fw-bold m-0"><i class="bi bi-box-seam me-1 text-primary"></i> Spare Parts Used Grid</h6>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3" id="btnAddSpareRow">
                            <i class="bi bi-plus-lg me-1"></i> Add Spare Part Row
                        </button>
                    </div>

                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Spare Part Item (Type & Search)</th>
                                    <th style="width: 180px;">Quantity Used</th>
                                    <th class="text-end" style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="sparePartsGridBody">
                                <tr id="emptyPartsRow">
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-box-seam fs-2 d-block mb-1 text-secondary opacity-50"></i>
                                        No spare parts selected (0 stock deduction). Click "Add Spare Part Row" if parts were replaced.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Service Remarks / Job Note</label>
                    <input type="text" name="remarks" class="form-control form-control-kv" placeholder="e.g. Cleaned board, replaced capacitor.">
                </div>

                <!-- Section 4: Service Action Buttons (Placed After Grid & Dynamic Labels) -->
                <div class="p-3 bg-light rounded-3 border pt-4 border-top">
                    <label class="form-label fw-bold small text-muted d-block mb-2 text-uppercase">Select Service Action to Finalize *</label>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="action_type" value="complete" id="btnCompleteAction" class="btn btn-success btn-kv-accent py-2.5 px-3 fs-6 fw-bold">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i> Complete
                        </button>
                        <button type="submit" name="action_type" value="flash" id="btnFlashAction" class="btn btn-danger py-2.5 px-3 fs-6 fw-bold rounded-3">
                            <i class="bi bi-x-circle-fill me-2 fs-5"></i> Flash (Dead Box)
                        </button>
                        <button type="submit" name="action_type" value="software_issue" id="btnSoftwareAction" class="btn btn-warning py-2.5 px-3 fs-6 fw-bold rounded-3 text-dark">
                            <i class="bi bi-laptop-fill me-2 fs-5"></i> Software Issue (Dead Box)
                        </button>
                        <button type="submit" name="action_type" value="send_to_pud" id="btnPudAction" class="btn btn-secondary py-2.5 px-3 fs-6 fw-bold rounded-3">
                            <i class="bi bi-truck me-2 fs-5"></i> Send to PUD
                        </button>
                        <button type="button" class="btn btn-light rounded-3 ms-auto" onclick="resetForm()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Past Service Entries History List -->
<div class="kv-card">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i> Recent Service Entries</h5>
        
        <form action="{{ route('service.index') }}" method="GET" class="d-flex gap-2">
            @if(Auth::user()->isAdmin())
            <select name="staff_id" class="form-select form-select-sm form-select-kv" onchange="this.form.submit()">
                <option value="">All Technicians</option>
                @foreach($staffList as $st)
                <option value="{{ $st->id }}" {{ request('staff_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
            @endif
            <input type="text" name="search" class="form-control form-control-sm form-control-kv" placeholder="Search service #, barcode..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-primary btn-kv-primary">Filter</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Service Code</th>
                    <th>Date</th>
                    <th>STB Barcode & Model</th>
                    <th>Technician</th>
                    <th>Service Action</th>
                    <th>Spare Parts Used</th>
                    <th class="text-end text-nowrap" style="min-width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $srv)
                <tr>
                    <td>
                        <a href="{{ route('service.show', $srv->id) }}" class="fw-bold text-primary text-decoration-none">
                            {{ $srv->service_code }}
                        </a>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($srv->service_date)->format('d M Y') }}</td>
                    <td>
                        <div class="fw-bold text-dark"><i class="bi bi-upc-scan text-primary me-1"></i> {{ $srv->setTopBox->barcode_number ?? 'N/A' }}</div>
                        <div class="text-muted small">{{ $srv->setTopBox->box_name ?? '' }}</div>
                    </td>
                    <td>{{ $srv->technician->name ?? 'N/A' }}</td>
                    <td>
                        @if($srv->setTopBox && $srv->setTopBox->stb_status === 'flash')
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">Flashed (Dead Box)</span>
                        @elseif($srv->setTopBox && $srv->setTopBox->stb_status === 'software_issue')
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1"><i class="bi bi-laptop me-1"></i> Software Issue (Dead Box)</span>
                        @elseif($srv->setTopBox && $srv->setTopBox->stb_status === 'send_to_pud')
                            <span class="badge bg-secondary-subtle text-dark rounded-pill px-3 py-1"><i class="bi bi-truck me-1"></i> Sent to PUD</span>
                        @else
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Service Done (QC Pending)</span>
                        @endif
                    </td>
                    <td>
                        @if($srv->items && $srv->items->count() > 0)
                            <div class="small fw-bold text-dark">
                                @foreach($srv->items as $si)
                                    <span class="badge bg-secondary-subtle text-dark border me-1 mb-1">
                                        {{ $si->item->item_name ?? 'Part' }} x {{ (float)$si->quantity }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted small">None (0 Parts)</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <div class="d-inline-flex align-items-center justify-content-end gap-1">
                            <a href="{{ route('service.show', $srv->id) }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                <i class="bi bi-eye me-1"></i> View
                            </a>
                            <form action="{{ route('service.destroy', $srv->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this service entry?');">
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
                    <td colspan="7" class="text-center text-muted py-4">No service entries recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $services->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    let partsRowCounter = 0;
    let technicianStock = [];
    let staffSelect = null;
    let tomSelectInstances = {};

    $(document).ready(function() {
        // Initialize Tom Select for Technician
        if (document.getElementById('service_staff_id')) {
            staffSelect = new TomSelect('#service_staff_id', {
                create: false,
                placeholder: 'Type to search Technician...',
                allowEmptyOption: true,
                dropdownParent: 'body'
            });

            staffSelect.on('change', function(val) {
                if (val) loadTechnicianStock(val);
            });

            let initialStaff = $('#service_staff_id').val();
            if (initialStaff) {
                loadTechnicianStock(initialStaff);
            }
        }

        updateActionButtonsLabel();
    });

    function loadTechnicianStock(staffId) {
        $.ajax({
            url: "{{ url('/transactions/service/technician-stock') }}/" + staffId,
            success: function(res) {
                if (res.success) {
                    technicianStock = res.stocks;
                }
            }
        });
    }

    function updateActionButtonsLabel() {
        let hasParts = $('#sparePartsGridBody tr[id^="partRow_"]').length > 0;

        if (hasParts) {
            $('#btnCompleteAction').html('<i class="bi bi-check-circle-fill me-2 fs-5"></i> Complete & Deduct Stock');
            $('#btnFlashAction').html('<i class="bi bi-x-circle-fill me-2 fs-5"></i> Flash & Deduct Stock (Dead Box)');
            $('#btnSoftwareAction').html('<i class="bi bi-laptop-fill me-2 fs-5"></i> Software Issue & Deduct Stock');
            $('#btnPudAction').html('<i class="bi bi-truck me-2 fs-5"></i> Send to PUD & Deduct Stock');
        } else {
            $('#btnCompleteAction').html('<i class="bi bi-check-circle-fill me-2 fs-5"></i> Complete');
            $('#btnFlashAction').html('<i class="bi bi-x-circle-fill me-2 fs-5"></i> Flash (Dead Box)');
            $('#btnSoftwareAction').html('<i class="bi bi-laptop-fill me-2 fs-5"></i> Software Issue (Dead Box)');
            $('#btnPudAction').html('<i class="bi bi-truck me-2 fs-5"></i> Send to PUD');
        }
    }

    // Barcode Lookup Trigger
    $('#btnLookupStb, #service_barcode_scan_input').on('click keyup change', function(e) {
        if (e.type === 'keyup' && e.key !== 'Enter') return;

        let barcode = $('#service_barcode_scan_input').val().trim();
        if (!barcode) return;

        $.ajax({
            url: "{{ route('stb-checkin.lookup') }}",
            data: { barcode: barcode },
            headers: { 'Accept': 'application/json' },
            success: function(res) {
                if (res.success && res.found) {
                    if (res.box.is_delivered) {
                        alert('BOX IS NOT CHECKED IN FROM FRONT OFFICE');
                        $('#selected_set_top_box_id').val('');
                        $('#stbSummaryCard').addClass('d-none');
                        $('#service_barcode_scan_input').val('').focus();
                        return;
                    }
                    $('#selected_set_top_box_id').val(res.box.id);
                    displayStbSummaryCard(res.box);
                    $('#service_barcode_scan_input').val('');
                } else {
                    alert('Barcode ' + barcode + ' not found in database. Please check barcode number.');
                }
            }
        });
    });

    function displayStbSummaryCard(boxObj) {
        $('#cardBarcode').text(boxObj.barcode_number);
        $('#cardBoxName').text(boxObj.box_name);
        $('#cardOperator').text(boxObj.operator_name || 'Unassigned');
        $('#stbSummaryCard').removeClass('d-none');
    }

    // Add Spare Part Row to Grid
    $('#btnAddSpareRow').on('click', function() {
        let staffId = $('#service_staff_id').val();
        if (!staffId) {
            alert('Please select a Technician first to load available spare parts.');
            return;
        }

        partsRowCounter++;
        $('#emptyPartsRow').remove();

        let itemOptions = '<option value=""></option>';
        technicianStock.forEach(stk => {
            itemOptions += `<option value="${stk.item_id}">
                ${stk.item_name} (${stk.item_code}) - Avail: ${stk.available_qty}
            </option>`;
        });

        let rowHtml = `
        <tr id="partRow_${partsRowCounter}">
            <td>${partsRowCounter}</td>
            <td>
                <select name="items[${partsRowCounter}][item_id]" id="spare_part_select_${partsRowCounter}" class="form-select form-select-sm form-select-kv" required>
                    ${itemOptions}
                </select>
            </td>
            <td>
                <input type="number" name="items[${partsRowCounter}][quantity]" class="form-control form-control-sm form-control-kv" value="1" min="1" required>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="removeSpareRow(${partsRowCounter})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;

        $('#sparePartsGridBody').append(rowHtml);

        // Initialize Tom Select with placeholder string and empty default option
        let selectId = `#spare_part_select_${partsRowCounter}`;
        tomSelectInstances[partsRowCounter] = new TomSelect(selectId, {
            create: false,
            placeholder: 'Type to search spare part...',
            allowEmptyOption: true,
            dropdownParent: 'body'
        });

        updateActionButtonsLabel();
    });

    function removeSpareRow(rowId) {
        if (tomSelectInstances[rowId]) {
            tomSelectInstances[rowId].destroy();
            delete tomSelectInstances[rowId];
        }
        $(`#partRow_${rowId}`).remove();

        if ($('#sparePartsGridBody tr').length === 0) {
            $('#sparePartsGridBody').html(`
                <tr id="emptyPartsRow">
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-box-seam fs-2 d-block mb-1 text-secondary opacity-50"></i>
                        No spare parts selected (0 stock deduction). Click "Add Spare Part Row" if parts were replaced.
                    </td>
                </tr>
            `);
        }

        updateActionButtonsLabel();
    }

    function resetForm() {
        if (confirm('Are you sure you want to reset the form?')) {
            $('#selected_set_top_box_id').val('');
            $('#stbSummaryCard').addClass('d-none');

            // Destroy Tom Select instances
            Object.keys(tomSelectInstances).forEach(id => {
                if (tomSelectInstances[id]) tomSelectInstances[id].destroy();
            });
            tomSelectInstances = {};

            $('#sparePartsGridBody').html(`
                <tr id="emptyPartsRow">
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-box-seam fs-2 d-block mb-1 text-secondary opacity-50"></i>
                        No spare parts selected (0 stock deduction). Click "Add Spare Part Row" if parts were replaced.
                    </td>
                </tr>
            `);

            updateActionButtonsLabel();
        }
    }

    $('#serviceForm').on('submit', function(e) {
        let stbId = $('#selected_set_top_box_id').val();
        if (!stbId) {
            e.preventDefault();
            alert('Please scan an STB Box barcode for servicing.');
            $('#service_barcode_scan_input').focus();
            return false;
        }
    });
</script>
@endpush
