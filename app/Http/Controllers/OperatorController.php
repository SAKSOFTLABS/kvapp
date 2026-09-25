<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class OperatorController extends Controller
{
    public function index(Request $request)
    {
        $query = Operator::withCount(['boxes', 'boxes as complaint_boxes_count' => function ($q) {
            $q->where('stb_status', 'complaint');
        }, 'boxes as tested_ok_boxes_count' => function ($q) {
            $q->where('stb_status', 'tested_ok');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('operator_name', 'like', "%{$search}%")
                  ->orWhere('operator_code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $operators = $query->orderBy('operator_name')->paginate(15)->withQueryString();

        return view('master.operators.index', compact('operators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'operator_code' => 'required|string|max:50|unique:operators,operator_code',
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $operator = Operator::create($validated);

        ActivityLogService::log('CREATE_OPERATOR', "Created Cable Operator '{$operator->operator_name}' ({$operator->operator_code})");

        return redirect()->route('operators.index')->with('success', "Cable Operator '{$operator->operator_name}' created successfully!");
    }

    public function update(Request $request, $id)
    {
        $operator = Operator::findOrFail($id);

        $validated = $request->validate([
            'operator_name' => 'required|string|max:255',
            'operator_code' => 'required|string|max:50|unique:operators,operator_code,' . $id,
            'contact_person' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $operator->update($validated);

        ActivityLogService::log('UPDATE_OPERATOR', "Updated Cable Operator '{$operator->operator_name}' ({$operator->operator_code})");

        return redirect()->route('operators.index')->with('success', "Cable Operator '{$operator->operator_name}' updated successfully!");
    }

    public function destroy($id)
    {
        $operator = Operator::findOrFail($id);
        
        if ($operator->boxes()->count() > 0) {
            return back()->with('error', "Cannot delete Operator '{$operator->operator_name}' because it has assigned Set Top Boxes.");
        }

        $name = $operator->operator_name;
        $operator->delete();

        ActivityLogService::log('DELETE_OPERATOR', "Deleted Cable Operator '{$name}'");

        return redirect()->route('operators.index')->with('success', "Cable Operator '{$name}' deleted successfully!");
    }

    /**
     * Import Cable Operators from CSV file (Super Admin Only)
     */
    public function importCsv(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access. Only Super Admin can import cable operators.');
        }

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to read uploaded CSV file.');
        }

        $header = fgetcsv($handle); // Read header line

        $importedCount = 0;
        $skippedCount = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($row) < 2) {
                    $skippedCount++;
                    continue;
                }

                $operatorName = trim($row[0] ?? '');
                $operatorCode = trim($row[1] ?? '');
                $contactPerson = trim($row[2] ?? '');
                $mobile = trim($row[3] ?? '');
                $location = trim($row[4] ?? '');
                $status = strtolower(trim($row[5] ?? 'active'));
                if (!in_array($status, ['active', 'inactive'])) $status = 'active';

                if (empty($operatorName) || empty($operatorCode)) {
                    $skippedCount++;
                    continue;
                }

                $existing = Operator::where('operator_code', $operatorCode)->first();
                if ($existing) {
                    $existing->update([
                        'operator_name' => $operatorName,
                        'contact_person' => $contactPerson,
                        'mobile' => $mobile,
                        'location' => $location,
                        'status' => $status,
                    ]);
                    $importedCount++;
                } else {
                    Operator::create([
                        'operator_name' => $operatorName,
                        'operator_code' => $operatorCode,
                        'contact_person' => $contactPerson,
                        'mobile' => $mobile,
                        'location' => $location,
                        'status' => $status,
                    ]);
                    $importedCount++;
                }
            }

            fclose($handle);
            DB::commit();

            ActivityLogService::log('IMPORT_OPERATORS', "Super Admin imported {$importedCount} cable operators from CSV file.");

            return redirect()->route('operators.index')
                ->with('success', "Successfully imported/updated {$importedCount} Cable Operators from CSV file! ({$skippedCount} invalid rows skipped).");
        } catch (Exception $e) {
            DB::rollBack();
            if ($handle) fclose($handle);
            return back()->with('error', 'CSV Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download Sample CSV for Cable Operator Import
     */
    public function downloadSampleCsv()
    {
        $filename = "operator_import_sample.csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['operator_name', 'operator_code', 'contact_person', 'mobile', 'location', 'status']);
            fputcsv($file, ['Kaloor Cable Vision', 'LCO-KLR-001', 'Suresh Kumar', '9847012345', 'Kaloor Junction, Ernakulam', 'active']);
            fputcsv($file, ['Ernakulam Digital Network', 'LCO-EKM-002', 'Mathew Joseph', '9847023456', 'MG Road, Kochi', 'active']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
