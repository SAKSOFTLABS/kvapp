<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\StockTransaction;
use App\Services\StockManagementService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class AddStockController extends Controller
{
    protected $stockService;

    public function __construct(StockManagementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $items = Item::where('status', 'active')->orderBy('item_name')->get();

        $query = StockTransaction::with(['item', 'creator'])->where('transaction_type', 'purchase');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($iq) use ($search) {
                    $iq->where('item_name', 'like', "%{$search}%")
                       ->orWhere('item_code', 'like', "%{$search}%");
                })->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('transactions.add_stock', compact('items', 'transactions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'purchase_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            $tx = $this->stockService->addMainStock($validated, Auth::id());
            
            $item = Item::find($validated['item_id']);
            ActivityLogService::log('ADD_STOCK', "Added {$validated['quantity']} units of {$item->item_name} to Main Stock");

            return redirect()->route('add-stock.index')->with('success', 'Stock added to Main Store successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'purchase_price' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            $this->stockService->updateMainStock($id, $validated, Auth::id());
            
            $item = Item::find($validated['item_id']);
            ActivityLogService::log('UPDATE_ADD_STOCK', "Updated stock transaction #{$id} for {$item->item_name}");

            return redirect()->route('add-stock.index')->with('success', 'Stock entry updated successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->stockService->deleteMainStock($id, Auth::id());
            
            ActivityLogService::log('DELETE_ADD_STOCK', "Deleted stock transaction #{$id}");

            return redirect()->route('add-stock.index')->with('success', 'Stock entry deleted successfully.');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
