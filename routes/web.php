<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SetTopBoxController;
use App\Http\Controllers\BoxModelController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\StbCheckInController;
use App\Http\Controllers\StbCheckOutController;
use App\Http\Controllers\AddStockController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\ServiceSectionController;
use App\Http\Controllers\QcCheckController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StbHistoryReportController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;

// Public Auth Routes
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Global Search AJAX
    Route::get('/global-search', [GlobalSearchController::class, 'search'])->name('global.search');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Reports (Admin, Staff, QC)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/stock', [ReportController::class, 'index'])->name('reports.stock');
    Route::get('/reports/service', [ReportController::class, 'index'])->name('reports.service');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/stb-history', [StbHistoryReportController::class, 'index'])->name('reports.stb-history');
    Route::get('/reports/stb-history/export-csv', [StbHistoryReportController::class, 'exportCsv'])->name('reports.stb-history.export');
    Route::get('/reports/stb-history/{id}/print', [StbHistoryReportController::class, 'printReport'])->name('reports.stb-history.print');

    // STB Check-In Voucher Module (Admin, Front Office, Staff)
    Route::middleware(['role:admin,front_office,staff'])->group(function () {
        Route::get('/transactions/stb-checkin', [StbCheckInController::class, 'index'])->name('stb-checkin.index');
        Route::get('/transactions/stb-checkin/lookup', [StbCheckInController::class, 'lookupBarcode'])->name('stb-checkin.lookup');
        Route::post('/transactions/stb-checkin/register', [StbCheckInController::class, 'quickRegister'])->name('stb-checkin.register');
        Route::post('/transactions/stb-checkin/store-voucher', [StbCheckInController::class, 'storeVoucher'])->name('stb-checkin.store-voucher');
        Route::get('/transactions/stb-checkin/{id}', [StbCheckInController::class, 'show'])->name('stb-checkin.show');
        Route::get('/transactions/stb-checkin/{id}/edit', [StbCheckInController::class, 'edit'])->name('stb-checkin.edit');
        Route::put('/transactions/stb-checkin/{id}', [StbCheckInController::class, 'update'])->name('stb-checkin.update');
        Route::delete('/transactions/stb-checkin/{id}', [StbCheckInController::class, 'destroy'])->name('stb-checkin.destroy');
        Route::get('/transactions/stb-checkin/{id}/print', [StbCheckInController::class, 'printVoucher'])->name('stb-checkin.print');
    });

    // STB Delivery (Checkout) Voucher Module (Admin, Front Office, Staff)
    Route::middleware(['role:admin,front_office,staff'])->group(function () {
        Route::get('/transactions/stb-checkout', [StbCheckOutController::class, 'index'])->name('stb-checkout.index');
        Route::get('/transactions/stb-checkout/lookup', [StbCheckOutController::class, 'lookupBarcode'])->name('stb-checkout.lookup');
        Route::post('/transactions/stb-checkout/store-voucher', [StbCheckOutController::class, 'storeVoucher'])->name('stb-checkout.store-voucher');
        Route::get('/transactions/stb-checkout/{id}', [StbCheckOutController::class, 'show'])->name('stb-checkout.show');
        Route::get('/transactions/stb-checkout/{id}/edit', [StbCheckOutController::class, 'edit'])->name('stb-checkout.edit');
        Route::put('/transactions/stb-checkout/{id}', [StbCheckOutController::class, 'update'])->name('stb-checkout.update');
        Route::delete('/transactions/stb-checkout/{id}', [StbCheckOutController::class, 'destroy'])->name('stb-checkout.destroy');
        Route::get('/transactions/stb-checkout/{id}/print', [StbCheckOutController::class, 'printVoucher'])->name('stb-checkout.print');
    });

    // Service Section (Admin, Staff, QC)
    Route::get('/transactions/service', [ServiceSectionController::class, 'index'])->name('service.index');
    Route::post('/transactions/service', [ServiceSectionController::class, 'store'])->name('service.store');
    Route::get('/transactions/service/{id}', [ServiceSectionController::class, 'show'])->name('service.show');
    Route::delete('/transactions/service/{id}', [ServiceSectionController::class, 'destroy'])->name('service.destroy');
    Route::get('/transactions/service/technician-stock/{staffId}', [ServiceSectionController::class, 'getTechnicianStock'])->name('service.tech-stock');
    Route::get('/set-top-boxes/json-search-barcode', [SetTopBoxController::class, 'jsonSearchBarcode'])->name('set-top-boxes.json-barcode');
    Route::get('/set-top-boxes/{id}/history', [SetTopBoxController::class, 'history'])->name('set-top-boxes.history');
    Route::get('/set-top-boxes/{id}/print-report', [SetTopBoxController::class, 'printReport'])->name('set-top-boxes.print-report');

    // QC Inspection Module (Admin, QC, Staff)
    Route::get('/transactions/qc-check', [QcCheckController::class, 'index'])->name('qc.index');
    Route::post('/transactions/qc-check', [QcCheckController::class, 'store'])->name('qc.store');
    Route::put('/transactions/qc-check/{id}', [QcCheckController::class, 'update'])->name('qc.update');
    Route::delete('/transactions/qc-check/{id}', [QcCheckController::class, 'destroy'])->name('qc.destroy');

    // Master Data & All Transactions (Accessible for Admin, Staff, and QC)
    Route::middleware(['role:admin,staff,qc'])->group(function () {
        // Master: User Management (Admin Only)
        Route::get('/master/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/master/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/master/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/master/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Master: Items
        Route::get('/master/items', [ItemController::class, 'index'])->name('items.index');
        Route::post('/master/items', [ItemController::class, 'store'])->name('items.store');
        Route::put('/master/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/master/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::get('/master/items/export-csv', [ItemController::class, 'exportCsv'])->name('items.export-csv');
        Route::post('/master/items/import', [ItemController::class, 'importCsv'])->name('items.import');
        Route::get('/master/items/sample-csv', [ItemController::class, 'downloadSampleCsv'])->name('items.sample-csv');

        // Master: Staff
        Route::get('/master/staff', [StaffController::class, 'index'])->name('staff.index');
        Route::post('/master/staff', [StaffController::class, 'store'])->name('staff.store');
        Route::put('/master/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/master/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');
        Route::get('/master/staff/json-search', [StaffController::class, 'jsonSearch'])->name('staff.json-search');

        // Master: Set Top Boxes & Box Models & Cable Operators
        Route::get('/master/set-top-boxes', [SetTopBoxController::class, 'index'])->name('set-top-boxes.index');
        Route::post('/master/set-top-boxes', [SetTopBoxController::class, 'store'])->name('set-top-boxes.store');
        Route::put('/master/set-top-boxes/{id}', [SetTopBoxController::class, 'update'])->name('set-top-boxes.update');
        Route::delete('/master/set-top-boxes/{id}', [SetTopBoxController::class, 'destroy'])->name('set-top-boxes.destroy');

        Route::get('/master/box-models', [BoxModelController::class, 'index'])->name('box-models.index');
        Route::post('/master/box-models', [BoxModelController::class, 'store'])->name('box-models.store');
        Route::put('/master/box-models/{id}', [BoxModelController::class, 'update'])->name('box-models.update');
        Route::delete('/master/box-models/{id}', [BoxModelController::class, 'destroy'])->name('box-models.destroy');

        Route::get('/master/operators', [OperatorController::class, 'index'])->name('operators.index');
        Route::post('/master/operators', [OperatorController::class, 'store'])->name('operators.store');
        Route::put('/master/operators/{id}', [OperatorController::class, 'update'])->name('operators.update');
        Route::delete('/master/operators/{id}', [OperatorController::class, 'destroy'])->name('operators.destroy');
        Route::post('/master/operators/import', [OperatorController::class, 'importCsv'])->name('operators.import');
        Route::get('/master/operators/sample-csv', [OperatorController::class, 'downloadSampleCsv'])->name('operators.sample-csv');

        // Transactions: Add Stock (Main Store)
        Route::get('/transactions/add-stock', [AddStockController::class, 'index'])->name('add-stock.index');
        Route::post('/transactions/add-stock', [AddStockController::class, 'store'])->name('add-stock.store');
        Route::put('/transactions/add-stock/{id}', [AddStockController::class, 'update'])->name('add-stock.update');
        Route::delete('/transactions/add-stock/{id}', [AddStockController::class, 'destroy'])->name('add-stock.destroy');

        // Transactions: Stock Transfer
        Route::get('/transactions/stock-transfer', [StockTransferController::class, 'index'])->name('stock-transfer.index');
        Route::post('/transactions/stock-transfer', [StockTransferController::class, 'store'])->name('stock-transfer.store');
        Route::put('/transactions/stock-transfer/{id}', [StockTransferController::class, 'update'])->name('stock-transfer.update');
        Route::delete('/transactions/stock-transfer/{id}', [StockTransferController::class, 'destroy'])->name('stock-transfer.destroy');
        Route::get('/transactions/stock-transfer/item-info/{itemId}', [StockTransferController::class, 'getItemStockInfo'])->name('stock-transfer.item-info');

        // Settings (Admin Only)
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
