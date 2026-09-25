<?php

namespace App\Http\Controllers;

use App\Models\SetTopBox;
use App\Models\BoxModel;
use App\Models\Operator;
use App\Models\ServiceTransaction;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetTopBoxController extends Controller
{
    public function index(Request $request)
    {
        $query = SetTopBox::with(['boxModel', 'operator'])->withCount('serviceHistory');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('box_name', 'like', "%{$search}%")
                  ->orWhere('barcode_number', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('boxModel', function ($mq) use ($search) {
                      $mq->where('model_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('operator', function ($oq) use ($search) {
                      $oq->where('operator_name', 'like', "%{$search}%")
                         ->orWhere('operator_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stb_status')) {
            $query->where('stb_status', $request->stb_status);
        }

        if ($request->filled('operator_id')) {
            $query->where('operator_id', $request->operator_id);
        }

        $boxes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();
        $operators = Operator::where('status', 'active')->orderBy('operator_name')->get();

        return view('master.set_top_boxes.index', compact('boxes', 'boxModels', 'operators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'box_model_id' => 'required|exists:box_models,id',
            'operator_id' => 'nullable|exists:operators,id',
            'barcode_number' => 'required|string|max:100|unique:set_top_boxes,barcode_number',
            'stb_status' => 'required|in:complaint,service_done,tested_ok,flash,send_to_pk',
            'remarks' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Restrict Send to PK status to Admin only
        if ($validated['stb_status'] === 'send_to_pk' && !Auth::user()->isAdmin()) {
            return back()->withInput()->with('error', "Only Administrator role is authorized to set status 'Send to PK'.");
        }

        $boxModel = BoxModel::find($validated['box_model_id']);
        $validated['box_name'] = $boxModel ? $boxModel->model_name : 'Unknown Model';

        $box = SetTopBox::create($validated);

        ActivityLogService::log('CREATE_STB', "Registered Set Top Box {$box->box_name} (Barcode: {$box->barcode_number}) under Operator ID #" . ($box->operator_id ?? 'None'));

        return redirect()->route('set-top-boxes.index')->with('success', 'Set Top Box registered successfully.');
    }

    public function update(Request $request, $id)
    {
        $box = SetTopBox::findOrFail($id);

        $validated = $request->validate([
            'box_model_id' => 'required|exists:box_models,id',
            'operator_id' => 'nullable|exists:operators,id',
            'barcode_number' => 'required|string|max:100|unique:set_top_boxes,barcode_number,' . $id,
            'stb_status' => 'required|in:complaint,service_done,tested_ok,flash,send_to_pk',
            'remarks' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Restrict Send to PK status to Admin only
        if ($validated['stb_status'] === 'send_to_pk' && !Auth::user()->isAdmin()) {
            return back()->withInput()->with('error', "Only Administrator role is authorized to set status 'Send to PK'.");
        }

        $boxModel = BoxModel::find($validated['box_model_id']);
        $validated['box_name'] = $boxModel ? $boxModel->model_name : $box->box_name;

        $box->update($validated);

        ActivityLogService::log('UPDATE_STB', "Updated Set Top Box {$box->box_name} (Barcode: {$box->barcode_number})");

        return redirect()->route('set-top-boxes.index')->with('success', 'Set Top Box updated successfully.');
    }

    public function destroy($id)
    {
        $box = SetTopBox::findOrFail($id);

        if ($box->serviceHistory()->count() > 0) {
            return back()->with('error', 'Cannot delete Set Top Box because service history records exist for it.');
        }

        $box->delete();
        ActivityLogService::log('DELETE_STB', "Deleted Set Top Box {$box->box_name}");

        return redirect()->route('set-top-boxes.index')->with('success', 'Set Top Box deleted successfully.');
    }

    public function history($id)
    {
        return redirect()->route('reports.stb-history', ['stb_id' => $id]);
    }

    public function printReport($id)
    {
        $box = SetTopBox::with(['operator', 'qcHistory.inspector', 'serviceHistory' => function ($q) {
            $q->with(['technician', 'items.item', 'creator'])->orderBy('service_date', 'desc');
        }])->findOrFail($id);

        $totalServiceCost = $box->serviceHistory->sum('total_cost');

        return view('master.set_top_boxes.print_report', compact('box', 'totalServiceCost'));
    }

    public function jsonSearchBarcode(Request $request)
    {
        $barcode = trim($request->get('barcode', ''));

        if (empty($barcode)) {
            return response()->json(['success' => false, 'message' => 'Barcode cannot be empty']);
        }

        $box = SetTopBox::with('operator')->where('barcode_number', $barcode)
            ->orWhere('box_name', 'like', "%{$barcode}%")
            ->first();

        if (!$box) {
            return response()->json(['success' => false, 'message' => 'No Set Top Box found matching barcode: ' . $barcode]);
        }

        return response()->json([
            'success' => true,
            'box' => [
                'id' => $box->id,
                'box_name' => $box->box_name,
                'barcode_number' => $box->barcode_number,
                'stb_status' => $box->stb_status,
                'status_label' => $box->status_label,
                'status_badge_class' => $box->status_badge_class,
                'operator_name' => $box->operator ? $box->operator->operator_name : 'Unassigned',
                'remarks' => $box->remarks,
            ]
        ]);
    }
}
