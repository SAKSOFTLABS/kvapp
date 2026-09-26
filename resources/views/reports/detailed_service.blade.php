@extends('layouts.app')

@section('title', 'Detailed Service Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-file-earmark-medical text-danger me-2"></i> Detailed Service Report</h3>
        <p class="text-muted small mb-0">Comprehensive service breakdown with multi-criteria filters for Box Models, Technicians, Date Ranges, and Repair Statuses.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.detailed-service.export', request()->query()) }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (CSV)
        </a>
        <a href="{{ route('reports.detailed-service.print', request()->query()) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-3 no-print">
            <i class="bi bi-printer me-1"></i> Print Report
        </a>
    </div>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4 no-print">
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-primary shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Total Services</div>
            <div class="fs-4 fw-extrabold text-primary mt-1">{{ number_format($totalServices) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-success shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Repaired / Done</div>
            <div class="fs-4 fw-extrabold text-success mt-1">{{ number_format($repairedCount) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-danger shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Send to PUD</div>
            <div class="fs-4 fw-extrabold text-danger mt-1">{{ number_format($pudCount) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-dark shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Flash (Dead)</div>
            <div class="fs-4 fw-extrabold text-dark mt-1">{{ number_format($flashCount) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-warning shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Software Issue</div>
            <div class="fs-4 fw-extrabold text-warning mt-1">{{ number_format($softwareIssueCount) }}</div>
        </div>
    </div>
    <div class="col-6 col-sm-4 col-md-2">
        <div class="kv-card p-3 text-center border-start border-4 border-info shadow-sm h-100">
            <div class="text-uppercase small text-muted fw-bold">Total Parts Cost</div>
            <div class="fs-5 fw-extrabold text-info mt-1">₹{{ number_format($totalCostSum, 2) }}</div>
        </div>
    </div>
</div>

<!-- Multi-Criteria Filter Card -->
<div class="kv-card mb-4 p-3 no-print">
    <form action="{{ route('reports.detailed-service') }}" method="GET" class="row g-2 align-items-center">
        <!-- Search Keyword / Barcode -->
        <div class="col-12 col-md-3">
            <label class="form-label fw-bold small mb-1">Search Barcode / Ticket</label>
            <input type="text" name="search" class="form-control form-control-sm form-control-kv" value="{{ request('search') }}" placeholder="Search Barcode / Service Code...">
        </div>

        <!-- Box Model Filter -->
        <div class="col-12 col-md-2">
            <label class="form-label fw-bold small mb-1">Box Model Wise</label>
            <select name="box_model_id" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Box Models --</option>
                @foreach($boxModels as $bm)
                <option value="{{ $bm->id }}" {{ request('box_model_id') == $bm->id ? 'selected' : '' }}>{{ $bm->model_name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Staff / Technician Filter -->
        <div class="col-12 col-md-2">
            <label class="form-label fw-bold small mb-1">Staff / Technician</label>
            <select name="staff_id" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Staff / Techs --</option>
                @foreach($staffList as $st)
                <option value="{{ $st->id }}" {{ request('staff_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Repaired Box / Action Type Filter -->
        <div class="col-12 col-md-2">
            <label class="form-label fw-bold small mb-1">Repaired Box / Result</label>
            <select name="action_type" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Repair Statuses --</option>
                <option value="repaired" {{ request('action_type') == 'repaired' ? 'selected' : '' }}>Repaired (Service Done)</option>
                <option value="send_to_pud" {{ request('action_type') == 'send_to_pud' ? 'selected' : '' }}>Send to PUD (Complaint)</option>
                <option value="flash" {{ request('action_type') == 'flash' ? 'selected' : '' }}>Flash (Dead Box)</option>
                <option value="software_issue" {{ request('action_type') == 'software_issue' ? 'selected' : '' }}>Software Issue (Dead Box)</option>
            </select>
        </div>

        <!-- Date From Filter -->
        <div class="col-6 col-md-1.5">
            <label class="form-label fw-bold small mb-1">Date From</label>
            <input type="date" name="date_from" class="form-control form-control-sm form-control-kv" value="{{ request('date_from') }}">
        </div>

        <!-- Date To Filter -->
        <div class="col-6 col-md-1.5">
            <label class="form-label fw-bold small mb-1">Date To</label>
            <input type="date" name="date_to" class="form-control form-control-sm form-control-kv" value="{{ request('date_to') }}">
        </div>

        <!-- Submit & Reset -->
        <div class="col-12 col-md-2 d-flex gap-1 mt-3">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100"><i class="bi bi-filter me-1"></i> Apply</button>
            <a href="{{ route('reports.detailed-service') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

@if(isset($staffPerformance) && count($staffPerformance) > 0)
<!-- Staff Performance Breakdown Summary Table -->
<div class="kv-card mb-4 p-3 no-print">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i> Service Staff Performance Breakdown</h6>
        <span class="badge bg-secondary-subtle text-dark">Staff Box Count Matrix</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered align-middle mb-0 text-center" style="font-size: 13px;">
            <thead class="table-light">
                <tr>
                    <th class="text-start">Technician / Staff Name</th>
                    <th>Repaired / Done</th>
                    <th>Flash Box</th>
                    <th class="table-warning">Software Issue</th>
                    <th>Send to PUD</th>
                    <th>Total Processed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staffPerformance as $sp)
                <tr>
                    <td class="text-start fw-bold"><i class="bi bi-person me-1 text-secondary"></i> {{ $sp['staff']->name }}</td>
                    <td><span class="badge bg-success-subtle text-success fw-bold fs-7">{{ $sp['repaired'] }}</span></td>
                    <td><span class="badge bg-dark-subtle text-dark fw-bold fs-7">{{ $sp['flash'] }}</span></td>
                    <td><span class="badge bg-warning text-dark fw-bold fs-7">{{ $sp['software_issue'] }}</span></td>
                    <td><span class="badge bg-danger-subtle text-danger fw-bold fs-7">{{ $sp['pud'] }}</span></td>
                    <td class="fw-extrabold text-primary fs-7">{{ $sp['total'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Detailed Service Data Table -->
<div class="kv-table-wrapper">
    <table class="kv-table">
        <thead>
            <tr>
                <th>Service Code</th>
                <th>Service Date</th>
                <th>STB Barcode & Model</th>
                <th>Cable Operator</th>
                <th>Assigned Technician</th>
                <th>Service Action / Status</th>
                <th>Total Cost</th>
                <th class="text-end no-print">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($services as $srv)
            @php
                $isPud = str_contains($srv->remarks ?? '', '[SENT TO PUD') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'send_to_pud');
                $isFlash = str_contains($srv->remarks ?? '', '[FLASH') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'flash');
                $isSoftware = str_contains($srv->remarks ?? '', '[SOFTWARE ISSUE') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'software_issue');
            @endphp
            <tr>
                <td>
                    <code class="fw-bold text-primary fs-7">{{ $srv->service_code }}</code>
                </td>
                <td>
                    <div><i class="bi bi-calendar-event text-primary me-1"></i> {{ \Carbon\Carbon::parse($srv->service_date)->format('d M Y') }}</div>
                    <div class="small text-muted font-monospace"><i class="bi bi-clock me-1"></i> {{ $srv->created_at ? $srv->created_at->format('h:i A') : '' }}</div>
                </td>
                <td>
                    <div class="fw-bold text-dark"><i class="bi bi-upc-scan me-1 text-secondary"></i> {{ $srv->setTopBox->barcode_number ?? 'N/A' }}</div>
                    <div class="small text-muted">{{ $srv->setTopBox->boxModel->model_name ?? ($srv->setTopBox->box_name ?? 'N/A') }}</div>
                </td>
                <td>
                    <span class="badge bg-secondary-subtle text-dark">{{ $srv->setTopBox->operator->operator_name ?? 'Unassigned' }}</span>
                </td>
                <td>
                    <div class="fw-bold text-dark"><i class="bi bi-person me-1 text-muted"></i> {{ $srv->technician->name ?? 'N/A' }}</div>
                </td>
                <td>
                    @if($isPud)
                        <span class="badge bg-danger text-white"><i class="bi bi-send-exclamation-fill me-1"></i> Complaint (Send to PUD)</span>
                    @elseif($isFlash)
                        <span class="badge bg-dark text-white"><i class="bi bi-x-circle-fill me-1"></i> Flash (Dead Box)</span>
                    @elseif($isSoftware)
                        <span class="badge bg-dark text-white"><i class="bi bi-laptop me-1"></i> Software Issue (Dead Box)</span>
                    @else
                        <span class="badge bg-success text-white"><i class="bi bi-check-circle-fill me-1"></i> Service Done (Repaired)</span>
                    @endif
                </td>
                <td class="fw-bold text-success">
                    ₹{{ number_format($srv->total_cost, 2) }}
                </td>
                <td class="text-end no-print">
                    @if($srv->setTopBox)
                    <a href="{{ route('reports.stb-history', ['sort' => 'desc', 'search' => $srv->setTopBox->barcode_number]) }}" class="btn btn-outline-primary btn-xs rounded-pill">
                        History
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-4">No detailed service records found matching filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(is_object($services) && method_exists($services, 'links'))
<div class="mt-4 no-print">
    {{ $services->links() }}
</div>
@endif
@endsection
