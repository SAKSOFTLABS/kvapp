<?php

namespace App\Http\Controllers;

use App\Models\SetTopBox;
use App\Models\QcCheck;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QcCheckController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->isFrontOfficeOnly()) {
            return redirect()->route('dashboard')->with('error', 'Access denied: QC Testing module is restricted for Front Office staff.');
        }

        // Pending QC Boxes (status = service_done)
        $pendingQuery = SetTopBox::with(['boxModel', 'operator', 'serviceHistory.technician'])
            ->where('stb_status', 'service_done');

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('barcode_number', 'like', "%{$search}%")
                  ->orWhere('box_name', 'like', "%{$search}%")
                  ->orWhereHas('operator', function ($oq) use ($search) {
                      $oq->where('operator_name', 'like', "%{$search}%");
                  });
            });
        }

        $pendingBoxes = $pendingQuery->orderBy('updated_at', 'asc')->paginate(10, ['*'], 'pending_page')->withQueryString();

        $qcLogsQuery = QcCheck::with(['setTopBox.operator', 'inspector', 'serviceTransaction']);

        if ($request->filled('search')) {
            $search = $request->search;
            $qcLogsQuery->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('setTopBox', function ($sq) use ($search) {
                      $sq->where('barcode_number', 'like', "%{$search}%")
                         ->orWhere('box_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('inspector', function ($iq) use ($search) {
                      $iq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $qcLogs = $qcLogsQuery->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'log_page')
            ->withQueryString();

        return view('qc.index', compact('pendingBoxes', 'qcLogs'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->isFrontOfficeOnly()) {
            return back()->with('error', 'Access denied: QC Testing module is restricted for Front Office staff.');
        }

        $validated = $request->validate([
            'set_top_box_id' => 'required|exists:set_top_boxes,id',
            'service_transaction_id' => 'nullable|exists:service_transactions,id',
            'qc_status' => 'required|in:tested_ok,complaint,flash',
            'remarks' => 'nullable|string',
        ]);

        $stb = SetTopBox::findOrFail($validated['set_top_box_id']);
        $voucherNumber = $this->generateVoucherNumber();

        // Record QC check entry
        $qc = QcCheck::create([
            'voucher_number' => $voucherNumber,
            'set_top_box_id' => $stb->id,
            'service_transaction_id' => $validated['service_transaction_id'] ?? null,
            'qc_user_id' => Auth::id(),
            'qc_status' => $validated['qc_status'],
            'qc_date' => now()->toDateString(),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        // Update STB status based on QC result
        $stb->stb_status = $validated['qc_status'];
        $stb->save();

        $statusLabel = $stb->status_label;
        ActivityLogService::log('QC_INSPECTION', "QC Inspector performed check on Box '{$stb->barcode_number}' (Voucher #{$qc->voucher_number}). Result: {$statusLabel}");

        return redirect()->route('qc.index')->with('success', "QC Voucher {$qc->voucher_number} recorded for STB {$stb->barcode_number}! New Status: {$statusLabel}");
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->isFrontOfficeOnly()) {
            return back()->with('error', 'Access denied: QC Testing module is restricted for Front Office staff.');
        }

        $qc = QcCheck::findOrFail($id);

        $validated = $request->validate([
            'qc_status' => 'required|in:tested_ok,complaint,flash',
            'remarks' => 'nullable|string',
        ]);

        $qc->update([
            'qc_status' => $validated['qc_status'],
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $stb = SetTopBox::find($qc->set_top_box_id);
        if ($stb) {
            $stb->stb_status = $validated['qc_status'];
            $stb->save();
        }

        $statusLabel = $stb ? $stb->status_label : ucfirst(str_replace('_', ' ', $validated['qc_status']));
        ActivityLogService::log('UPDATE_QC_INSPECTION', "Updated QC Voucher #{$qc->voucher_number} for Box '" . ($stb->barcode_number ?? 'N/A') . "'. New Result: {$statusLabel}");

        return redirect()->route('qc.index')->with('success', "QC Voucher {$qc->voucher_number} updated successfully! New Status: {$statusLabel}");
    }

    public function destroy($id)
    {
        if (Auth::user()->isFrontOfficeOnly()) {
            return back()->with('error', 'Access denied: QC Testing module is restricted for Front Office staff.');
        }

        $qc = QcCheck::findOrFail($id);
        $stb = SetTopBox::find($qc->set_top_box_id);
        $voucherNum = $qc->voucher_number;

        $qc->delete();

        if ($stb) {
            // Revert STB status to 'service_done' so it returns to pending QC list
            $stb->stb_status = 'service_done';
            $stb->save();
        }

        ActivityLogService::log('DELETE_QC_INSPECTION', "Deleted QC Voucher #{$voucherNum}");

        return redirect()->route('qc.index')->with('success', "QC Voucher {$voucherNum} deleted successfully! Box has been returned to Pending QC list.");
    }

    private function generateVoucherNumber()
    {
        $datePrefix = 'QC-' . date('Ymd') . '-';
        $latest = QcCheck::where('voucher_number', 'like', $datePrefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest && !empty($latest->voucher_number)) {
            $num = (int) substr($latest->voucher_number, -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $countToday = QcCheck::whereDate('created_at', now()->toDateString())->count();
            $next = str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);
        }

        return $datePrefix . $next;
    }
}
