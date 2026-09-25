<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Staff;
use App\Models\MainStock;
use App\Models\StaffStock;
use App\Models\StockTransfer;
use App\Services\StockManagementService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class StockTransferController extends Controller
{
    protected $stockService;

    public function __construct(StockManagementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $staffList = Staff::where('status', 'active')->orderBy('name')->get();
        $items = Item::with('mainStock')->where('status', 'active')->orderBy('item_name')->get();

        $query = StockTransfer::with(['staff', 'item', 'creator']);

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transfer_code', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('staff', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('item', function ($iq) use ($search) {
                      $iq->where('item_name', 'like', "%{$search}%");
                  });
            });
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('transactions.stock_transfer', compact('staffList', 'items', 'transfers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transfer_date' => 'required|date',
            'staff_id' => 'required|exists:staff,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        try {
            $transfer = $this->stockService->transferStock($validated, Auth::id());

            $staff = Staff::find($validated['staff_id']);
            $item = Item::find($validated['item_id']);

            ActivityLogService::log('STOCK_TRANSFER', "Transferred {$validated['quantity']} units of {$item->item_name} to {$staff->name} (Transfer: {$transfer->transfer_code})");

            return redirect()->route('stock-transfer.index')->with('success', "Stock transferred to {$staff->name} successfully.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'transfer_date' => 'required|date',
            'staff_id' => 'required|exists:staff,id',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        try {
            $transfer = $this->stockService->updateStockTransfer($id, $validated, Auth::id());

            $staff = Staff::find($validated['staff_id']);
            $item = Item::find($validated['item_id']);

            ActivityLogService::log('UPDATE_STOCK_TRANSFER', "Updated stock transfer {$transfer->transfer_code} to {$staff->name}");

            return redirect()->route('stock-transfer.index')->with('success', "Stock transfer updated successfully.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->stockService->deleteStockTransfer($id, Auth::id());

            ActivityLogService::log('DELETE_STOCK_TRANSFER', "Deleted stock transfer #{$id}");

            return redirect()->route('stock-transfer.index')->with('success', "Stock transfer deleted and balances reverted successfully.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function getItemStockInfo($itemId)
    {
        $mainStock = MainStock::where('item_id', $itemId)->first();
        $qty = $mainStock ? (float)$mainStock->quantity : 0;
        $item = Item::find($itemId);

        return response()->json([
            'success' => true,
            'item_id' => $itemId,
            'item_name' => $item ? $item->item_name : '',
            'main_stock_qty' => (int)$qty,
            'sales_price' => $item ? (float)$item->sales_price : 0,
        ]);
    }
}
