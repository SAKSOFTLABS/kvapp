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
        $staffList = Staff::where('status', 'active')->where('designation', '!=', 'Front Office')->orderBy('name')->get();

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

        $staffPerformance = [];
        foreach ($staffList as $st) {
            $staffTxns = $allMatchingServices->where('staff_id', $st->id);
            $stSoftware = $staffTxns->filter(function ($s) {
                return str_contains($s->remarks ?? '', '[SOFTWARE ISSUE')
                    || ($s->setTopBox && $s->setTopBox->stb_status === 'software_issue');
            })->count();

            $stFlash = $staffTxns->filter(function ($s) {
                return str_contains($s->remarks ?? '', '[FLASH')
                    || ($s->setTopBox && $s->setTopBox->stb_status === 'flash');
            })->count();

            $stPud = $staffTxns->filter(function ($s) {
                return str_contains($s->remarks ?? '', '[SENT TO PUD')
                    || ($s->setTopBox && $s->setTopBox->stb_status === 'send_to_pud');
            })->count();

            $stRepaired = $staffTxns->filter(function ($s) {
                return !str_contains($s->remarks ?? '', '[SENT TO PUD')
                    && !str_contains($s->remarks ?? '', '[FLASH')
                    && !str_contains($s->remarks ?? '', '[SOFTWARE ISSUE')
                    && (!$s->setTopBox || !in_array($s->setTopBox->stb_status, ['send_to_pud', 'flash', 'software_issue']));
            })->count();

            $staffPerformance[] = [
                'staff' => $st,
                'total' => $staffTxns->count(),
                'repaired' => $stRepaired,
                'flash' => $stFlash,
                'software_issue' => $stSoftware,
                'pud' => $stPud,
            ];
        }

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
            'totalCostSum',
            'staffPerformance'
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

    public function printReport(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $monthTitle = Carbon::parse($dateFrom)->format('M-Y');
        if ($dateFrom !== Carbon::parse($dateFrom)->startOfMonth()->format('Y-m-d') || $dateTo !== Carbon::parse($dateTo)->endOfMonth()->format('Y-m-d')) {
            $monthTitle = Carbon::parse($dateFrom)->format('d-m-Y') . ' to ' . Carbon::parse($dateTo)->format('d-m-Y');
        }

        $staffQuery = Staff::where('status', 'active')->where('designation', '!=', 'Front Office');
        if ($request->filled('staff_id')) {
            $staffQuery->where('id', $request->staff_id);
        }
        $technicians = $staffQuery->orderBy('name')->get();

        $txQuery = ServiceTransaction::with(['setTopBox.boxModel', 'technician']);
        if ($request->filled('box_model_id')) {
            $modelId = $request->box_model_id;
            $txQuery->whereHas('setTopBox', function ($q) use ($modelId) {
                $q->where('box_model_id', $modelId);
            });
        }
        if ($request->filled('staff_id')) {
            $txQuery->where('staff_id', $request->staff_id);
        }

        if ($request->filled('action_type')) {
            $action = $request->action_type;
            if ($action === 'repaired' || $action === 'service_done') {
                $txQuery->where('remarks', 'not like', '[SENT TO PUD%')
                        ->where('remarks', 'not like', '[FLASH%')
                        ->where('remarks', 'not like', '[SOFTWARE ISSUE%');
            } elseif ($action === 'send_to_pud') {
                $txQuery->where(function ($q) {
                    $q->where('remarks', 'like', '[SENT TO PUD%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'send_to_pud');
                      });
                });
            } elseif ($action === 'flash') {
                $txQuery->where(function ($q) {
                    $q->where('remarks', 'like', '[FLASH%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'flash');
                      });
                });
            } elseif ($action === 'software_issue') {
                $txQuery->where(function ($q) {
                    $q->where('remarks', 'like', '[SOFTWARE ISSUE%')
                      ->orWhereHas('setTopBox', function ($sq) {
                          $sq->where('stb_status', 'software_issue');
                      });
                });
            }
        }

        $txQuery->whereDate('service_date', '>=', $dateFrom)
                ->whereDate('service_date', '<=', $dateTo);

        $services = $txQuery->orderBy('service_date', 'asc')->get();

        $datesList = [];
        $matrix = [];
        $totals = [];

        foreach ($technicians as $tech) {
            $totals[$tech->id] = [
                'repaired' => 0,
                'flash' => 0,
                'software_issue' => 0,
                'reservice' => 0,
            ];
        }

        foreach ($services as $srv) {
            $dStr = Carbon::parse($srv->service_date)->format('d-m-Y');
            if (!in_array($dStr, $datesList)) {
                $datesList[] = $dStr;
            }

            $tId = $srv->staff_id;
            if (!isset($matrix[$dStr])) {
                $matrix[$dStr] = [];
            }
            if (!isset($matrix[$dStr][$tId])) {
                $matrix[$dStr][$tId] = ['repaired' => 0, 'flash' => 0, 'software_issue' => 0];
            }

            $isSoftwareIssue = str_contains($srv->remarks ?? '', '[SOFTWARE ISSUE')
                || ($srv->setTopBox && $srv->setTopBox->stb_status === 'software_issue');

            $isFlash = str_contains($srv->remarks ?? '', '[FLASH')
                || ($srv->setTopBox && $srv->setTopBox->stb_status === 'flash');

            $isPud = str_contains($srv->remarks ?? '', '[SENT TO PUD')
                || ($srv->setTopBox && $srv->setTopBox->stb_status === 'send_to_pud');

            if ($isSoftwareIssue) {
                $matrix[$dStr][$tId]['software_issue']++;
                if (isset($totals[$tId])) {
                    $totals[$tId]['software_issue']++;
                }
            } elseif ($isFlash || $isPud) {
                $matrix[$dStr][$tId]['flash']++;
                if (isset($totals[$tId])) {
                    $totals[$tId]['flash']++;
                }
            } else {
                $matrix[$dStr][$tId]['repaired']++;
                if (isset($totals[$tId])) {
                    $totals[$tId]['repaired']++;
                }
            }

            if ($srv->setTopBox && $srv->setTopBox->stb_status === 'reservice') {
                if (isset($totals[$tId])) {
                    $totals[$tId]['reservice']++;
                }
            }
        }

        usort($datesList, function ($a, $b) {
            return strtotime($a) <=> strtotime($b);
        });

        return view('reports.print_detailed_service', compact(
            'monthTitle',
            'dateFrom',
            'dateTo',
            'technicians',
            'datesList',
            'matrix',
            'totals'
        ));
    }
}
