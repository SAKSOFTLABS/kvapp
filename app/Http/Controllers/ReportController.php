<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Staff;
use App\Models\SetTopBox;
use App\Models\MainStock;
use App\Models\StaffStock;
use App\Models\StockTransfer;
use App\Models\StockTransaction;
use App\Models\ServiceTransaction;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $reportCategory = ($request->routeIs('reports.service') || $request->get('type') === 'service') ? 'service' : 'stock';

        if ($reportCategory === 'service') {
            $tab = 'service';
        } else {
            $allowedStockTabs = ['total_stock', 'main_stock', 'staff_stock', 'stock_transfer', 'low_stock'];
            $tab = $request->get('type', 'total_stock');
            if (!in_array($tab, $allowedStockTabs)) {
                $tab = 'total_stock';
            }
        }

        $staffList = Staff::where('status', 'active')->orderBy('name')->get();
        $itemList = Item::where('status', 'active')->orderBy('item_name')->get();
        $boxes = SetTopBox::where('status', 'active')->orderBy('box_name')->get();

        $data = [];

        switch ($tab) {
            case 'total_stock':
                $query = Item::with(['mainStock', 'staffStocks']);
                if ($request->filled('item_id')) {
                    $query->where('id', $request->item_id);
                }
                $data = $query->paginate(20)->withQueryString();
                break;

            case 'main_stock':
                $query = MainStock::with('item');
                if ($request->filled('item_id')) {
                    $query->where('item_id', $request->item_id);
                }
                $data = $query->paginate(20)->withQueryString();
                break;

            case 'staff_stock':
                $query = StaffStock::with(['staff', 'item']);
                if ($request->filled('staff_id')) {
                    $query->where('staff_id', $request->staff_id);
                }
                if ($request->filled('item_id')) {
                    $query->where('item_id', $request->item_id);
                }
                $data = $query->paginate(20)->withQueryString();
                break;

            case 'stock_transfer':
                $query = StockTransfer::with(['staff', 'item', 'creator']);
                if ($request->filled('staff_id')) {
                    $query->where('staff_id', $request->staff_id);
                }
                if ($request->filled('item_id')) {
                    $query->where('item_id', $request->item_id);
                }
                if ($request->filled('date_from')) {
                    $query->whereDate('transfer_date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('transfer_date', '<=', $request->date_to);
                }
                $data = $query->orderBy('transfer_date', 'desc')->paginate(20)->withQueryString();
                break;

            case 'service':
                $query = ServiceTransaction::with(['setTopBox', 'technician', 'items.item', 'creator']);
                if ($request->filled('staff_id')) {
                    $query->where('staff_id', $request->staff_id);
                }
                if ($request->filled('stb_id')) {
                    $query->where('set_top_box_id', $request->stb_id);
                }
                if ($request->filled('date_from')) {
                    $query->whereDate('service_date', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('service_date', '<=', $request->date_to);
                }
                $data = $query->orderBy('service_date', 'desc')->paginate(20)->withQueryString();
                break;

            case 'low_stock':
                $threshold = 10;
                $data = MainStock::with('item')
                    ->where('quantity', '<=', $threshold)
                    ->orderBy('quantity', 'asc')
                    ->get();
                break;
        }

        return view('reports.index', compact('reportCategory', 'tab', 'staffList', 'itemList', 'boxes', 'data'));
    }

    public function exportCsv(Request $request)
    {
        $tab = $request->get('type', 'main_stock');
        $filename = "kerala_vision_report_{$tab}_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($tab) {
            $file = fopen('php://output', 'w');

            if ($tab === 'total_stock') {
                fputcsv($file, ['Item Code', 'Item Name', 'Main Store Qty', 'Staff Issued Qty', 'Total Stock Qty', 'Purchase Price (₹)', 'Total Valuation (₹)']);
                $items = Item::with(['mainStock', 'staffStocks'])->get();
                foreach ($items as $i) {
                    $mainQty = $i->mainStock->quantity ?? 0;
                    $staffQty = $i->staffStocks->sum('quantity');
                    $totalQty = $mainQty + $staffQty;
                    $price = $i->purchase_price ?? 0;
                    fputcsv($file, [
                        $i->item_code ?? '',
                        $i->item_name ?? '',
                        $mainQty,
                        $staffQty,
                        $totalQty,
                        $price,
                        $totalQty * $price
                    ]);
                }
            } elseif ($tab === 'main_stock') {
                fputcsv($file, ['Item Code', 'Item Name', 'Main Stock Qty', 'Purchase Price (₹)', 'Total Value (₹)']);
                $stocks = MainStock::with('item')->get();
                foreach ($stocks as $s) {
                    fputcsv($file, [
                        $s->item->item_code ?? '',
                        $s->item->item_name ?? '',
                        $s->quantity,
                        $s->item->purchase_price ?? 0,
                        $s->quantity * ($s->item->purchase_price ?? 0)
                    ]);
                }
            } elseif ($tab === 'staff_stock') {
                fputcsv($file, ['Staff Name', 'Item Code', 'Item Name', 'Staff Stock Qty']);
                $stocks = StaffStock::with(['staff', 'item'])->get();
                foreach ($stocks as $s) {
                    fputcsv($file, [
                        $s->staff->name ?? '',
                        $s->item->item_code ?? '',
                        $s->item->item_name ?? '',
                        $s->quantity
                    ]);
                }
            } elseif ($tab === 'service') {
                fputcsv($file, ['Service Code', 'Date', 'Set Top Box', 'Barcode', 'Technician', 'Total Cost (₹)', 'Remarks']);
                $services = ServiceTransaction::with(['setTopBox', 'technician'])->get();
                foreach ($services as $srv) {
                    fputcsv($file, [
                        $srv->service_code,
                        $srv->service_date,
                        $srv->setTopBox->box_name ?? '',
                        $srv->setTopBox->barcode_number ?? '',
                        $srv->technician->name ?? '',
                        $srv->total_cost,
                        $srv->remarks
                    ]);
                }
            } else {
                fputcsv($file, ['Report Type', $tab]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
