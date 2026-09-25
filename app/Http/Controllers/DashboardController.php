<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Staff;
use App\Models\SetTopBox;
use App\Models\MainStock;
use App\Models\StaffStock;
use App\Models\StockTransfer;
use App\Models\ServiceTransaction;
use App\Models\QcCheck;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Role Flags (Exclude Admin)
        $isServiceUser = $user->isService() && !$user->isAdmin();
        $isQcUser = $user->isQc() && !$user->isAdmin();

        if ($isServiceUser) {
            $staffId = $user->staff_id;
            if (!$staffId && $user->staff) {
                $staffId = $user->staff->id;
            }

            // 1. User Personal Spare Parts Stock
            $myStockItems = StaffStock::with('item')
                ->where('staff_id', $staffId)
                ->get();

            $myTotalStockQty = (int) StaffStock::where('staff_id', $staffId)->sum('quantity');
            $myTotalStockTypes = StaffStock::where('staff_id', $staffId)->where('quantity', '>', 0)->count();

            // 2. User Total Serviced Boxes
            $myServiceQuery = ServiceTransaction::where(function ($q) use ($staffId, $user) {
                if ($staffId) {
                    $q->where('staff_id', $staffId);
                } else {
                    $q->where('created_by', $user->id);
                }
            });

            $totalMyServices = (clone $myServiceQuery)->count();
            $todaysMyServices = (clone $myServiceQuery)->whereDate('service_date', now()->toDateString())->count();
            $monthlyMyServices = (clone $myServiceQuery)->whereMonth('service_date', now()->month)->whereYear('service_date', now()->year)->count();

            // 3. QC Rejected Boxes (Services done by this technician that failed QC inspection)
            $myQcRejectedCount = QcCheck::whereHas('serviceTransaction', function ($q) use ($staffId, $user) {
                if ($staffId) {
                    $q->where('staff_id', $staffId);
                } else {
                    $q->where('created_by', $user->id);
                }
            })->whereIn('qc_status', ['complaint', 'flash'])->count();

            $myQcRejectedLogs = QcCheck::with(['setTopBox.boxModel', 'serviceTransaction'])
                ->whereHas('serviceTransaction', function ($q) use ($staffId, $user) {
                    if ($staffId) {
                        $q->where('staff_id', $staffId);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })
                ->whereIn('qc_status', ['complaint', 'flash'])
                ->latest()
                ->take(5)
                ->get();

            // 4. Low Stock Alert for Technician (threshold = 5)
            $threshold = 5;
            $myLowStockCount = StaffStock::where('staff_id', $staffId)->where('quantity', '<=', $threshold)->count();
            $myLowStockItems = StaffStock::with('item')
                ->where('staff_id', $staffId)
                ->where('quantity', '<=', $threshold)
                ->get();

            // 5. Technician's Recent Service Activity
            $recentServices = ServiceTransaction::with(['setTopBox.boxModel', 'technician', 'creator'])
                ->where(function ($q) use ($staffId, $user) {
                    if ($staffId) {
                        $q->where('staff_id', $staffId);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })
                ->latest()
                ->take(5)
                ->get();

            // 6. Recent Stock Transfers received by Technician
            $recentTransfers = StockTransfer::with(['staff', 'item', 'creator'])
                ->where('staff_id', $staffId)
                ->latest()
                ->take(5)
                ->get();

            // 7. Chart Data - Monthly Services (last 6 months for technician)
            $chartMonths = [];
            $chartServiceCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $chartMonths[] = $dt->format('M Y');
                $chartServiceCounts[] = ServiceTransaction::where(function ($q) use ($staffId, $user) {
                    if ($staffId) {
                        $q->where('staff_id', $staffId);
                    } else {
                        $q->where('created_by', $user->id);
                    }
                })
                ->whereYear('service_date', $dt->year)
                ->whereMonth('service_date', $dt->month)
                ->count();
            }

            // 8. Chart Data - Top 5 items in technician's personal stock
            $topStockItems = StaffStock::join('items', 'staff_stocks.item_id', '=', 'items.id')
                ->where('staff_stocks.staff_id', $staffId)
                ->select('items.item_name', 'staff_stocks.quantity')
                ->orderBy('staff_stocks.quantity', 'desc')
                ->take(5)
                ->get();

            return view('dashboard', compact(
                'isServiceUser',
                'isQcUser',
                'myTotalStockQty',
                'myTotalStockTypes',
                'myStockItems',
                'totalMyServices',
                'todaysMyServices',
                'monthlyMyServices',
                'myQcRejectedCount',
                'myQcRejectedLogs',
                'myLowStockCount',
                'myLowStockItems',
                'recentServices',
                'recentTransfers',
                'chartMonths',
                'chartServiceCounts',
                'topStockItems'
            ));
        }

        if ($isQcUser) {
            // 1. Total QC Tested Boxes by this Inspector
            $totalMyQcTested = QcCheck::where('qc_user_id', $user->id)->count();
            $todaysMyQcTested = QcCheck::where('qc_user_id', $user->id)->whereDate('qc_date', now()->toDateString())->count();
            $monthlyMyQcTested = QcCheck::where('qc_user_id', $user->id)->whereMonth('qc_date', now()->month)->whereYear('qc_date', now()->year)->count();

            // 2. QC Passed Boxes (Tested OK) by this Inspector
            $myQcPassedCount = QcCheck::where('qc_user_id', $user->id)->where('qc_status', 'tested_ok')->count();

            // 3. QC Rejected Boxes (Complaint / Flash) by this Inspector
            $myQcRejectedCount = QcCheck::where('qc_user_id', $user->id)->whereIn('qc_status', ['complaint', 'flash'])->count();

            // 4. Pending QC Boxes (Awaiting Testing Queue)
            $pendingQcCount = SetTopBox::where('stb_status', 'service_done')->count();

            // 5. Low stock alerts (Inspector's assigned staff stock if any)
            $staffId = $user->staff_id;
            $myLowStockCount = $staffId ? StaffStock::where('staff_id', $staffId)->where('quantity', '<=', 5)->count() : 0;
            $myLowStockItems = $staffId ? StaffStock::with('item')->where('staff_id', $staffId)->where('quantity', '<=', 5)->get() : collect();

            // 6. Recent QC Logs performed by this inspector
            $recentMyQcLogs = QcCheck::with(['setTopBox.operator', 'setTopBox.boxModel', 'serviceTransaction'])
                ->where('qc_user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            // 7. Queue of Boxes awaiting QC Testing
            $pendingQcBoxes = SetTopBox::with(['boxModel', 'operator'])
                ->where('stb_status', 'service_done')
                ->orderBy('updated_at', 'asc')
                ->take(5)
                ->get();

            // 8. 6-Month QC Performance Chart
            $chartMonths = [];
            $chartServiceCounts = [];
            for ($i = 5; $i >= 0; $i--) {
                $dt = now()->subMonths($i);
                $chartMonths[] = $dt->format('M Y');
                $chartServiceCounts[] = QcCheck::where('qc_user_id', $user->id)
                    ->whereYear('qc_date', $dt->year)
                    ->whereMonth('qc_date', $dt->month)
                    ->count();
            }

            return view('dashboard', compact(
                'isServiceUser',
                'isQcUser',
                'totalMyQcTested',
                'todaysMyQcTested',
                'monthlyMyQcTested',
                'myQcPassedCount',
                'myQcRejectedCount',
                'pendingQcCount',
                'myLowStockCount',
                'myLowStockItems',
                'recentMyQcLogs',
                'pendingQcBoxes',
                'chartMonths',
                'chartServiceCounts'
            ));
        }

        // --- Standard Admin / Staff / Front Office Overall System Dashboard ---
        $isServiceUser = false;
        $isQcUser = false;
        $totalItems = Item::where('status', 'active')->count();
        $totalStaff = Staff::where('status', 'active')->count();
        $totalBoxes = SetTopBox::where('status', 'active')->count();

        // Main Stock Value
        $mainStockValue = MainStock::join('items', 'main_stocks.item_id', '=', 'items.id')
            ->selectRaw('SUM(main_stocks.quantity * items.purchase_price) as total_val')
            ->value('total_val') ?? 0;

        $todaysServices = ServiceTransaction::whereDate('service_date', now()->toDateString())->count();
        $monthlyServices = ServiceTransaction::whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year)
            ->count();

        // Low stock threshold
        $threshold = 10;
        $lowStockCount = MainStock::where('quantity', '<=', $threshold)->count();

        $recentTransfers = StockTransfer::with(['staff', 'item', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $recentServices = ServiceTransaction::with(['setTopBox', 'technician', 'creator'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        // Low stock items list for quick alert card
        $lowStockItems = MainStock::with('item')
            ->where('quantity', '<=', $threshold)
            ->get();

        // Chart Data - Monthly Services (last 6 months)
        $chartMonths = [];
        $chartServiceCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->subMonths($i);
            $chartMonths[] = $dt->format('M Y');
            $chartServiceCounts[] = ServiceTransaction::whereYear('service_date', $dt->year)
                ->whereMonth('service_date', $dt->month)
                ->count();
        }

        // Chart Data - Top 5 items by stock
        $topStockItems = MainStock::join('items', 'main_stocks.item_id', '=', 'items.id')
            ->select('items.item_name', 'main_stocks.quantity')
            ->orderBy('main_stocks.quantity', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'isServiceUser',
            'isQcUser',
            'totalItems',
            'totalStaff',
            'totalBoxes',
            'mainStockValue',
            'todaysServices',
            'monthlyServices',
            'lowStockCount',
            'recentTransfers',
            'recentServices',
            'recentActivities',
            'lowStockItems',
            'chartMonths',
            'chartServiceCounts',
            'topStockItems'
        ));
    }
}
