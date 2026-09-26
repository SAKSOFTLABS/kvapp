<?php

namespace App\Http\Controllers;

use App\Models\SetTopBox;
use App\Models\CheckinVoucherItem;
use App\Models\CheckoutVoucherItem;
use App\Models\ServiceTransaction;
use App\Models\QcCheck;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StbHistoryReportController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('search', $request->get('barcode', '')));
        $stbId = $request->get('stb_id');
        $sortOrder = strtolower($request->get('sort', 'desc')) === 'asc' ? 'asc' : 'desc';

        $stbList = SetTopBox::where('status', 'active')->orderBy('barcode_number')->get();

        $selectedBox = null;

        if ($stbId) {
            $selectedBox = SetTopBox::with(['boxModel', 'operator'])->find($stbId);
        } elseif (!empty($search)) {
            $selectedBox = SetTopBox::with(['boxModel', 'operator'])
                ->where('barcode_number', $search)
                ->orWhere('barcode_number', 'like', "%{$search}%")
                ->first();
        }

        $events = [];
        $latestCheckout = null;

        if ($selectedBox) {
            // Check Latest Delivery Checkout status
            $latestCheckoutItem = CheckoutVoucherItem::with(['checkoutVoucher.operator', 'voucher.operator'])
                ->where('set_top_box_id', $selectedBox->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestCheckoutItem) {
                $latestCheckout = $latestCheckoutItem;
            }

            // 1. Box Registration Event
            $events[] = [
                'event_type' => 'REGISTER',
                'title' => 'STB Box Registered',
                'icon' => 'bi-plus-circle-fill',
                'badge_class' => 'bg-info text-dark',
                'date_time' => $selectedBox->created_at,
                'user_name' => 'System / Intake',
                'details' => [
                    'Box Model' => $selectedBox->boxModel->model_name ?? ($selectedBox->box_name ?? 'N/A'),
                    'Barcode Number' => $selectedBox->barcode_number,
                    'Initial Operator' => $selectedBox->operator->operator_name ?? 'Unassigned',
                    'Initial Status' => ucfirst(str_replace('_', ' ', $selectedBox->stb_status)),
                    'Remarks' => $selectedBox->remarks ?? 'N/A',
                ],
                'spare_parts' => [],
            ];

            // 2. Check-In Voucher Intake Events
            $checkinItems = CheckinVoucherItem::with(['checkinVoucher.operator', 'checkinVoucher.creator', 'voucher.operator', 'voucher.creator'])
                ->where(function ($q) use ($selectedBox) {
                    $q->where('set_top_box_id', $selectedBox->id)
                      ->orWhere('barcode_number', $selectedBox->barcode_number);
                })
                ->get();

            foreach ($checkinItems as $ci) {
                $voucher = $ci->checkinVoucher ?? ($ci->voucher ?? \App\Models\CheckinVoucher::with(['operator', 'creator'])->find($ci->checkin_voucher_id));
                if ($voucher) {
                    $intakeStatus = $ci->stb_status ? ucfirst(str_replace('_', ' ', $ci->stb_status)) : 'Complaint';
                    $events[] = [
                        'event_type' => 'CHECKIN',
                        'title' => 'STB Check-In Intake',
                        'icon' => 'bi-box-arrow-in-down',
                        'badge_class' => 'bg-primary text-white',
                        'date_time' => $ci->created_at ?? $voucher->created_at,
                        'user_name' => $voucher->creator->name ?? 'System',
                        'operator_name' => $voucher->operator->operator_name ?? 'N/A',
                        'status' => $intakeStatus,
                        'voucher_number' => $voucher->voucher_number,
                        'details' => [
                            'Voucher Number' => $voucher->voucher_number,
                            'Check-In Date' => Carbon::parse($voucher->checkin_date)->format('d M Y'),
                            'Received From Operator' => $voucher->operator->operator_name ?? 'N/A',
                            'Intake Status' => $intakeStatus,
                            'Remarks' => $voucher->remarks ?? 'N/A',
                        ],
                        'spare_parts' => [],
                    ];
                }
            }

            // 3. Service & Repair Events with Spare Parts
            $serviceTxns = ServiceTransaction::with(['technician', 'creator', 'items.item', 'setTopBox'])
                ->where('set_top_box_id', $selectedBox->id)
                ->get();

            foreach ($serviceTxns as $srv) {
                $spareParts = [];
                foreach ($srv->items as $item) {
                    $spareParts[] = [
                        'item_code' => $item->item->item_code ?? 'N/A',
                        'item_name' => $item->item->item_name ?? 'N/A',
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->total_cost,
                    ];
                }

                $statusLabel = 'Completed';
                $badgeClass = 'bg-success text-white';

                if (str_contains($srv->remarks ?? '', '[SENT TO PUD') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'send_to_pud')) {
                    $statusLabel = 'Complaint (Send to PUD)';
                    $badgeClass = 'bg-danger text-white';
                } elseif (str_contains($srv->remarks ?? '', '[FLASH') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'flash')) {
                    $statusLabel = 'Flash (Dead Box)';
                    $badgeClass = 'bg-dark text-white';
                } elseif (str_contains($srv->remarks ?? '', '[SOFTWARE ISSUE') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'software_issue')) {
                    $statusLabel = 'Software Issue (Dead Box)';
                    $badgeClass = 'bg-dark text-white';
                }

                $events[] = [
                    'event_type' => 'SERVICE',
                    'title' => 'Service & Repair',
                    'icon' => 'bi-tools',
                    'badge_class' => 'bg-warning text-dark',
                    'date_time' => $srv->created_at ?? Carbon::parse($srv->service_date),
                    'user_name' => $srv->technician->name ?? ($srv->creator->name ?? 'Technician'),
                    'operator_name' => 'Service Center',
                    'status' => $statusLabel,
                    'status_badge_class' => $badgeClass,
                    'voucher_number' => $srv->service_code,
                    'details' => [
                        'Service Code' => $srv->service_code,
                        'Service Date' => Carbon::parse($srv->service_date)->format('d M Y'),
                        'Assigned Technician' => $srv->technician->name ?? 'Unassigned',
                        'Issues Reported' => $srv->problem_description ?? 'N/A',
                        'Action / Repair Done' => $srv->action_taken ?? 'N/A',
                        'Work Status' => $statusLabel,
                        'Total Repair Cost' => '₹' . number_format($srv->total_cost, 2),
                        'Remarks' => $srv->remarks ?? 'N/A',
                    ],
                    'spare_parts' => $spareParts,
                ];
            }

            // 4. QC Testing Inspection Events
            $qcChecks = QcCheck::with(['inspector'])
                ->where('set_top_box_id', $selectedBox->id)
                ->get();

            foreach ($qcChecks as $qc) {
                $statusLabel = 'Tested OK (Passed)';
                $badgeClass = 'bg-success text-white';
                if ($qc->qc_status === 'flash') {
                    $statusLabel = 'Flash (Dead Box)';
                    $badgeClass = 'bg-danger text-white';
                } elseif ($qc->qc_status === 'complaint') {
                    $statusLabel = 'QC Rejected (Complaint)';
                    $badgeClass = 'bg-secondary text-white';
                }

                $events[] = [
                    'event_type' => 'QC',
                    'title' => 'QC Inspection',
                    'icon' => 'bi-patch-check-fill',
                    'badge_class' => $badgeClass,
                    'date_time' => $qc->created_at ?? Carbon::parse($qc->qc_date),
                    'user_name' => $qc->inspector->name ?? 'QC Inspector',
                    'operator_name' => 'QC Section',
                    'status' => $statusLabel,
                    'voucher_number' => $qc->voucher_number,
                    'details' => [
                        'QC Voucher #' => $qc->voucher_number,
                        'QC Inspection Date' => Carbon::parse($qc->qc_date)->format('d M Y'),
                        'QC Inspector' => $qc->inspector->name ?? 'N/A',
                        'QC Status Result' => $statusLabel,
                        'Inspector Remarks' => $qc->remarks ?? 'N/A',
                    ],
                    'spare_parts' => [],
                ];
            }

            // 5. Check-Out Delivery Voucher Events
            $checkoutItems = CheckoutVoucherItem::with(['checkoutVoucher.operator', 'checkoutVoucher.creator', 'voucher.operator', 'voucher.creator'])
                ->where(function ($q) use ($selectedBox) {
                    $q->where('set_top_box_id', $selectedBox->id)
                      ->orWhere('barcode_number', $selectedBox->barcode_number);
                })
                ->get();

            foreach ($checkoutItems as $co) {
                $voucher = $co->checkoutVoucher ?? ($co->voucher ?? \App\Models\CheckoutVoucher::with(['operator', 'creator'])->find($co->checkout_voucher_id));
                if ($voucher) {
                    $deliveryStatus = $co->stb_status ? ucfirst(str_replace('_', ' ', $co->stb_status)) : 'Delivered';
                    if ($deliveryStatus === 'Tested ok') {
                        $deliveryStatus = 'Tested OK (Passed)';
                    } elseif ($deliveryStatus === 'Flash') {
                        $deliveryStatus = 'Flash (Dead Box)';
                    }

                    $events[] = [
                        'event_type' => 'CHECKOUT',
                        'title' => 'STB Check-Out Delivery',
                        'icon' => 'bi-box-arrow-up-right',
                        'badge_class' => 'bg-success text-white',
                        'date_time' => $co->created_at ?? $voucher->created_at,
                        'user_name' => $voucher->creator->name ?? 'System',
                        'operator_name' => $voucher->operator->operator_name ?? 'N/A',
                        'status' => $deliveryStatus,
                        'voucher_number' => $voucher->voucher_number,
                        'details' => [
                            'Delivery Voucher' => $voucher->voucher_number,
                            'Delivery Date' => Carbon::parse($voucher->checkout_date)->format('d M Y'),
                            'Delivered To Operator' => $voucher->operator->operator_name ?? 'N/A',
                            'Delivery Box Status' => $deliveryStatus,
                            'Remarks' => $voucher->remarks ?? 'N/A',
                        ],
                        'spare_parts' => [],
                    ];
                }
            }

            // Sort Events Date & Time wise
            usort($events, function ($a, $b) use ($sortOrder) {
                $timeA = Carbon::parse($a['date_time'])->timestamp;
                $timeB = Carbon::parse($b['date_time'])->timestamp;

                if ($timeA === $timeB) {
                    return 0;
                }
                return ($sortOrder === 'asc') ? ($timeA <=> $timeB) : ($timeB <=> $timeA);
            });
        }

        return view('reports.stb_history', compact('stbList', 'selectedBox', 'events', 'search', 'sortOrder', 'latestCheckout'));
    }

    public function printReport($id, Request $request)
    {
        $sortOrder = strtolower($request->get('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
        $selectedBox = SetTopBox::with(['boxModel', 'operator'])->findOrFail($id);

        $events = [];

        // Gather events same as index
        $events[] = [
            'event_type' => 'REGISTER',
            'title' => 'STB Box Registered',
            'badge_class' => 'bg-info text-dark',
            'date_time' => $selectedBox->created_at,
            'user_name' => 'System / Intake',
            'details' => [
                'Box Model' => $selectedBox->boxModel->model_name ?? ($selectedBox->box_name ?? 'N/A'),
                'Barcode Number' => $selectedBox->barcode_number,
                'Initial Operator' => $selectedBox->operator->operator_name ?? 'Unassigned',
                'Initial Status' => ucfirst(str_replace('_', ' ', $selectedBox->stb_status)),
            ],
            'spare_parts' => [],
        ];

        $checkinItems = CheckinVoucherItem::with(['checkinVoucher.operator', 'checkinVoucher.creator'])
            ->where('set_top_box_id', $selectedBox->id)
            ->get();
        foreach ($checkinItems as $ci) {
            $voucher = $ci->checkinVoucher;
            if ($voucher) {
                $events[] = [
                    'event_type' => 'CHECKIN',
                    'title' => "STB Check-In Intake (Voucher #{$voucher->voucher_number})",
                    'badge_class' => 'bg-primary text-white',
                    'date_time' => $ci->created_at ?? $voucher->created_at,
                    'user_name' => $voucher->creator->name ?? 'System',
                    'details' => [
                        'Voucher Number' => $voucher->voucher_number,
                        'Check-In Date' => Carbon::parse($voucher->checkin_date)->format('d M Y'),
                        'Operator' => $voucher->operator->operator_name ?? 'N/A',
                        'Intake Status' => $ci->stb_status ? ucfirst(str_replace('_', ' ', $ci->stb_status)) : 'Complaint',
                    ],
                    'spare_parts' => [],
                ];
            }
        }

        $serviceTxns = ServiceTransaction::with(['technician', 'creator', 'items.item', 'setTopBox'])
            ->where('set_top_box_id', $selectedBox->id)
            ->get();
        foreach ($serviceTxns as $srv) {
            $spareParts = [];
            foreach ($srv->items as $item) {
                $spareParts[] = [
                    'item_code' => $item->item->item_code ?? 'N/A',
                    'item_name' => $item->item->item_name ?? 'N/A',
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->total_cost,
                ];
            }

            $statusLabel = 'Completed';
            if (str_contains($srv->remarks ?? '', '[SENT TO PUD') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'send_to_pud')) {
                $statusLabel = 'Complaint (Send to PUD)';
            } elseif (str_contains($srv->remarks ?? '', '[FLASH') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'flash')) {
                $statusLabel = 'Flash (Dead Box)';
            } elseif (str_contains($srv->remarks ?? '', '[SOFTWARE ISSUE') || ($srv->setTopBox && $srv->setTopBox->stb_status === 'software_issue')) {
                $statusLabel = 'Software Issue (Dead Box)';
            }

            $events[] = [
                'event_type' => 'SERVICE',
                'title' => "Service & Repair (#{$srv->service_code})",
                'badge_class' => 'bg-warning text-dark',
                'date_time' => $srv->created_at ?? Carbon::parse($srv->service_date),
                'user_name' => $srv->technician->name ?? ($srv->creator->name ?? 'Technician'),
                'details' => [
                    'Service Code' => $srv->service_code,
                    'Technician' => $srv->technician->name ?? 'N/A',
                    'Problem' => $srv->problem_description ?? 'N/A',
                    'Action Done' => $srv->action_taken ?? 'N/A',
                    'Work Status' => $statusLabel,
                    'Repair Cost' => '₹' . number_format($srv->total_cost, 2),
                ],
                'spare_parts' => $spareParts,
            ];
        }

        $qcChecks = QcCheck::with(['inspector'])
            ->where('set_top_box_id', $selectedBox->id)
            ->get();
        foreach ($qcChecks as $qc) {
            $events[] = [
                'event_type' => 'QC',
                'title' => "QC Inspection Result: " . ucfirst(str_replace('_', ' ', $qc->qc_status)),
                'badge_class' => 'bg-success text-white',
                'date_time' => $qc->created_at ?? Carbon::parse($qc->qc_date),
                'user_name' => $qc->inspector->name ?? 'QC Inspector',
                'details' => [
                    'QC Date' => Carbon::parse($qc->qc_date)->format('d M Y'),
                    'QC Inspector' => $qc->inspector->name ?? 'N/A',
                    'QC Result' => ucfirst(str_replace('_', ' ', $qc->qc_status)),
                    'Remarks' => $qc->remarks ?? 'N/A',
                ],
                'spare_parts' => [],
            ];
        }

        $checkoutItems = CheckoutVoucherItem::with(['checkoutVoucher.operator', 'checkoutVoucher.creator'])
            ->where('set_top_box_id', $selectedBox->id)
            ->get();
        foreach ($checkoutItems as $co) {
            $voucher = $co->checkoutVoucher;
            if ($voucher) {
                $events[] = [
                    'event_type' => 'CHECKOUT',
                    'title' => "STB Delivery Check-Out (Voucher #{$voucher->voucher_number})",
                    'badge_class' => 'bg-success text-white',
                    'date_time' => $co->created_at ?? $voucher->created_at,
                    'user_name' => $voucher->creator->name ?? 'System',
                    'details' => [
                        'Delivery Voucher' => $voucher->voucher_number,
                        'Delivery Date' => Carbon::parse($voucher->checkout_date)->format('d M Y'),
                        'Operator' => $voucher->operator->operator_name ?? 'N/A',
                        'Delivery Status' => $co->stb_status ? ucfirst(str_replace('_', ' ', $co->stb_status)) : 'Delivered',
                    ],
                    'spare_parts' => [],
                ];
            }
        }

        usort($events, function ($a, $b) use ($sortOrder) {
            $timeA = Carbon::parse($a['date_time'])->timestamp;
            $timeB = Carbon::parse($b['date_time'])->timestamp;
            if ($timeA === $timeB) return 0;
            return ($sortOrder === 'asc') ? ($timeA <=> $timeB) : ($timeB <=> $timeA);
        });

        return view('reports.print_stb_history', compact('selectedBox', 'events', 'sortOrder'));
    }

    public function exportCsv(Request $request)
    {
        $stbId = $request->get('stb_id');
        $box = SetTopBox::with(['boxModel', 'operator'])->findOrFail($stbId);

        $filename = "stb_history_{$box->barcode_number}_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($box) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['STB History Report for Barcode:', $box->barcode_number]);
            fputcsv($file, ['Box Model:', $box->boxModel->model_name ?? $box->box_name]);
            fputcsv($file, ['Operator:', $box->operator->operator_name ?? 'Unassigned']);
            fputcsv($file, ['Current Status:', $box->stb_status]);
            fputcsv($file, []);

            fputcsv($file, ['Date & Time', 'Event Category', 'Event Title', 'Performed By / User', 'Details / Remarks']);

            // Services
            $services = ServiceTransaction::with(['technician', 'items.item'])->where('set_top_box_id', $box->id)->get();
            foreach ($services as $s) {
                $partsStr = [];
                foreach ($s->items as $pi) {
                    $partsStr[] = "{$pi->item->item_name} (Qty: {$pi->quantity}, Cost: ₹{$pi->total_cost})";
                }
                $details = "Ticket: {$s->service_code} | Issues: {$s->problem_description} | Action: {$s->action_taken} | Parts: " . implode('; ', $partsStr);
                fputcsv($file, [
                    Carbon::parse($s->created_at)->format('Y-m-d H:i:s'),
                    'SERVICE & REPAIR',
                    "Service Ticket {$s->service_code}",
                    $s->technician->name ?? 'Technician',
                    $details
                ]);
            }

            // QC Checks
            $qcChecks = QcCheck::with('inspector')->where('set_top_box_id', $box->id)->get();
            foreach ($qcChecks as $qc) {
                fputcsv($file, [
                    Carbon::parse($qc->created_at)->format('Y-m-d H:i:s'),
                    'QC INSPECTION',
                    "Result: {$qc->qc_status}",
                    $qc->inspector->name ?? 'QC Inspector',
                    $qc->remarks ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
