<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Staff;
use App\Models\SetTopBox;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([
                'items' => [],
                'staff' => [],
                'boxes' => [],
            ]);
        }

        $items = Item::where('item_name', 'like', "%{$q}%")
            ->orWhere('item_code', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'item_name', 'item_code', 'sales_price']);

        $staff = Staff::where('name', 'like', "%{$q}%")
            ->orWhere('username', 'like', "%{$q}%")
            ->orWhere('mobile', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'name', 'designation', 'mobile']);

        $boxes = SetTopBox::where('box_name', 'like', "%{$q}%")
            ->orWhere('barcode_number', 'like', "%{$q}%")
            ->take(5)
            ->get(['id', 'box_name', 'barcode_number', 'status']);

        return response()->json([
            'items' => $items,
            'staff' => $staff,
            'boxes' => $boxes,
        ]);
    }
}
