@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@if(!empty($isServiceUser) && $isServiceUser)
<!-- ========================================== -->
<!-- SERVICE TECHNICIAN USER-WISE DASHBOARD     -->
<!-- ========================================== -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-extrabold mb-0"><i class="bi bi-person-badge text-primary me-2"></i> Technician Dashboard</h2>
        <p class="text-muted small mb-0">Personal inventory & servicing stats for <strong>{{ Auth::user()->name }}</strong></p>
    </div>
    @if(!Auth::user()->isFrontOfficeOnly())
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto mt-2 mt-md-0">
        <a href="{{ route('service.index') }}" class="btn btn-kv-primary btn-sm flex-fill text-center px-3">
            <i class="bi bi-tools me-1"></i> New Service Ticket
        </a>
    </div>
    @endif
</div>

<!-- Technician Primary Stat Cards (User-Wise) -->
<div class="row g-3 mb-4">
    <!-- 1. My Personal Stock -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">My Personal Stock</div>
                <div class="metric-value">{{ number_format($myTotalStockQty) }} <span class="fs-7 text-muted fw-normal">Units</span></div>
                <span class="badge bg-primary-subtle text-primary mt-1">{{ $myTotalStockTypes }} Spare Item Types</span>
            </div>
            <div class="metric-icon-box metric-icon-blue">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <!-- 2. Total Serviced Boxes -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Total Serviced Boxes</div>
                <div class="metric-value">{{ number_format($totalMyServices) }} <span class="fs-7 text-muted fw-normal">STBs</span></div>
                <span class="badge bg-success-subtle text-success mt-1">Completed Services</span>
            </div>
            <div class="metric-icon-box metric-icon-emerald">
                <i class="bi bi-tools"></i>
            </div>
        </div>
    </div>

    <!-- 3. QC Rejected Boxes -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">QC Rejected Boxes</div>
                <div class="metric-value text-danger">{{ number_format($myQcRejectedCount) }}</div>
                @if($myQcRejectedCount > 0)
                    <span class="badge bg-danger-subtle text-danger mt-1">Requires Re-Work</span>
                @else
                    <span class="badge bg-success-subtle text-success mt-1">Zero Rejections</span>
                @endif
            </div>
            <div class="metric-icon-box" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <i class="bi bi-x-octagon"></i>
            </div>
        </div>
    </div>

    <!-- 4. Low Stock Alert -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Low Stock Alert</div>
                <div class="metric-value text-warning">{{ number_format($myLowStockCount) }} <span class="fs-7 text-muted fw-normal">Items</span></div>
                @if($myLowStockCount > 0)
                    <span class="badge bg-warning-subtle text-warning mt-1">Refill Required</span>
                @else
                    <span class="badge bg-success-subtle text-success mt-1">Stock Sufficient</span>
                @endif
            </div>
            <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary User Stat Summary -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-primary-subtle text-primary" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-calendar-check fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">TODAY'S SERVICES</div>
                <div class="fs-4 fw-extrabold">{{ $todaysMyServices }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-success-subtle text-success" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-graph-up-arrow fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">THIS MONTH'S SERVICES</div>
                <div class="fs-4 fw-extrabold">{{ $monthlyMyServices }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-danger-subtle text-danger" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-shield-x fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">QC REJECTED</div>
                <div class="fs-4 fw-extrabold text-danger">{{ $myQcRejectedCount }} Boxes</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-warning-subtle text-warning" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-boxes fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">LOW STOCK SPARES</div>
                <div class="fs-4 fw-extrabold text-warning">{{ $myLowStockCount }} Items</div>
            </div>
        </div>
    </div>
</div>

<!-- Technician Charts Row -->
<div class="row g-4 mb-4">
    <!-- Technician Monthly Service Trend -->
    <div class="col-12 col-lg-7">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i> My Monthly Servicing Output</h5>
                <span class="text-muted small">Last 6 Months</span>
            </div>
            <div style="height: 280px;">
                <canvas id="techServiceTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Personal Spare Stock Levels -->
    <div class="col-12 col-lg-5">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-success me-2"></i> My Personal Spare Parts</h5>
                <span class="text-muted small">Stock Breakdown</span>
            </div>
            <div style="height: 280px;" class="d-flex align-items-center justify-content-center">
                <canvas id="techInventoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Technician Specific Tables -->
<div class="row g-4">
    <!-- Personal Spare Parts Stock Grid & Low Stock Alerts -->
    <div class="col-12 col-lg-6">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-box-seam text-primary me-2"></i> My Spare Parts Inventory</h5>
                <span class="badge bg-secondary-subtle text-secondary">{{ count($myStockItems) }} Items Assigned</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>Code</th>
                            <th class="text-center">My Stock Qty</th>
                            <th class="text-end">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myStockItems as $stk)
                        <tr>
                            <td class="fw-bold text-dark">{{ $stk->item->item_name ?? 'N/A' }}</td>
                            <td><code>{{ $stk->item->item_code ?? '' }}</code></td>
                            <td class="text-center fw-bold fs-6">
                                <span class="badge {{ $stk->quantity <= 5 ? 'bg-danger text-white' : 'bg-primary-subtle text-primary' }} rounded-pill px-3 py-1">
                                    {{ $stk->quantity }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if($stk->quantity <= 0)
                                    <span class="badge bg-danger-subtle text-danger">Out of Stock</span>
                                @elseif($stk->quantity <= 5)
                                    <span class="badge bg-warning-subtle text-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No spare parts currently assigned to your stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- My Recent Serviced Boxes & QC Rejections -->
    <div class="col-12 col-lg-6">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-tools text-success me-2"></i> My Recent Serviced Boxes</h5>
                <a href="{{ route('service.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Service Code</th>
                            <th>STB Barcode</th>
                            <th>Date</th>
                            <th class="text-end">Parts Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentServices as $srv)
                        <tr>
                            <td><span class="fw-bold text-primary">{{ $srv->service_code }}</span></td>
                            <td>
                                <div><strong>{{ $srv->setTopBox->box_name ?? 'STB' }}</strong></div>
                                <code>{{ $srv->setTopBox->barcode_number ?? 'N/A' }}</code>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($srv->service_date)->format('d M Y') }}</td>
                            <td class="text-end fw-bold">₹{{ number_format($srv->total_cost, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No recent service records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@elseif(!empty($isQcUser) && $isQcUser)
<!-- ========================================== -->
<!-- QC INSPECTOR USER-WISE DASHBOARD           -->
<!-- ========================================== -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-extrabold mb-0"><i class="bi bi-patch-check-fill text-success me-2"></i> QC Inspector Dashboard</h2>
        <p class="text-muted small mb-0">Quality inspection metrics & logs for <strong>{{ Auth::user()->name }}</strong></p>
    </div>
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto mt-2 mt-md-0">
        <a href="{{ route('qc.index') }}" class="btn btn-success btn-sm flex-fill text-center px-3 fw-bold">
            <i class="bi bi-qr-code-scan me-1"></i> Start QC Testing
        </a>
    </div>
</div>

<!-- QC Inspector Stat Cards -->
<div class="row g-3 mb-4">
    <!-- 1. Total QC Tested -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Total QC Tested</div>
                <div class="metric-value">{{ number_format($totalMyQcTested) }} <span class="fs-7 text-muted fw-normal">Inspections</span></div>
                <span class="badge bg-primary-subtle text-primary mt-1">By Me</span>
            </div>
            <div class="metric-icon-box metric-icon-blue">
                <i class="bi bi-patch-check"></i>
            </div>
        </div>
    </div>

    <!-- 2. QC Passed (Tested OK) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">QC Passed (Tested OK)</div>
                <div class="metric-value text-success">{{ number_format($myQcPassedCount) }}</div>
                <span class="badge bg-success-subtle text-success mt-1">Ready for Delivery</span>
            </div>
            <div class="metric-icon-box metric-icon-emerald">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- 3. QC Rejected (Complaint/Flash) -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">QC Rejected Boxes</div>
                <div class="metric-value text-danger">{{ number_format($myQcRejectedCount) }}</div>
                @if($myQcRejectedCount > 0)
                    <span class="badge bg-danger-subtle text-danger mt-1">Sent Back for Re-Work</span>
                @else
                    <span class="badge bg-success-subtle text-success mt-1">Zero Rejections</span>
                @endif
            </div>
            <div class="metric-icon-box" style="background: rgba(239, 68, 68, 0.15); color: #ef4444;">
                <i class="bi bi-x-octagon"></i>
            </div>
        </div>
    </div>

    <!-- 4. Pending QC Queue -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Pending QC Queue</div>
                <div class="metric-value text-warning">{{ number_format($pendingQcCount) }} <span class="fs-7 text-muted fw-normal">Boxes</span></div>
                <span class="badge bg-warning-subtle text-warning mt-1">Awaiting Inspection</span>
            </div>
            <div class="metric-icon-box" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary QC Summary Bar -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 bg-primary-subtle text-primary"><i class="bi bi-calendar-check fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">TODAY'S QC TESTS</div>
                <div class="fs-4 fw-extrabold">{{ $todaysMyQcTested }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 bg-success-subtle text-success"><i class="bi bi-graph-up-arrow fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">THIS MONTH'S QC</div>
                <div class="fs-4 fw-extrabold">{{ $monthlyMyQcTested }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 bg-success-subtle text-success"><i class="bi bi-shield-check fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">QC PASSED</div>
                <div class="fs-4 fw-extrabold text-success">{{ $myQcPassedCount }} Boxes</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 bg-danger-subtle text-danger"><i class="bi bi-shield-x fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">QC REJECTED</div>
                <div class="fs-4 fw-extrabold text-danger">{{ $myQcRejectedCount }} Boxes</div>
            </div>
        </div>
    </div>
</div>

<!-- QC Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-12 col-lg-7">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-success me-2"></i> My Monthly QC Inspections Trend</h5>
                <span class="text-muted small">Last 6 Months</span>
            </div>
            <div style="height: 280px;">
                <canvas id="qcInspectorTrendChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-5">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-primary me-2"></i> My QC Inspection Results</h5>
                <span class="text-muted small">Passed vs Rejected Ratio</span>
            </div>
            <div style="height: 280px;" class="d-flex align-items-center justify-content-center">
                <canvas id="qcRatioChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- QC Tables Row -->
<div class="row g-4">
    <!-- Queue Awaiting QC Testing -->
    <div class="col-12 col-lg-6">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-hourglass-split text-warning me-2"></i> Serviced Boxes Awaiting QC</h5>
                <a href="{{ route('qc.index') }}" class="btn btn-sm btn-link text-decoration-none">View Full Queue</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>STB Barcode</th>
                            <th>Model</th>
                            <th>Operator</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingQcBoxes as $pBox)
                        <tr>
                            <td><code>{{ $pBox->barcode_number }}</code></td>
                            <td>{{ $pBox->box_name }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $pBox->operator->operator_name ?? 'N/A' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('qc.index') }}" class="btn btn-sm btn-outline-success rounded-3 py-0 px-2 fs-8">
                                    <i class="bi bi-check2-circle me-1"></i> Test Now
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No boxes currently awaiting QC testing.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- My Recent QC Inspection Log -->
    <div class="col-12 col-lg-6">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-clock-history text-primary me-2"></i> My Recent QC Inspection Log</h5>
                <a href="{{ route('qc.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Barcode</th>
                            <th>Date</th>
                            <th>QC Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentMyQcLogs as $qLog)
                        <tr>
                            <td><code>{{ $qLog->setTopBox->barcode_number ?? 'N/A' }}</code></td>
                            <td>{{ \Carbon\Carbon::parse($qLog->qc_date)->format('d M Y') }}</td>
                            <td>
                                @if($qLog->qc_status == 'tested_ok')
                                    <span class="badge bg-success-subtle text-success">Passed (Tested OK)</span>
                                @elseif($qLog->qc_status == 'flash')
                                    <span class="badge bg-danger-subtle text-danger">Flash (Dead Box)</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Complaint</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $qLog->remarks ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No recent QC inspections recorded by you.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@else
<!-- ========================================== -->
<!-- OVERALL SYSTEM DASHBOARD (Admin/Staff)     -->
<!-- ========================================== -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
    <div>
        <h2 class="fw-extrabold mb-0">Dashboard</h2>
    </div>
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto mt-2 mt-md-0">
        @if(Auth::user()->isAdmin())
        <a href="{{ route('add-stock.index') }}" class="btn btn-kv-primary btn-sm flex-fill text-center"><i class="bi bi-plus-circle me-1"></i> Add Stock</a>
        <a href="{{ route('stock-transfer.index') }}" class="btn btn-kv-accent btn-sm flex-fill text-center"><i class="bi bi-arrow-left-right me-1"></i> Transfer Stock</a>
        @endif
        @if(!Auth::user()->isFrontOfficeOnly())
        <a href="{{ route('service.index') }}" class="btn btn-outline-primary btn-sm rounded-3 flex-fill text-center"><i class="bi bi-tools me-1"></i> New Service</a>
        @endif
    </div>
</div>

<!-- Metrics Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Items -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Total Items</div>
                <div class="metric-value">{{ number_format($totalItems) }}</div>
                <span class="badge bg-primary-subtle text-primary mt-1">Active Catalog</span>
            </div>
            <div class="metric-icon-box metric-icon-blue">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>

    <!-- Total Staff -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Total Staff</div>
                <div class="metric-value">{{ number_format($totalStaff) }}</div>
                <span class="badge bg-success-subtle text-success mt-1">Technicians</span>
            </div>
            <div class="metric-icon-box metric-icon-emerald">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <!-- Set Top Boxes -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Set Top Boxes</div>
                <div class="metric-value">{{ number_format($totalBoxes) }}</div>
                <span class="badge bg-info-subtle text-info mt-1">Tracked STBs</span>
            </div>
            <div class="metric-icon-box metric-icon-blue" style="background: rgba(6, 182, 212, 0.15); color: #06b6d4;">
                <i class="bi bi-tv"></i>
            </div>
        </div>
    </div>

    <!-- Main Stock Value -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kv-card metric-card">
            <div>
                <div class="metric-label">Main Stock Value</div>
                <div class="metric-value">₹{{ number_format($mainStockValue, 2) }}</div>
                <span class="badge bg-success-subtle text-success mt-1">Valuation</span>
            </div>
            <div class="metric-icon-box metric-icon-emerald">
                <i class="bi bi-currency-rupee"></i>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-primary-subtle text-primary" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-calendar-check fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">TODAY'S SERVICES</div>
                <div class="fs-4 fw-extrabold">{{ $todaysServices }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-success-subtle text-success" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-graph-up-arrow fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">THIS MONTH'S SERVICES</div>
                <div class="fs-4 fw-extrabold">{{ $monthlyServices }}</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="kv-card py-3 d-flex align-items-center gap-3">
            <div class="icon-circle bg-danger-subtle text-danger" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; border-radius: 50% !important; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;"><i class="bi bi-exclamation-triangle fs-4"></i></div>
            <div>
                <div class="text-muted small fw-bold">LOW STOCK ALERTS</div>
                <div class="fs-4 fw-extrabold text-danger">{{ $lowStockCount }} Items</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Service Trend Chart -->
    <div class="col-12 col-lg-7">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Monthly Service Trend</h5>
                <span class="text-muted small">Last 6 Months</span>
            </div>
            <div style="height: 280px;">
                <canvas id="serviceTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Inventory Top Stock Chart -->
    <div class="col-12 col-lg-5">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-pie-chart-fill text-success me-2"></i> Main Stock Levels</h5>
                <span class="text-muted small">Top Items</span>
            </div>
            <div style="height: 280px;" class="d-flex align-items-center justify-content-center">
                <canvas id="inventoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tables & Activity Row -->
<div class="row g-4">
    <!-- Recent Services -->
    <div class="col-12 col-lg-6">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-tools text-primary me-2"></i> Recent Service Records</h5>
                <a href="{{ route('service.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>STB / Barcode</th>
                            <th>Technician</th>
                            <th>Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentServices as $srv)
                        <tr>
                            <td><span class="fw-bold text-primary">{{ $srv->service_code }}</span></td>
                            <td>
                                <div><strong>{{ $srv->setTopBox->box_name ?? 'N/A' }}</strong></div>
                                <code class="text-muted">{{ $srv->setTopBox->barcode_number ?? '' }}</code>
                            </td>
                            <td>{{ $srv->technician->name ?? 'N/A' }}</td>
                            <td class="fw-bold">₹{{ number_format($srv->total_cost, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No recent services recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transfers -->
    <div class="col-12 col-lg-6">
        <div class="kv-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0"><i class="bi bi-arrow-left-right text-success me-2"></i> Recent Stock Transfers</h5>
                @if(Auth::user()->isAdmin())
                <a href="{{ route('stock-transfer.index') }}" class="btn btn-sm btn-link text-decoration-none">View All</a>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transfer Code</th>
                            <th>Technician</th>
                            <th>Item</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransfers as $trf)
                        <tr>
                            <td><span class="fw-bold text-success">{{ $trf->transfer_code }}</span></td>
                            <td>{{ $trf->staff->name ?? 'N/A' }}</td>
                            <td>{{ $trf->item->item_name ?? 'N/A' }}</td>
                            <td><span class="badge bg-success-subtle text-success fs-7">{{ $trf->quantity }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3">No recent transfers.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
@if(!empty($isServiceUser) && $isServiceUser)
<script>
    // Technician Chart 1: Servicing Output Trend
    const ctxTechTrend = document.getElementById('techServiceTrendChart').getContext('2d');
    new Chart(ctxTechTrend, {
        type: 'line',
        data: {
            labels: @json($chartMonths),
            datasets: [{
                label: 'Services Completed',
                data: @json($chartServiceCounts),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Technician Chart 2: Personal Inventory Breakdown
    const ctxTechInv = document.getElementById('techInventoryChart').getContext('2d');
    const techStockData = @json($topStockItems);
    new Chart(ctxTechInv, {
        type: 'doughnut',
        data: {
            labels: techStockData.length > 0 ? techStockData.map(i => i.item_name) : ['No Stock'],
            datasets: [{
                data: techStockData.length > 0 ? techStockData.map(i => i.quantity) : [1],
                backgroundColor: techStockData.length > 0 ? ['#2563eb', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6'] : ['#cbd5e1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
</script>
@elseif(!empty($isQcUser) && $isQcUser)
<script>
    // QC Inspector Chart 1: Monthly Inspection Output
    const ctxQcTrend = document.getElementById('qcInspectorTrendChart').getContext('2d');
    new Chart(ctxQcTrend, {
        type: 'line',
        data: {
            labels: @json($chartMonths),
            datasets: [{
                label: 'QC Inspections',
                data: @json($chartServiceCounts),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#10b981'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    // QC Inspector Chart 2: Passed vs Rejected Ratio
    const ctxQcRatio = document.getElementById('qcRatioChart').getContext('2d');
    const passedCount = {{ $myQcPassedCount }};
    const rejectedCount = {{ $myQcRejectedCount }};
    new Chart(ctxQcRatio, {
        type: 'doughnut',
        data: {
            labels: ['Passed (Tested OK)', 'Rejected (Complaint/Flash)'],
            datasets: [{
                data: (passedCount === 0 && rejectedCount === 0) ? [1, 0] : [passedCount, rejectedCount],
                backgroundColor: (passedCount === 0 && rejectedCount === 0) ? ['#cbd5e1', '#ef4444'] : ['#10b981', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
</script>
@else
<script>
    // Chart 1: Monthly Service Trend
    const ctxTrend = document.getElementById('serviceTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: @json($chartMonths),
            datasets: [{
                label: 'Services Completed',
                data: @json($chartServiceCounts),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Chart 2: Inventory Distribution
    const ctxInv = document.getElementById('inventoryChart').getContext('2d');
    const topStockData = @json($topStockItems);
    new Chart(ctxInv, {
        type: 'doughnut',
        data: {
            labels: topStockData.map(i => i.item_name),
            datasets: [{
                data: topStockData.map(i => i.quantity),
                backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#06b6d4', '#8b5cf6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
            }
        }
    });
</script>
@endif
@endpush
