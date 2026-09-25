<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\MainStock;
use App\Models\StockTransaction;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $query = Item::with('mainStock');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('master.items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:100|unique:items,item_code',
            'opening_stock' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated) {
            $item = Item::create($validated);

            // Update main stock ledger
            MainStock::create([
                'item_id' => $item->id,
                'quantity' => $validated['opening_stock'],
            ]);

            if ($validated['opening_stock'] > 0) {
                StockTransaction::create([
                    'transaction_type' => 'opening',
                    'date' => now()->toDateString(),
                    'item_id' => $item->id,
                    'quantity' => $validated['opening_stock'],
                    'unit_price' => $validated['purchase_price'],
                    'supplier' => 'Opening Stock',
                    'remarks' => 'Item opening balance',
                    'created_by' => Auth::id(),
                ]);
            }

            ActivityLogService::log('CREATE_ITEM', "Created item {$item->item_name} ({$item->item_code}) with opening stock {$validated['opening_stock']}");
        });

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'item_code' => 'required|string|max:100|unique:items,item_code,' . $id,
            'purchase_price' => 'required|numeric|min:0',
            'sales_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $item->update($validated);

        ActivityLogService::log('UPDATE_ITEM', "Updated item {$item->item_name} ({$item->item_code})");

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        // Check if item has transactions or stock
        $transferCount = $item->stockTransfers()->count();
        $serviceCount = $item->serviceItems()->count();

        if ($transferCount > 0 || $serviceCount > 0) {
            return back()->with('error', 'Cannot delete item because it has associated transaction records.');
        }

        DB::transaction(function () use ($item) {
            $item->mainStock()->delete();
            $item->delete();
            ActivityLogService::log('DELETE_ITEM', "Deleted item {$item->item_name}");
        });

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    public function exportCsv()
    {
        $items = Item::with('mainStock')->get();
        $filename = "kerala_vision_items_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Item Code', 'Item Name', 'Opening Stock', 'Current Main Stock', 'Purchase Price (₹)', 'Sales Price (₹)', 'Status']);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->item_code,
                    $item->item_name,
                    $item->opening_stock,
                    $item->main_stock_qty,
                    $item->purchase_price,
                    $item->sales_price,
                    strtoupper($item->status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import items from CSV file (Super Admin Only)
     */
    public function importCsv(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin can import items.');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to read uploaded CSV file.');
        }

        $header = fgetcsv($handle); // Header row

        $importedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 2) {
                    $skippedCount++;
                    continue;
                }

                $itemName = trim($row[0] ?? '');
                $itemCode = trim($row[1] ?? '');
                $openingStock = (float) ($row[2] ?? 0);
                $purchasePrice = (float) ($row[3] ?? 0);
                $salesPrice = (float) ($row[4] ?? 0);
                $description = trim($row[5] ?? '');
                $status = strtolower(trim($row[6] ?? 'active'));
                if (!in_array($status, ['active', 'inactive'])) $status = 'active';

                if (empty($itemName) || empty($itemCode)) {
                    $skippedCount++;
                    continue;
                }

                $existing = Item::where('item_code', $itemCode)->first();
                if ($existing) {
                    $existing->update([
                        'item_name' => $itemName,
                        'purchase_price' => $purchasePrice,
                        'sales_price' => $salesPrice,
                        'description' => $description,
                        'status' => $status,
                    ]);
                    $importedCount++;
                } else {
                    $item = Item::create([
                        'item_name' => $itemName,
                        'item_code' => $itemCode,
                        'opening_stock' => $openingStock,
                        'purchase_price' => $purchasePrice,
                        'sales_price' => $salesPrice,
                        'description' => $description,
                        'status' => $status,
                    ]);

                    MainStock::create([
                        'item_id' => $item->id,
                        'quantity' => $openingStock,
                    ]);

                    if ($openingStock > 0) {
                        StockTransaction::create([
                            'transaction_type' => 'opening',
                            'date' => now()->toDateString(),
                            'item_id' => $item->id,
                            'quantity' => $openingStock,
                            'unit_price' => $purchasePrice,
                            'supplier' => 'CSV Import',
                            'remarks' => 'Imported opening balance',
                            'created_by' => Auth::id(),
                        ]);
                    }
                    $importedCount++;
                }
            }

            fclose($handle);
            DB::commit();

            ActivityLogService::log('IMPORT_ITEMS', "Super Admin imported {$importedCount} items from CSV file.");

            return redirect()->route('items.index')
                ->with('success', "Successfully imported/updated {$importedCount} items from CSV file! ({$skippedCount} invalid rows skipped).");
        } catch (Exception $e) {
            DB::rollBack();
            if ($handle) fclose($handle);
            return back()->with('error', 'CSV Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Sample CSV for Item Import
     */
    public function downloadSampleCsv()
    {
        $filename = "item_import_sample.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['item_name', 'item_code', 'opening_stock', 'purchase_price', 'sales_price', 'description', 'status']);
            fputcsv($file, ['HDMI Cable 1.5m Gold', 'ITM-HDMI-01', '50', '120.00', '250.00', 'High speed 4K HDMI cable 1.5m', 'active']);
            fputcsv($file, ['12V 1.5A Power Adapter', 'ITM-PWR-02', '100', '180.00', '350.00', 'Set top box power supply unit', 'active']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
