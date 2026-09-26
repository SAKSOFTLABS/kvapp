<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin can access System Settings.');
        }

        $settings = [
            'company_name' => Setting::get('company_name', 'Kerala Vision Service Management'),
            'company_phone' => Setting::get('company_phone', ''),
            'company_email' => Setting::get('company_email', ''),
            'company_address' => Setting::get('company_address', ''),
            'currency_symbol' => Setting::get('currency_symbol', '₹'),
            'low_stock_threshold' => Setting::get('low_stock_threshold', '10'),
            'app_zoom_level' => Setting::get('app_zoom_level', '80%'),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'Access Restricted: Only Super Admin can update System Settings.');
        }

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_address' => 'nullable|string',
            'currency_symbol' => 'required|string|max:10',
            'low_stock_threshold' => 'required|numeric|min:1',
            'app_zoom_level' => 'required|string|in:75%,80%,85%,90%,95%,100%,110%,125%',
        ]);

        foreach ($validated as $key => $val) {
            Setting::set($key, $val);
        }

        ActivityLogService::log('UPDATE_SETTINGS', "Updated application settings including zoom level ({$validated['app_zoom_level']})");

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
