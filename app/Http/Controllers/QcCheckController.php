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

        // Recent QC Inspections Log
        $qcLogs = QcCheck::with(['setTopBox.operator', 'inspector', 'serviceTransaction'])
            ->orderBy('created_at', 'desc')
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

        // Record QC check entry
        $qc = QcCheck::create([
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
        ActivityLogService::log('QC_INSPECTION', "QC Inspector performed check on Box '{$stb->barcode_number}'. Result: {$statusLabel}");

        return redirect()->route('qc.index')->with('success', "QC Check recorded for STB {$stb->barcode_number}! New Status: {$statusLabel}");
    }
}
