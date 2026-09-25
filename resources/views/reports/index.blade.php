@extends('layouts.app')

@section('title', $reportCategory === 'service' ? 'Service Report' : 'Stock Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        @if($reportCategory === 'service')
        <h3 class="fw-extrabold mb-1"><i class="bi bi-tools text-warning me-2"></i> Service Report</h3>
        <p class="text-muted small mb-0">Detailed log statements of Set Top Box repairs, technicians, and service transactions.</p>
        @else
        <h3 class="fw-extrabold mb-1"><i class="bi bi-box-seam text-primary me-2"></i> Stock Report</h3>
        <p class="text-muted small mb-0">Complete audit statements for Total Inventory, Main Store stock, Staff Issued stock, Stock Transfers, and Low Stock Alerts.</p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export-csv', ['type' => $tab]) }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel (CSV)
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-3 no-print">
            <i class="bi bi-printer me-1"></i> Print Report
        </button>
    </div>
</div>

<!-- Report Submenu Nav Pills -->
<div class="kv-card mb-4 p-2 overflow-x-auto no-print">
    <div class="nav nav-pills flex-nowrap gap-1">
        @if($reportCategory === 'service')
            <a class="nav-link py-2 px-3 small fw-bold active btn-kv-primary text-white" href="{{ route('reports.service', ['type' => 'service']) }}">
                <i class="bi bi-journal-text me-1"></i> Service Logs
            </a>
        @else
            <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'total_stock' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stock', ['type' => 'total_stock']) }}">
                <i class="bi bi-boxes me-1"></i> Total Stock
            </a>
            <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'main_stock' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stock', ['type' => 'main_stock']) }}">
                <i class="bi bi-building me-1"></i> Main Stock
            </a>
            <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'staff_stock' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stock', ['type' => 'staff_stock']) }}">
                <i class="bi bi-person-badge me-1"></i> Staff Stock
            </a>
            <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'stock_transfer' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stock', ['type' => 'stock_transfer']) }}">
                <i class="bi bi-arrow-left-right me-1"></i> Stock Transfers
            </a>
            <a class="nav-link py-2 px-3 small fw-bold {{ $tab == 'low_stock' ? 'active btn-kv-primary text-white' : 'text-dark' }}" href="{{ route('reports.stock', ['type' => 'low_stock']) }}">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Low Stock
            </a>
        @endif
    </div>
</div>

<!-- Filters Card -->
<div class="kv-card mb-4 p-3 no-print">
    <form action="{{ $reportCategory === 'service' ? route('reports.service') : route('reports.stock') }}" method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="type" value="{{ $tab }}">

        @if(in_array($tab, ['total_stock', 'main_stock', 'staff_stock', 'stock_transfer']))
        <div class="col-12 col-md-3">
            <select name="item_id" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Items --</option>
                @foreach($itemList as $itm)
                <option value="{{ $itm->id }}" {{ request('item_id') == $itm->id ? 'selected' : '' }}>{{ $itm->item_name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if(in_array($tab, ['staff_stock', 'stock_transfer', 'service']))
        <div class="col-12 col-md-3">
            <select name="staff_id" class="form-select form-select-sm form-select-kv">
                <option value="">-- All Technicians --</option>
                @foreach($staffList as $stf)
                <option value="{{ $stf->id }}" {{ request('staff_id') == $stf->id ? 'selected' : '' }}>{{ $stf->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if(in_array($tab, ['stock_transfer', 'service']))
        <div class="col-6 col-md-3">
            <input type="date" name="date_from" class="form-control form-control-sm form-control-kv" value="{{ request('date_from') }}" placeholder="From Date">
        </div>
        <div class="col-6 col-md-3">
            <input type="date" name="date_to" class="form-control form-control-sm form-control-kv" value="{{ request('date_to') }}" placeholder="To Date">
        </div>
        @endif

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm btn-kv-primary w-100">Apply Filter</button>
            <a href="{{ $reportCategory === 'service' ? route('reports.service', ['type' => $tab]) : route('reports.stock', ['type' => $tab]) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

<!-- Report Content Body -->
<div class="kv-table-wrapper">
    @if($tab == 'total_stock')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Main Store Qty</th>
                    <th>Staff Issued Qty</th>
                    <th>Total Stock Qty</th>
                    <th>Purchase Price</th>
                    <th>Total Stock Valuation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                @php
                    $mainQty = $row->mainStock->quantity ?? 0;
                    $staffQty = $row->staffStocks->sum('quantity');
                    $totalQty = $mainQty + $staffQty;
                @endphp
                <tr>
                    <td><code>{{ $row->item_code }}</code></td>
                    <td class="fw-bold">{{ $row->item_name }}</td>
                    <td><span class="badge bg-secondary-subtle text-dark fs-7 fw-bold">{{ number_format($mainQty, 2) }}</span></td>
                    <td><span class="badge bg-info-subtle text-info fs-7 fw-bold">{{ number_format($staffQty, 2) }}</span></td>
                    <td><span class="badge bg-primary-subtle text-primary fs-7 fw-bold">{{ number_format($totalQty, 2) }}</span></td>
                    <td>₹{{ number_format($row->purchase_price ?? 0, 2) }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($totalQty * ($row->purchase_price ?? 0), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No item records found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab == 'main_stock')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Opening Stock</th>
                    <th>Main Store Qty</th>
                    <th>Purchase Price</th>
                    <th>Sales Price</th>
                    <th>Total Inventory Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td><code>{{ $row->item->item_code ?? 'N/A' }}</code></td>
                    <td class="fw-bold">{{ $row->item->item_name ?? 'N/A' }}</td>
                    <td>{{ number_format($row->item->opening_stock ?? 0, 2) }}</td>
                    <td><span class="badge bg-primary-subtle text-primary fs-7 fw-bold">{{ number_format($row->quantity, 2) }}</span></td>
                    <td>₹{{ number_format($row->item->purchase_price ?? 0, 2) }}</td>
                    <td>₹{{ number_format($row->item->sales_price ?? 0, 2) }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($row->quantity * ($row->item->purchase_price ?? 0), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab == 'staff_stock')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Technician</th>
                    <th>Item Code</th>
                    <th>Item Description</th>
                    <th>Issued Stock Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="fw-bold"><i class="bi bi-person me-1 text-muted"></i> {{ $row->staff->name ?? 'N/A' }}</td>
                    <td><code>{{ $row->item->item_code ?? 'N/A' }}</code></td>
                    <td>{{ $row->item->item_name ?? 'N/A' }}</td>
                    <td><span class="badge bg-success-subtle text-success fs-7 fw-bold">{{ number_format($row->quantity, 2) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No technician stock records found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab == 'stock_transfer')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Recipient Technician</th>
                    <th>Item Transferred</th>
                    <th>Qty</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="fw-bold text-success">{{ $row->transfer_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->transfer_date)->format('d M Y') }}</td>
                    <td class="fw-bold">{{ $row->staff->name ?? 'N/A' }}</td>
                    <td>{{ $row->item->item_name ?? 'N/A' }}</td>
                    <td><span class="badge bg-success-subtle text-success fs-7">{{ number_format($row->quantity, 2) }}</span></td>
                    <td>{{ $row->remarks ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No stock transfers found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab == 'service')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Service Code</th>
                    <th>Date</th>
                    <th>Set Top Box</th>
                    <th>Barcode</th>
                    <th>Technician</th>
                    <th>Total Cost</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td class="fw-bold text-primary">{{ $row->service_code }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->service_date)->format('d M Y') }}</td>
                    <td>{{ $row->setTopBox->box_name ?? 'N/A' }}</td>
                    <td><code class="text-danger">{{ $row->setTopBox->barcode_number ?? '' }}</code></td>
                    <td>{{ $row->technician->name ?? 'N/A' }}</td>
                    <td class="fw-bold text-success">₹{{ number_format($row->total_cost, 2) }}</td>
                    <td>{{ $row->remarks ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No service records found.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($tab == 'low_stock')
        <table class="kv-table">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Main Store Qty</th>
                    <th>Status Alert</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                <tr>
                    <td><code>{{ $row->item->item_code ?? 'N/A' }}</code></td>
                    <td class="fw-bold text-danger">{{ $row->item->item_name ?? 'N/A' }}</td>
                    <td class="fw-bold fs-6">{{ number_format($row->quantity, 2) }}</td>
                    <td><span class="badge bg-danger text-white">CRITICAL LOW STOCK</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-success py-4"><i class="bi bi-check-circle me-1"></i> All items have healthy stock levels above threshold!</td></tr>
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
