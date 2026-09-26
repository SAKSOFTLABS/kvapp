@extends('layouts.app')

@section('title', 'STB BOX Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-tv-fill text-success me-2"></i> STB BOX Report</h3>
        <p class="text-muted small mb-0">Comprehensive inventory breakdown of Set Top Boxes across Models, Cable Operators, Intake Statuses, and Send to PUD units.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.stb-box.export', array_merge(request()->query(), ['tab' => $tab])) }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (CSV)
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-3 no-print">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4 no-print">
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-primary shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Total STBs</div>
            <div class="fs-4 fw-extrabold text-primary mt-1">{{ number_format($totalStbs) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-warning shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">In Complaints</div>
            <div class="fs-4 fw-extrabold text-warning mt-1">{{ number_format($totalComplaint) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-info shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Service Done</div>
            <div class="fs-4 fw-extrabold text-info mt-1">{{ number_format($totalServiceDone) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-success shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">QC Passed</div>
            <div class="fs-4 fw-extrabold text-success mt-1">{{ number_format($totalTestedOk) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-danger shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Send to PUD</div>
            <div class="fs-4 fw-extrabold text-danger mt-1">{{ number_format($totalSendToPud) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-secondary shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Delivered Out</div>
            <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($totalDelivered) }}</div>
        </div>
    </div>
</div>

<!-- Report Submenu Nav Tabs -->
<div class="kv-card mb-4 p-2 overflow-x-auto no-print">
    <div class="nav nav-pills flex-nowrap gap-1">
        <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'total_boxes' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stb-box', ['tab' => 'total_boxes']) }}">
            <i class="bi bi-box-seam me-1"></i> Total Boxes
        </a>
        <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'model_wise' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stb-box', ['tab' => 'model_wise']) }}">
            <i class="bi bi-cpu me-1"></i> Box Model Wise
        </a>
        <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'operator_wise' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stb-box', ['tab' => 'operator_wise']) }}">
            <i class="bi bi-people me-1"></i> Cable Operator Wise
        </a>
        <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'pud_wise' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stb-box', ['tab' => 'pud_wise']) }}">
            <i class="bi bi-send-exclamation me-1"></i> Send to PUD Wise
        </a>
    </div>
</div>

@if(in_array($tab, ['total_boxes', 'pud_wise']))
<!-- Filters Card -->
<div class="kv-card mb-4 p-3 no-print">
    <form action="{{ route('reports.stb-box') }}" method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="tab" value="{{ $tab }}">

        <div class="col-12 col-md-3">
            <input type="text" name="search" class="form-control form-control-sm form-control-kv" value="{{ request('search') }}" placeholder="Search Barcode / Box Name...">
        </div>

        <div class="col-12 col-md-3">
            <select name="box_model_id" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Box Models --</option>
                @foreach($boxModels as $bm)
                <option value="{{ $bm->id }}" {{ request('box_model_id') == $bm->id ? 'selected' : '' }}>{{ $bm->model_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-3">
            <select name="operator_id" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Cable Operators --</option>
                @foreach($operators as $op)
                <option value="{{ $op->id }}" {{ request('operator_id') == $op->id ? 'selected' : '' }}>{{ $op->operator_name }} ({{ $op->operator_code }})</option>
                @endforeach
            </select>
        </div>

        @if($tab === 'total_boxes')
        <div class="col-12 col-md-2">
            <select name="stb_status" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Statuses --</option>
                <option value="complaint" {{ request('stb_status') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                <option value="reservice" {{ request('stb_status') == 'reservice' ? 'selected' : '' }}>Reservice</option>
                <option value="service_done" {{ request('stb_status') == 'service_done' ? 'selected' : '' }}>Service Done</option>
                <option value="tested_ok" {{ request('stb_status') == 'tested_ok' ? 'selected' : '' }}>Tested OK (QC Passed)</option>
                <option value="send_to_pud" {{ request('stb_status') == 'send_to_pud' ? 'selected' : '' }}>Send to PUD</option>
                <option value="flash" {{ request('stb_status') == 'flash' ? 'selected' : '' }}>Flash (Dead Box)</option>
                <option value="software_issue" {{ request('stb_status') == 'software_issue' ? 'selected' : '' }}>Software Issue</option>
            </select>
        </div>
        @endif

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
            <a href="{{ route('reports.stb-box', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>
@endif

<!-- Table Content -->
<div class="kv-table-wrapper">
    @if($tab === 'total_boxes')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Barcode / Serial No</th>
                    <th>Box Model</th>
                    <th>Cable Operator</th>
                    <th>Intake / Current Status</th>
                    <th>Store / Delivery State</th>
                    <th>Registered Date</th>
                    <th class="text-end no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td><code>{{ $row->barcode_number }}</code></td>
                    <td class="fw-bold">{{ $row->boxModel->model_name ?? $row->box_name }}</td>
                    <td><span class="badge bg-secondary-subtle text-dark">{{ $row->operator->operator_name ?? 'Unassigned' }}</span></td>
                    <td>
                        @if($row->stb_status == 'tested_ok')
                            <span class="badge bg-success text-white"><i class="bi bi-patch-check-fill me-1"></i> QC Passed</span>
                        @elseif($row->stb_status == 'service_done')
                            <span class="badge bg-info text-white"><i class="bi bi-tools me-1"></i> Service Done</span>
                        @elseif($row->stb_status == 'send_to_pud')
                            <span class="badge bg-danger text-white"><i class="bi bi-send-exclamation-fill me-1"></i> Send to PUD</span>
                        @elseif(in_array($row->stb_status, ['flash', 'software_issue']))
                            <span class="badge bg-dark text-white"><i class="bi bi-x-circle-fill me-1"></i> Dead ({{ ucfirst($row->stb_status) }})</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ ucfirst($row->stb_status) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($row->isDelivered())
                            <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-all me-1"></i> Delivered Out</span>
                        @else
                            <span class="badge bg-primary-subtle text-primary border border-primary"><i class="bi bi-building me-1"></i> In Workshop</span>
                        @endif
                    </td>
                    <td>{{ $row->created_at ? $row->created_at->format('d M Y, h:i A') : 'N/A' }}</td>
                    <td class="text-end no-print">
                        <a href="{{ route('reports.stb-history', ['sort' => 'desc', 'search' => $row->barcode_number]) }}" class="btn btn-outline-primary btn-xs rounded-pill">
                            <i class="bi bi-clock-history me-1"></i> History
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No STB records found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'model_wise')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Box Model Name</th>
                    <th class="text-center">Total STBs</th>
                    <th class="text-center">Complaint / Repairing</th>
                    <th class="text-center">Service Completed</th>
                    <th class="text-center">QC Tested OK</th>
                    <th class="text-center">Send to PUD</th>
                    <th class="text-center">Dead Boxes</th>
                    <th class="text-center">Delivered Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summaryList as $m)
                <tr>
                    <td class="fw-extrabold text-primary fs-6"><i class="bi bi-cpu me-2"></i>{{ $m['model_name'] }}</td>
                    <td class="text-center"><span class="badge bg-primary-subtle text-primary fs-6 fw-extrabold px-3 py-2">{{ number_format($m['total']) }}</span></td>
                    <td class="text-center"><span class="badge bg-warning-subtle text-warning-emphasis fs-6 fw-bold">{{ number_format($m['complaint']) }}</span></td>
                    <td class="text-center"><span class="badge bg-info-subtle text-info fs-6 fw-bold">{{ number_format($m['service_done']) }}</span></td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success fs-6 fw-bold">{{ number_format($m['tested_ok']) }}</span></td>
                    <td class="text-center"><span class="badge bg-danger-subtle text-danger fs-6 fw-bold">{{ number_format($m['send_to_pud']) }}</span></td>
                    <td class="text-center"><span class="badge bg-secondary-subtle text-dark fs-6 fw-bold">{{ number_format($m['dead']) }}</span></td>
                    <td class="text-center"><span class="badge bg-success text-white fs-6 fw-bold">{{ number_format($m['delivered']) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No box models found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'operator_wise')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Operator Code</th>
                    <th>Cable Operator Name</th>
                    <th class="text-center">Total STBs</th>
                    <th class="text-center">Complaint / Repairing</th>
                    <th class="text-center">Service Completed</th>
                    <th class="text-center">QC Tested OK</th>
                    <th class="text-center">Send to PUD</th>
                    <th class="text-center">Dead Boxes</th>
                    <th class="text-center">Delivered Out</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summaryList as $op)
                <tr>
                    <td><code>{{ $op['operator_code'] }}</code></td>
                    <td class="fw-extrabold text-dark fs-6"><i class="bi bi-building me-2 text-primary"></i>{{ $op['operator_name'] }}</td>
                    <td class="text-center"><span class="badge bg-primary-subtle text-primary fs-6 fw-extrabold px-3 py-2">{{ number_format($op['total']) }}</span></td>
                    <td class="text-center"><span class="badge bg-warning-subtle text-warning-emphasis fs-6 fw-bold">{{ number_format($op['complaint']) }}</span></td>
                    <td class="text-center"><span class="badge bg-info-subtle text-info fs-6 fw-bold">{{ number_format($op['service_done']) }}</span></td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success fs-6 fw-bold">{{ number_format($op['tested_ok']) }}</span></td>
                    <td class="text-center"><span class="badge bg-danger-subtle text-danger fs-6 fw-bold">{{ number_format($op['send_to_pud']) }}</span></td>
                    <td class="text-center"><span class="badge bg-secondary-subtle text-dark fs-6 fw-bold">{{ number_format($op['dead']) }}</span></td>
                    <td class="text-center"><span class="badge bg-success text-white fs-6 fw-bold">{{ number_format($op['delivered']) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No cable operator records found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab === 'pud_wise')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Barcode / Serial No</th>
                    <th>Box Model</th>
                    <th>Cable Operator</th>
                    <th>Status</th>
                    <th>PUD Sent / Updated Date</th>
                    <th>Remarks</th>
                    <th class="text-end no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td><code>{{ $row->barcode_number }}</code></td>
                    <td class="fw-bold">{{ $row->boxModel->model_name ?? $row->box_name }}</td>
                    <td><span class="badge bg-secondary-subtle text-dark">{{ $row->operator->operator_name ?? 'Unassigned' }}</span></td>
                    <td><span class="badge bg-danger text-white"><i class="bi bi-send-exclamation-fill me-1"></i> Send to PUD</span></td>
                    <td>{{ $row->updated_at ? $row->updated_at->format('d M Y, h:i A') : 'N/A' }}</td>
                    <td class="small text-muted">{{ $row->remarks ?? '-' }}</td>
                    <td class="text-end no-print">
                        <a href="{{ route('reports.stb-history', ['sort' => 'desc', 'search' => $row->barcode_number]) }}" class="btn btn-outline-primary btn-xs rounded-pill">
                            <i class="bi bi-clock-history me-1"></i> History
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-check-circle text-success me-1"></i> No STBs are currently sent to external service centre (Send to PUD).</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif
</div>

@if(is_object($data) && method_exists($data, 'links'))
<div class="mt-4 no-print">
    {{ $data->links() }}
</div>
@endif
@endsection
