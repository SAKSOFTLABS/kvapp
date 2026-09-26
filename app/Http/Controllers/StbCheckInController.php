<?php

namespace App\Http\Controllers;

use App\Models\SetTopBox;
use App\Models\Operator;
use App\Models\BoxModel;
use App\Models\CheckinVoucher;
use App\Models\CheckinVoucherItem;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class StbCheckInController extends Controller
{
    public function index(Request $request)
    {
        $operators = Operator::where('status', 'active')->orderBy('operator_name')->get();
        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();

        // Auto-generate next Voucher Code e.g. CHK-YYYYMMDD-0001
        $nextVoucherNumber = $this->generateVoucherNumber();

        // Recent Vouchers Query
        $query = CheckinVoucher::with(['operator', 'creator', 'items.setTopBox']);

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

        return view('transactions.stb_checkin', compact('operators', 'boxModels', 'nextVoucherNumber', 'vouchers'));
    }

    public function lookupBarcode(Request $request)
    {
        $barcode = trim($request->get('barcode', ''));

        if (empty($barcode)) {
            return response()->json(['success' => false, 'message' => 'Barcode cannot be empty']);
        }

        $box = SetTopBox::with(['boxModel', 'operator'])->where('barcode_number', $barcode)->first();

        if ($box) {
            $checkinStatus = $this->checkReserviceStatus($box->id, now()->toDateString());
            $isReservice = ($checkinStatus === 'reservice');

            $statusLabel = $isReservice ? 'Reservice (< 30 Days)' : $box->status_label;
            $badgeClass = $isReservice ? 'bg-danger text-white border border-danger fw-bold' : $box->status_badge_class;

            return response()->json([
                'success' => true,
                'found' => true,
                'box' => [
                    'id' => $box->id,
                    'barcode_number' => $box->barcode_number,
                    'box_name' => $box->box_name,
                    'operator_id' => $box->operator_id,
                    'operator_name' => $box->operator ? $box->operator->operator_name : 'Unassigned',
                    'stb_status' => $isReservice ? 'reservice' : $box->stb_status,
                    'status_label' => $statusLabel,
                    'status_badge_class' => $badgeClass,
                    'is_delivered' => $box->isDelivered(),
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'found' => false,
            'barcode' => $barcode,
            'message' => "Barcode {$barcode} is not found in database."
        ]);
    }

    public function quickRegister(Request $request)
    {
        $validated = $request->validate([
            'box_model_id' => 'required|exists:box_models,id',
            'barcode_number' => 'required|string|max:100|unique:set_top_boxes,barcode_number',
            'remarks' => 'nullable|string',
        ]);

        $boxModel = BoxModel::find($validated['box_model_id']);

        $box = SetTopBox::create([
            'box_model_id' => $validated['box_model_id'],
            'box_name' => $boxModel ? $boxModel->model_name : 'Unknown Model',
            'barcode_number' => $validated['barcode_number'],
            'stb_status' => 'complaint', // Default for Check-in
            'remarks' => $validated['remarks'] ?? null,
            'status' => 'active',
        ]);

        ActivityLogService::log('QUICK_REGISTER_STB', "Registered new STB Box {$box->barcode_number} ({$box->box_name}) during check-in intake");

        return response()->json([
            'success' => true,
            'message' => "STB {$box->barcode_number} registered successfully!",
            'box' => [
                'id' => $box->id,
                'barcode_number' => $box->barcode_number,
                'box_name' => $box->box_name,
                'stb_status' => $box->stb_status,
                'status_label' => $box->status_label,
                'status_badge_class' => $box->status_badge_class,
            ]
        ]);
    }

    public function storeVoucher(Request $request)
    {
        $validated = $request->validate([
            'voucher_number' => 'required|string|max:100|unique:checkin_vouchers,voucher_number',
            'checkin_date' => 'required|date',
            'operator_id' => 'required|exists:operators,id',
            'remarks' => 'nullable|string',
            'box_ids' => 'required|array|min:1',
            'box_ids.*' => 'required|exists:set_top_boxes,id',
        ]);

        DB::beginTransaction();
        try {
            $operator = Operator::findOrFail($validated['operator_id']);

            $voucher = CheckinVoucher::create([
                'voucher_number' => $validated['voucher_number'],
                'checkin_date' => $validated['checkin_date'],
                'operator_id' => $validated['operator_id'],
                'total_boxes' => count($validated['box_ids']),
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['box_ids'] as $boxId) {
                $box = SetTopBox::findOrFail($boxId);
                
                // Determine intake status (reservice if checked out within 30 days, else complaint)
                $stbStatus = $this->checkReserviceStatus($box->id, $validated['checkin_date']);

                $box->operator_id = $operator->id;
                $box->stb_status = $stbStatus;
                $box->save();

                // Create Voucher Item detail with exact intake status
                $itemData = [
                    'checkin_voucher_id' => $voucher->id,
                    'set_top_box_id' => $box->id,
                    'barcode_number' => $box->barcode_number,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('checkin_voucher_items', 'stb_status')) {
                    $itemData['stb_status'] = $stbStatus;
                }
                CheckinVoucherItem::create($itemData);
            }

            DB::commit();

            ActivityLogService::log('STB_CHECKIN_VOUCHER', "Created STB Check-In Voucher {$voucher->voucher_number} for Operator '{$operator->operator_name}' with {$voucher->total_boxes} boxes");

            return redirect()->route('stb-checkin.index')
                ->with('success', "Check-In Voucher {$voucher->voucher_number} saved successfully! {$voucher->total_boxes} boxes checked in under {$operator->operator_name}.");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to save check-in voucher: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $voucher = CheckinVoucher::with(['operator', 'creator', 'items.setTopBox.boxModel'])->findOrFail($id);
        return view('transactions.show_checkin_voucher', compact('voucher'));
    }

    public function edit($id)
    {
        $voucher = CheckinVoucher::with(['operator', 'items.setTopBox.boxModel'])->findOrFail($id);
        $operators = Operator::where('status', 'active')->orderBy('operator_name')->get();
        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();

        return view('transactions.edit_checkin_voucher', compact('voucher', 'operators', 'boxModels'));
    }

    public function update(Request $request, $id)
    {
        $voucher = CheckinVoucher::findOrFail($id);

        $validated = $request->validate([
            'checkin_date' => 'required|date',
            'operator_id' => 'required|exists:operators,id',
            'remarks' => 'nullable|string',
            'box_ids' => 'required|array|min:1',
            'box_ids.*' => 'required|exists:set_top_boxes,id',
        ]);

        DB::beginTransaction();
        try {
            $operator = Operator::findOrFail($validated['operator_id']);

            $voucher->update([
                'checkin_date' => $validated['checkin_date'],
                'operator_id' => $validated['operator_id'],
                'total_boxes' => count($validated['box_ids']),
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // Sync Voucher Items
            CheckinVoucherItem::where('checkin_voucher_id', $voucher->id)->delete();

            foreach ($validated['box_ids'] as $boxId) {
                $box = SetTopBox::findOrFail($boxId);
                $stbStatus = $this->checkReserviceStatus($box->id, $validated['checkin_date']);

                $box->operator_id = $operator->id;
                $box->stb_status = $stbStatus;
                $box->save();

                $itemData = [
                    'checkin_voucher_id' => $voucher->id,
                    'set_top_box_id' => $box->id,
                    'barcode_number' => $box->barcode_number,
                ];
                if (\Illuminate\Support\Facades\Schema::hasColumn('checkin_voucher_items', 'stb_status')) {
                    $itemData['stb_status'] = $stbStatus;
                }
                CheckinVoucherItem::create($itemData);
            }

            DB::commit();

            ActivityLogService::log('UPDATE_STB_CHECKIN_VOUCHER', "Updated STB Check-In Voucher {$voucher->voucher_number}");

            return redirect()->route('stb-checkin.index')
                ->with('success', "Check-In Voucher {$voucher->voucher_number} updated successfully!");
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update check-in voucher: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $voucher = CheckinVoucher::findOrFail($id);

        DB::transaction(function () use ($voucher) {
            CheckinVoucherItem::where('checkin_voucher_id', $voucher->id)->delete();
            $voucher->delete();
            ActivityLogService::log('DELETE_STB_CHECKIN_VOUCHER', "Deleted Check-In Voucher {$voucher->voucher_number}");
        });

        return redirect()->route('stb-checkin.index')
            ->with('success', "Check-In Voucher deleted successfully.");
    }

    public function printVoucher($id)
    {
        $voucher = CheckinVoucher::with(['operator', 'creator', 'items.setTopBox.boxModel'])->findOrFail($id);
        return view('transactions.print_checkin_voucher', compact('voucher'));
    }

    private function checkReserviceStatus($boxId, $checkinDate)
    {
        $latestCheckoutDate = DB::table('checkout_voucher_items')
            ->join('checkout_vouchers', 'checkout_voucher_items.checkout_voucher_id', '=', 'checkout_vouchers.id')
            ->where('checkout_voucher_items.set_top_box_id', $boxId)
            ->max('checkout_vouchers.checkout_date');

        if ($latestCheckoutDate) {
            $diffDays = \Carbon\Carbon::parse($latestCheckoutDate)->diffInDays(\Carbon\Carbon::parse($checkinDate), false);
            if ($diffDays >= 0 && $diffDays <= 30) {
                return 'reservice';
            }
        }

        return 'complaint';
    }

    private function generateVoucherNumber()
    {
        $datePrefix = 'CHK-' . date('Ymd') . '-';
        $latest = CheckinVoucher::where('voucher_number', 'like', $datePrefix . '%')
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
