<?php

namespace App\Http\Controllers;

use App\Models\BoxModel;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class BoxModelController extends Controller
{
    public function index(Request $request)
    {
        $query = BoxModel::withCount('setTopBoxes');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                  ->orWhere('model_code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $models = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('master.box_models.index', compact('models'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_name' => 'required|string|max:255|unique:box_models,model_name',
            'model_code' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $model = BoxModel::create($validated);

        ActivityLogService::log('CREATE_BOX_MODEL', "Created Box Model / Group {$model->model_name}");

        return redirect()->back()->with('success', 'Set Top Box Model created successfully.');
    }

    public function update(Request $request, $id)
    {
        $model = BoxModel::findOrFail($id);

        $validated = $request->validate([
            'model_name' => 'required|string|max:255|unique:box_models,model_name,' . $id,
            'model_code' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $model->update($validated);

        ActivityLogService::log('UPDATE_BOX_MODEL', "Updated Box Model {$model->model_name}");

        return redirect()->back()->with('success', 'Set Top Box Model updated successfully.');
    }

    public function destroy($id)
    {
        $model = BoxModel::findOrFail($id);

        if ($model->setTopBoxes()->count() > 0) {
            return back()->with('error', 'Cannot delete Box Model because registered Set Top Boxes are associated with it.');
        }

        $model->delete();

        ActivityLogService::log('DELETE_BOX_MODEL', "Deleted Box Model {$model->model_name}");

        return redirect()->back()->with('success', 'Set Top Box Model deleted successfully.');
    }
}
