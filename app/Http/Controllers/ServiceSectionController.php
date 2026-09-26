<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\SetTopBox;
use App\Models\StaffStock;
use App\Models\ServiceTransaction;
use App\Services\StockManagementService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class ServiceSectionController extends Controller
{
    protected $stockService;

    public function __construct(StockManagementService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isFrontOfficeOnly()) {
            return redirect()->route('dashboard')->with('error', 'Access denied: Service Section module is restricted for Front Office staff.');
        }

        // If staff, restrict staff list to self
        if ($user->isStaff() && $user->staff_id) {
            $staffList = Staff::where('id', $user->staff_id)->where('status', 'active')->get();
        } else {
            $staffList = Staff::where('status', 'active')->orderBy('name')->get();
        }

        $boxes = SetTopBox::with('operator')->where('status', 'active')->orderBy('box_name')->get();
        $nextServiceCode = 'SRV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

        $query = ServiceTransaction::with(['setTopBox.operator', 'technician', 'items.item', 'creator']);

        if ($user->isStaff() && $user->staff_id) {
            $query->where('staff_id', $user->staff_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('service_code', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('setTopBox', function ($bq) use ($search) {
                      $bq->where('box_name', 'like', "%{$search}%")
                         ->orWhere('barcode_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('technician', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('transactions.service', compact('staffList', 'boxes', 'services', 'nextServiceCode'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->isFrontOfficeOnly()) {
            return back()->with('error', 'Access denied: Service Section module is restricted for Front Office staff.');
        }

        $validated = $request->validate([
            'service_date' => 'required|date',
            'set_top_box_id' => 'required|exists:set_top_boxes,id',
            'staff_id' => 'required|exists:staff,id',
            'action_type' => 'required|in:complete,flash',
            'remarks' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.quantity' => 'nullable|numeric|min:0.01',
        ]);

        try {
            $box = SetTopBox::findOrFail($validated['set_top_box_id']);
            if ($box->isDelivered()) {
                return back()->withInput()->with('error', 'BOX IS NOT CHECKED IN FROM FRONT OFFICE');
            }

            $service = $this->stockService->recordService($validated, Auth::id());
            $tech = Staff::find($validated['staff_id']);

            $actionText = ($validated['action_type'] === 'flash') ? 'FLASHED (Dead Box)' : 'SERVICED DONE';
            ActivityLogService::log('RECORD_SERVICE', "{$actionText} STB Service {$service->service_code} for Box '{$box->barcode_number}' by Tech {$tech->name}");

            $statusMsg = ($validated['action_type'] === 'flash') 
                ? "Service recorded as FLASH (Dead Box)! Box barcode: {$box->barcode_number}"
                : "Service recorded successfully! Box {$box->barcode_number} is set to Service Done (Awaiting QC).";

            return redirect()->route('service.index')->with('success', $statusMsg);
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $service = ServiceTransaction::with(['setTopBox.operator', 'technician', 'items.item', 'creator'])->findOrFail($id);
        return view('transactions.show_service', compact('service'));
    }

    public function destroy($id)
    {
        $service = ServiceTransaction::findOrFail($id);
        $service->delete();

        ActivityLogService::log('DELETE_SERVICE', "Deleted Service Entry {$service->service_code}");
        return redirect()->route('service.index')->with('success', 'Service entry deleted successfully.');
    }

    public function getTechnicianStock($staffId)
    {
        $stocks = StaffStock::with('item')
            ->where('staff_id', $staffId)
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($s) {
                return [
                    'item_id' => $s->item_id,
                    'item_name' => $s->item->item_name,
                    'item_code' => $s->item->item_code,
                    'available_qty' => (float)$s->quantity,
                    'sales_price' => (float)$s->item->sales_price,
                ];
            });

        return response()->json([
            'success' => true,
            'staff_id' => $staffId,
            'stocks' => $stocks
        ]);
    }
}
