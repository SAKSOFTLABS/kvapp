<?php

namespace App\Http\Controllers;

use App\Models\ServiceTransaction;
use App\Models\BoxModel;
use App\Models\Staff;
use App\Models\SetTopBox;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DetailedServiceReportController extends Controller
{
    public function index(Request $request)
    {
        $boxModels = BoxModel::where('status', 'active')->orderBy('model_name')->get();
        $staffList = Staff::where('status', 'active')->orderBy('name')->get();

        $query = ServiceTransaction::with(['setTopBox.boxModel', 'setTopBox.operator', 'technician', 'items.item']);

        // 1. Box Model filter
        if ($request->filled('box_model_id')) {
            $modelId = $request->box_model_id;
            $query->whereHas('setTopBox', function ($q) use ($modelId) {
                $q->where('box_model_id', $modelId);
            });
        }

        // 2. Staff / Technician filter
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // 3. Date From & Date To filter
        if ($request->filled('date_from')) {
            $query->whereDate('service_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('service_date', '<=', $request->date_to);
        }

        // 4. Repaired Box / Action Type filter
        if ($request->filled('action_type')) {
            $action = $request->action_type;
            if ($action === 'repaired' || $action === 'service_done') {
                $query->where('remarks', 'not like', '[SENT TO PUD%')
                      ->where('remarks', 'not like', '[FLASH%')
                      ->where('remarks', 'not like', '[SOFTWARE ISSUE%');
            } elseif ($action === 'send_to_pud') {
                $query->where(function ($q) {
                    $q->where('remarks', 'like', '[SENT TO PUD%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'send_to_pud');
                      });
                });
            } elseif ($action === 'flash') {
                $query->where(function ($q) {
                    $q->where('remarks', 'like', '[FLASH%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'flash');
                      });
                });
            } elseif ($action === 'software_issue') {
                $query->where(function ($q) {
                    $q->where('remarks', 'like', '[SOFTWARE ISSUE%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'software_issue');
                      });
                });
            }
        }

        // 5. Keyword / Barcode / Code Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('service_code', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('setTopBox', function ($bq) use ($search) {
                      $bq->where('barcode_number', 'like', "%{$search}%")
                         ->orWhere('box_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('technician', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Clone query for metrics calculations
        $statsQuery = clone $query;
        $allMatchingServices = $statsQuery->get();

        $totalServices = $allMatchingServices->count();
        $repairedCount = $allMatchingServices->filter(function ($s) {
            return !str_contains($s->remarks ?? '', '[SENT TO PUD')
                && !str_contains($s->remarks ?? '', '[FLASH')
                && !str_contains($s->remarks ?? '', '[SOFTWARE ISSUE');
        })->count();

        $pudCount = $allMatchingServices->filter(function ($s) {
            return str_contains($s->remarks ?? '', '[SENT TO PUD')
                || ($s->setTopBox && $s->setTopBox->stb_status === 'send_to_pud');
        })->count();

        $flashCount = $allMatchingServices->filter(function ($s) {
            return str_contains($s->remarks ?? '', '[FLASH')
                || ($s->setTopBox && $s->setTopBox->stb_status === 'flash');
        })->count();

        $softwareIssueCount = $allMatchingServices->filter(function ($s) {
            return str_contains($s->remarks ?? '', '[SOFTWARE ISSUE')
                || ($s->setTopBox && $s->setTopBox->stb_status === 'software_issue');
        })->count();

        $totalCostSum = $allMatchingServices->sum('total_cost');

        $services = $query->orderBy('service_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25)
            ->withQueryString();

        return view('reports.detailed_service', compact(
            'boxModels',
            'staffList',
            'services',
            'totalServices',
            'repairedCount',
            'pudCount',
            'flashCount',
            'softwareIssueCount',
            'totalCostSum'
        ));
    }

    public function exportCsv(Request $request)
    {
        $filename = "detailed_service_report_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $query = ServiceTransaction::with(['setTopBox.boxModel', 'setTopBox.operator', 'technician', 'items.item']);

        if ($request->filled('box_model_id')) {
            $modelId = $request->box_model_id;
            $query->whereHas('setTopBox', function ($q) use ($modelId) {
                $q->where('box_model_id', $modelId);
            });
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('service_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('service_date', '<=', $request->date_to);
        }

        if ($request->filled('action_type')) {
            $action = $request->action_type;
            if ($action === 'repaired' || $action === 'service_done') {
                $query->where('remarks', 'not like', '[SENT TO PUD%')
                      ->where('remarks', 'not like', '[FLASH%')
                      ->where('remarks', 'not like', '[SOFTWARE ISSUE%');
            } elseif ($action === 'send_to_pud') {
                $query->where(function ($q) {
                    $q->where('remarks', 'like', '[SENT TO PUD%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'send_to_pud');
                      });
                });
            } elseif ($action === 'flash') {
                $query->where(function ($q) {
                    $q->where('remarks', 'like', '[FLASH%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'flash');
                      });
                });
            } elseif ($action === 'software_issue') {
                $query->where(function ($q) {
                    $q->where('remarks', 'like', '[SOFTWARE ISSUE%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'software_issue');
                      });
                });
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('service_code', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('setTopBox', function ($bq) use ($search) {
                      $bq->where('barcode_number', 'like', "%{$search}%")
                         ->orWhere('box_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('technician', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $services = $query->orderBy('service_date', 'desc')->orderBy('id', 'desc')->get();

        $callback = function () use ($services) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Service Code',
                'Service Date',
                'STB Barcode Number',
                'Box Model',
                'Cable Operator',
                'Assigned Technician',
                'Repair Result / Status',
                'Total Cost (INR)',
                'Remarks'
            ]);

            foreach ($services as $s) {
                $actionResult = 'Service Done (Repaired)';
                if (str_contains($s->remarks ?? '', '[SENT TO PUD') || ($s->setTopBox && $s->setTopBox->stb_status === 'send_to_pud')) {
                    $actionResult = 'Complaint (Send to PUD)';
                } elseif (str_contains($s->remarks ?? '', '[FLASH') || ($s->setTopBox && $s->setTopBox->stb_status === 'flash')) {
                    $actionResult = 'Flash (Dead Box)';
                } elseif (str_contains($s->remarks ?? '', '[SOFTWARE ISSUE') || ($s->setTopBox && $s->setTopBox->stb_status === 'software_issue')) {
                    $actionResult = 'Software Issue (Dead Box)';
                }

                fputcsv($file, [
                    $s->service_code,
                    Carbon::parse($s->service_date)->format('Y-m-d'),
                    $s->setTopBox->barcode_number ?? 'N/A',
                    $s->setTopBox->boxModel->model_name ?? ($s->setTopBox->box_name ?? 'N/A'),
                    $s->setTopBox->operator->operator_name ?? 'Unassigned',
                    $s->technician->name ?? 'N/A',
                    $actionResult,
                    number_format($s->total_cost, 2),
                    $s->remarks ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
