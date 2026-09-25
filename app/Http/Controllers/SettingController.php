<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'company_name' => Setting::get('company_name', 'Kerala Vision Service Management'),
            'company_phone' => Setting::get('company_phone', ''),
            'company_email' => Setting::get('company_email', ''),
            'company_address' => Setting::get('company_address', ''),
            'currency_symbol' => Setting::get('currency_symbol', '₹'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', '10'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string',
            'currency_symbol' => 'required|string|max:10',
            'low_stock_threshold' => 'required|numeric|min:1',
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, $val);
        }

        ActivityLogService::log('UPDATE_SETTINGS', 'Updated application settings');

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully.');
    }
}
