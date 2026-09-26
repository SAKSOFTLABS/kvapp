<?php

namespace App\Http\Controllers;

use App\Models\SetTopBox;
use App\Models\BoxModel;
use App\Models\Operator;
use Illuminate\Http\Request;

class StbBoxReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'total_boxes');
        $allowedTabs = ['total_boxes', 'model_wise', 'operator_wise', 'pud_wise'];
        if (!in_array($tab, $allowedTabs)) {
            $tab = 'total_boxes';
        }

        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();
        $operators = Operator::where('status', 'active')->orderBy('operator_name')->get();

        // Overall Summary Statistics
        $totalStbs = SetTopBox::count();
        $totalSendToPud = SetTopBox::where('stb_status', 'send_to_pud')->count();
        $totalComplaint = SetTopBox::whereIn('stb_status', ['complaint', 'reservice'])->count();
        $totalServiceDone = SetTopBox::where('stb_status', 'service_done')->count();
        $totalTestedOk = SetTopBox::where('stb_status', 'tested_ok')->count();
        $totalDeadBoxes = SetTopBox::whereIn('stb_status', ['flash', 'software_issue'])->count();

        // Count Delivered Boxes
        $allBoxes = SetTopBox::all();
        $totalDelivered = $allBoxes->filter(fn($b) => $b->isDelivered())->count();

        $data = null;
        $summaryList = [];

        switch ($tab) {
            case 'total_boxes':
                $query = SetTopBox::with(['boxModel', 'operator']);

                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(function ($q) use ($search) {
                        $q->where('barcode_number', 'like', "%{$search}%")
                          ->orWhere('box_name', 'like', "%{$search}%")
                          ->orWhereHas('boxModel', function ($mq) use ($search) {
                              $mq->where('model_name', 'like', "%{$search}%");
                          })
                          ->orWhereHas('operator', function ($oq) use ($search) {
                              $oq->where('operator_name', 'like', "%{$search}%");
                          });
                    });
                }

                if ($request->filled('box_model_id')) {
                    $query->where('box_model_id', $request->box_model_id);
                }

                if ($request->filled('operator_id')) {
                    $query->where('operator_id', $request->operator_id);
                }

                if ($request->filled('stb_status')) {
                    $query->where('stb_status', $request->stb_status);
                }

                $data = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
                break;

            case 'model_wise':
                $models = BoxModel::withCount('setTopBoxes')->orderBy('model_name')->get();
                $modelData = [];

                foreach ($models as $m) {
                    $boxesInModel = SetTopBox::where('box_model_id', $m->id)->get();
                    $mTotal = $boxesInModel->count();
                    $mComplaint = $boxesInModel->whereIn('stb_status', ['complaint', 'reservice'])->count();
                    $mServiceDone = $boxesInModel->where('stb_status', 'service_done')->count();
                    $mTestedOk = $boxesInModel->where('stb_status', 'tested_ok')->count();
                    $mPud = $boxesInModel->where('stb_status', 'send_to_pud')->count();
                    $mDead = $boxesInModel->whereIn('stb_status', ['flash', 'software_issue'])->count();
                    $mDelivered = $boxesInModel->filter(fn($b) => $b->isDelivered())->count();

                    $modelData[] = [
                        'model_id' => $m->id,
                        'model_name' => $m->model_name,
                        'total' => $mTotal,
                        'complaint' => $mComplaint,
                        'service_done' => $mServiceDone,
                        'tested_ok' => $mTestedOk,
                        'delivered' => $mDelivered,
                        'send_to_pud' => $mPud,
                        'dead' => $mDead,
                    ];
                }
                $summaryList = $modelData;
                break;

            case 'operator_wise':
                $ops = Operator::orderBy('operator_name')->get();
                $opData = [];

                foreach ($ops as $op) {
                    $boxesInOp = SetTopBox::where('operator_id', $op->id)->get();
                    $opTotal = $boxesInOp->count();
                    $opComplaint = $boxesInOp->whereIn('stb_status', ['complaint', 'reservice'])->count();
                    $opServiceDone = $boxesInOp->where('stb_status', 'service_done')->count();
                    $opTestedOk = $boxesInOp->where('stb_status', 'tested_ok')->count();
                    $opPud = $boxesInOp->where('stb_status', 'send_to_pud')->count();
                    $opDead = $boxesInOp->whereIn('stb_status', ['flash', 'software_issue'])->count();
                    $opDelivered = $boxesInOp->filter(fn($b) => $b->isDelivered())->count();

                    $opData[] = [
                        'operator_id' => $op->id,
                        'operator_name' => $op->operator_name,
                        'operator_code' => $op->operator_code,
                        'total' => $opTotal,
                        'complaint' => $opComplaint,
                        'service_done' => $opServiceDone,
                        'tested_ok' => $opTestedOk,
                        'delivered' => $opDelivered,
                        'send_to_pud' => $opPud,
                        'dead' => $opDead,
                    ];
                }

                $unassignedBoxes = SetTopBox::whereNull('operator_id')->get();
                if ($unassignedBoxes->count() > 0) {
                    $opData[] = [
                        'operator_id' => null,
                        'operator_name' => 'Unassigned',
                        'operator_code' => 'N/A',
                        'total' => $unassignedBoxes->count(),
                        'complaint' => $unassignedBoxes->whereIn('stb_status', ['complaint', 'reservice'])->count(),
                        'service_done' => $unassignedBoxes->where('stb_status', 'service_done')->count(),
                        'tested_ok' => $unassignedBoxes->where('stb_status', 'tested_ok')->count(),
                        'delivered' => 0,
                        'send_to_pud' => $unassignedBoxes->where('stb_status', 'send_to_pud')->count(),
                        'dead' => $unassignedBoxes->whereIn('stb_status', ['flash', 'software_issue'])->count(),
                    ];
                }

                $summaryList = $opData;
                break;

            case 'pud_wise':
                $query = SetTopBox::with(['boxModel', 'operator', 'serviceHistory.technician'])
                    ->where('stb_status', 'send_to_pud');

                if ($request->filled('search')) {
                    $search = trim($request->search);
                    $query->where(function ($q) use ($search) {
                        $q->where('barcode_number', 'like', "%{$search}%")
                          ->orWhere('box_name', 'like', "%{$search}%")
                          ->orWhereHas('operator', function ($oq) use ($search) {
                              $oq->where('operator_name', 'like', "%{$search}%");
                          });
                    });
                }

                if ($request->filled('box_model_id')) {
                    $query->where('box_model_id', $request->box_model_id);
                }

                if ($request->filled('operator_id')) {
                    $query->where('operator_id', $request->operator_id);
                }

                $data = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
                break;
        }

        return view('reports.stb_box_report', compact(
            'tab',
            'boxModels',
            'operators',
            'totalStbs',
            'totalSendToPud',
            'totalComplaint',
            'totalServiceDone',
            'totalTestedOk',
            'totalDelivered',
            'totalDeadBoxes',
            'data',
            'summaryList'
        ));
    }

    public function exportCsv(Request $request)
    {
        $tab = $request->get('tab', 'total_boxes');
        $filename = "stb_box_report_{$tab}_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($tab) {
            $file = fopen('php://output', 'w');

            if ($tab === 'total_boxes') {
                fputcsv($file, ['Barcode Number', 'Box Model', 'Cable Operator', 'Intake Status', 'Delivered Out Status', 'Remarks']);
                $boxes = SetTopBox::with(['boxModel', 'operator'])->get();
                foreach ($boxes as $b) {
                    fputcsv($file, [
                        $b->barcode_number,
                        $b->boxModel->model_name ?? $b->box_name,
                        $b->operator->operator_name ?? 'Unassigned',
                        $b->stb_status,
                        $b->isDelivered() ? 'Delivered Out' : 'In Store/Workshop',
                        $b->remarks ?? ''
                    ]);
                }
            } elseif ($tab === 'model_wise') {
                fputcsv($file, ['Box Model Name', 'Total STBs', 'Complaint Qty', 'Service Done Qty', 'QC Tested OK Qty', 'Delivered Qty', 'Send to PUD Qty', 'Dead Boxes Qty']);
                $models = BoxModel::orderBy('model_name')->get();
                foreach ($models as $m) {
                    $boxesInModel = SetTopBox::where('box_model_id', $m->id)->get();
                    fputcsv($file, [
                        $m->model_name,
                        $boxesInModel->count(),
                        $boxesInModel->whereIn('stb_status', ['complaint', 'reservice'])->count(),
                        $boxesInModel->where('stb_status', 'service_done')->count(),
                        $boxesInModel->where('stb_status', 'tested_ok')->count(),
                        $boxesInModel->filter(fn($b) => $b->isDelivered())->count(),
                        $boxesInModel->where('stb_status', 'send_to_pud')->count(),
                        $boxesInModel->whereIn('stb_status', ['flash', 'software_issue'])->count(),
                    ]);
                }
            } elseif ($tab === 'operator_wise') {
                fputcsv($file, ['Operator Code', 'Operator Name', 'Total STBs', 'Complaint Qty', 'Service Done Qty', 'QC Tested OK Qty', 'Delivered Qty', 'Send to PUD Qty', 'Dead Boxes Qty']);
                $ops = Operator::orderBy('operator_name')->get();
                foreach ($ops as $op) {
                    $boxesInOp = SetTopBox::where('operator_id', $op->id)->get();
                    fputcsv($file, [
                        $op->operator_code,
                        $op->operator_name,
                        $boxesInOp->count(),
                        $boxesInOp->whereIn('stb_status', ['complaint', 'reservice'])->count(),
                        $boxesInOp->where('stb_status', 'service_done')->count(),
                        $boxesInOp->where('stb_status', 'tested_ok')->count(),
                        $boxesInOp->filter(fn($b) => $b->isDelivered())->count(),
                        $boxesInOp->where('stb_status', 'send_to_pud')->count(),
                        $boxesInOp->whereIn('stb_status', ['flash', 'software_issue'])->count(),
                    ]);
                }
            } elseif ($tab === 'pud_wise') {
                fputcsv($file, ['Barcode Number', 'Box Model', 'Cable Operator', 'Status', 'Sent/Updated Date', 'Remarks']);
                $pudBoxes = SetTopBox::with(['boxModel', 'operator'])->where('stb_status', 'send_to_pud')->get();
                foreach ($pudBoxes as $pb) {
                    fputcsv($file, [
                        $pb->barcode_number,
                        $pb->boxModel->model_name ?? $pb->box_name,
                        $pb->operator->operator_name ?? 'Unassigned',
                        'Send to PUD',
                        $pb->updated_at ? $pb->updated_at->format('Y-m-d H:i:s') : '',
                        $pb->remarks ?? ''
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
