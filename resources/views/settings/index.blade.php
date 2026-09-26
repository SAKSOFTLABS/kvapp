@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-extrabold mb-1"><i class="bi bi-gear-fill text-primary me-2"></i> System Settings</h3>
        <p class="text-muted small mb-0">Configure company metadata, currency, low stock alert limits, display zoom scale, and printable headers (Super Admin Only).</p>
    </div>
</div>

<div class="kv-card max-w-700">
    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-building me-2"></i> Company Information</h5>

        <div class="mb-3">
            <label class="form-label fw-bold small">Company Name *</label>
            <input type="text" name="company_name" class="form-control form-control-kv" value="{{ $settings['company_name'] }}" required>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label fw-bold small">Support Phone</label>
                <input type="text" name="company_phone" class="form-control form-control-kv" value="{{ $settings['company_phone'] }}">
            </div>
            <div class="col-6">
                <label class="form-label fw-bold small">Support Email</label>
                <input type="email" name="company_email" class="form-control form-control-kv" value="{{ $settings['company_email'] }}">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold small">Company Office Address</label>
            <textarea name="company_address" class="form-control form-control-kv" rows="2">{{ $settings['company_address'] }}</textarea>
        </div>

        <h5 class="fw-bold my-3 border-bottom pb-2 text-primary"><i class="bi bi-sliders me-2"></i> Inventory Thresholds & Currency</h5>

        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label fw-bold small">Currency Symbol *</label>
                <input type="text" name="currency_symbol" class="form-control form-control-kv" value="{{ $settings['currency_symbol'] }}" required>
            </div>
            <div class="col-6">
                <label class="form-label fw-bold small">Low Stock Alert Threshold (Qty) *</label>
                <input type="number" name="low_stock_threshold" class="form-control form-control-kv" value="{{ $settings['low_stock_threshold'] }}" required>
            </div>
        </div>

        <h5 class="fw-bold my-3 border-bottom pb-2 text-primary"><i class="bi bi-aspect-ratio me-2"></i> Display & Typography Zoom (Super Admin)</h5>

        <div class="mb-4">
            <label class="form-label fw-bold small">System Display Zoom Scale *</label>
            <select name="app_zoom_level" class="form-select form-select-kv" required>
                <option value="75%" {{ ($settings['app_zoom_level'] ?? '80%') == '75%' ? 'selected' : '' }}>75% - Ultra Compact View</option>
                <option value="80%" {{ ($settings['app_zoom_level'] ?? '80%') == '80%' ? 'selected' : '' }}>80% - Compact View (Recommended / Default)</option>
                <option value="85%" {{ ($settings['app_zoom_level'] ?? '80%') == '85%' ? 'selected' : '' }}>85% - Medium Compact View</option>
                <option value="90%" {{ ($settings['app_zoom_level'] ?? '80%') == '90%' ? 'selected' : '' }}>90% - Slight Compact View</option>
                <option value="95%" {{ ($settings['app_zoom_level'] ?? '80%') == '95%' ? 'selected' : '' }}>95% - Nearly Standard View</option>
                <option value="100%" {{ ($settings['app_zoom_level'] ?? '80%') == '100%' ? 'selected' : '' }}>100% - Standard Default View</option>
                <option value="110%" {{ ($settings['app_zoom_level'] ?? '80%') == '110%' ? 'selected' : '' }}>110% - Larger Text View</option>
                <option value="125%" {{ ($settings['app_zoom_level'] ?? '80%') == '125%' ? 'selected' : '' }}>125% - Extra Large Text View</option>
            </select>
            <div class="form-text text-muted small mt-1">
                <i class="bi bi-info-circle me-1"></i> Dynamically changes application font size & display zoom level across all pages for all users.
            </div>
        </div>

        <button type="submit" class="btn btn-kv-primary px-4 py-2">
            <i class="bi bi-check-circle me-1"></i> Save Configuration
        </button>
    </form>
</div>
@endsection
