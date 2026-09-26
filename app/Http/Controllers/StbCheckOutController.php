<?php

namespace App\Http\Controllers;

use App\Models\SetTopBox;
use App\Models\Operator;
use App\Models\BoxModel;
use App\Models\CheckoutVoucher;
use App\Models\CheckoutVoucherItem;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class StbCheckOutController extends Controller
{
    public function index(Request $request)
    {
        $operators = Operator::where('status', 'active')->orderBy('operator_name')->get();
        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();

        // Auto-generate next Voucher Code e.g. OUT-YYYYMMDD-0001
        $nextVoucherNumber = $this->generateVoucherNumber();

        // Recent Checkout Vouchers Query
        $query = CheckoutVoucher::with(['operator', 'creator', 'items.setTopBox']);

        if ($request->filled('operator_id')) {
            $query->where('operator_id', $request->operator_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('operator', function ($oq) use ($search) {
                      $oq->where('operator_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('barcode_number', 'like', "%{$search}%");
                  });
            });
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('transactions.stb_checkout', compact('operators', 'boxModels', 'nextVoucherNumber', 'vouchers'));
    }

    public function lookupBarcode(Request $request)
    {
        $barcode = trim($request->get('barcode', ''));
        $operatorId = $request->get('operator_id');

        if (empty($barcode)) {
            return response()->json(['success' => false, 'message' => 'Barcode cannot be empty']);
        }

        $box = SetTopBox::with(['boxModel', 'operator'])->where('barcode_number', $barcode)->first();

        if (!$box) {
            return response()->json([
                'success' => true,
                'found' => false,
                'valid' => false,
                'barcode' => $barcode,
                'message' => "Barcode {$barcode} is not found in database."
            ]);
        }

        // Rule 1: Allow QC passed items ('tested_ok') and Dead Boxes ('flash', 'software_issue')
        if (!in_array($box->stb_status, ['tested_ok', 'flash', 'software_issue'])) {
            $statusText = $box->status_label;
            return response()->json([
                'success' => true,
                'found' => true,
                'valid' => false,
                'message' => "Cannot checkout STB {$barcode}! Current status is '{$statusText}'. Only QC Passed ('Tested OK') or Dead Boxes ('Flash' / 'Software Issue') can be delivered."
            ]);
        }

        // Rule 2: Validate STB Box with the selected Operator
        if ($operatorId && $box->operator_id != $operatorId) {
            $selectedOp = Operator::find($operatorId);
            $selectedOpName = $selectedOp ? $selectedOp->operator_name : 'Selected Operator';
            $boxOpName = $box->operator ? $box->operator->operator_name : 'Unassigned';

            return response()->json([
                'success' => true,
                'found' => true,
                'valid' => false,
                'message' => "Box {$barcode} is assigned to '{$boxOpName}', but '{$selectedOpName}' is selected in the voucher header! STB can only be delivered to its assigned Cable Operator."
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => true,
            'valid' => true,
            'box' => [
                'id' => $box->id,
                'barcode_number' => $box->barcode_number,
                'box_name' => $box->box_name,
                'operator_id' => $box->operator_id,
                'operator_name' => $box->operator ? $box->operator->operator_name : 'Unassigned',
                'stb_status' => $box->stb_status,
                'status_label' => $box->status_label,
                'status_badge_class' => $box->status_badge_class,
            ]
        ]);
    }

    public function storeVoucher(Request $request)
    {
        $validated = $request->validate([
            'voucher_number' => 'required|string|max:100|unique:checkout_vouchers,voucher_number',
            'checkout_date' => 'required|date',
            'operator_id' => 'required|exists:operators,id',
            'remarks' => 'nullable|string',
            'box_ids' => 'required|array|min:1',
            'box_ids.*' => 'required|exists:set_top_boxes,id',
        ]);

        DB::beginTransaction();
        try {
            $operator = Operator::findOrFail($validated['operator_id']);

            $voucher = CheckoutVoucher::create([
                'voucher_number' => $validated['voucher_number'],
                'checkout_date' => $validated['checkout_date'],
                'operator_id' => $validated['operator_id'],
                'total_boxes' => count($validated['box_ids']),
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['box_ids'] as $boxId) {
                $box = SetTopBox::findOrFail($boxId);
                
                // Validate status (tested_ok, flash, software_issue) and operator match again on server side
                if (!in_array($box->stb_status, ['tested_ok', 'flash', 'software_issue'])) {
                    throw new Exception("Box {$box->barcode_number} is not in 'Tested OK', 'Flash', or 'Software Issue' status.");
                }
                if ($box->operator_id != $operator->id) {
                    throw new Exception("Box {$box->barcode_number} does not belong to {$operator->operator_name}.");
                }

                $checkoutItemStatus = in_array($box->stb_status, ['tested_ok', 'flash', 'software_issue']) ? $box->stb_status : 'tested_ok';

                // Preserve STB Box status (do not overwrite to 'delivered')
                $box->operator_id = $operator->id;
                $box->save();

                // Create Voucher Item detail with exact box status ('tested_ok' or 'flash')
                $itemData = [
                    'checkout_voucher_id' => $voucher->id,
                    'set_top_box_id' => $box->id,
                    'barcode_number' => $box->barcode_number,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('checkout_voucher_items', 'stb_status')) {
                    $itemData['stb_status'] = $checkoutItemStatus;
                }
                CheckoutVoucherItem::create($itemData);
            }

            DB::commit();

            ActivityLogService::log('STB_CHECKOUT_VOUCHER', "Created STB Delivery Voucher {$voucher->voucher_number} for Operator '{$operator->operator_name}' with {$voucher->total_boxes} boxes");

            return redirect()->route('stb-checkout.index')
                ->with('success', "Delivery Voucher {$voucher->voucher_number} saved successfully! {$voucher->total_boxes} STBs delivered to {$operator->operator_name}.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save delivery voucher: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $voucher = CheckoutVoucher::with(['operator', 'creator', 'items.setTopBox.boxModel'])->findOrFail($id);
        return view('transactions.show_checkout_voucher', compact('voucher'));
    }

    public function edit($id)
    {
        $voucher = CheckoutVoucher::with(['operator', 'items.setTopBox.boxModel'])->findOrFail($id);
        $operators = Operator::where('status', 'active')->orderBy('operator_name')->get();
        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();

        return view('transactions.edit_checkout_voucher', compact('voucher', 'operators', 'boxModels'));
    }

    public function update(Request $request, $id)
    {
        $voucher = CheckoutVoucher::findOrFail($id);

        $validated = $request->validate([
            'checkout_date' => 'required|date',
            'operator_id' => 'required|exists:operators,id',
            'remarks' => 'nullable|string',
            'box_ids' => 'required|array|min:1',
            'box_ids.*' => 'required|exists:set_top_boxes,id',
        ]);

        DB::beginTransaction();
        try {
            $operator = Operator::findOrFail($validated['operator_id']);

            // Update Voucher header
            $voucher->update([
                'checkout_date' => $validated['checkout_date'],
                'operator_id' => $validated['operator_id'],
                'total_boxes' => count($validated['box_ids']),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Sync items
            CheckoutVoucherItem::where('checkout_voucher_id', $voucher->id)->delete();

            foreach ($validated['box_ids'] as $boxId) {
                $box = SetTopBox::findOrFail($boxId);
                $checkoutItemStatus = in_array($box->stb_status, ['tested_ok', 'flash']) ? $box->stb_status : 'tested_ok';

                $box->operator_id = $operator->id;
                $box->save();

                $itemData = [
                    'checkout_voucher_id' => $voucher->id,
                    'set_top_box_id' => $box->id,
                    'barcode_number' => $box->barcode_number,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('checkout_voucher_items', 'stb_status')) {
                    $itemData['stb_status'] = $checkoutItemStatus;
                }
                CheckoutVoucherItem::create($itemData);
            }

            DB::commit();

            ActivityLogService::log('UPDATE_STB_CHECKOUT_VOUCHER', "Updated STB Delivery Voucher {$voucher->voucher_number}");

            return redirect()->route('stb-checkout.index')
                ->with('success', "Delivery Voucher {$voucher->voucher_number} updated successfully!");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update delivery voucher: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $voucher = CheckoutVoucher::with('items')->findOrFail($id);

        DB::transaction(function () use ($voucher) {
            CheckoutVoucherItem::where('checkout_voucher_id', $voucher->id)->delete();
            $voucher->delete();
            ActivityLogService::log('DELETE_STB_CHECKOUT_VOUCHER', "Deleted Delivery Voucher {$voucher->voucher_number}");
        });

        return redirect()->route('stb-checkout.index')
            ->with('success', "Delivery Voucher deleted successfully.");
    }

    public function printVoucher($id)
    {
        $voucher = CheckoutVoucher::with(['operator', 'creator', 'items.setTopBox.boxModel'])->findOrFail($id);
        return view('transactions.print_checkout_voucher', compact('voucher'));
    }

    private function generateVoucherNumber()
    {
        $datePrefix = 'OUT-' . date('Ymd') . '-';
        $latest = CheckoutVoucher::where('voucher_number', 'like', $datePrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $num = (int) substr($latest->voucher_number, -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $datePrefix . $next;
    }
}
