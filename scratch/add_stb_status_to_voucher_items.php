<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('checkin_voucher_items', 'stb_status')) {
    Schema::table('checkin_voucher_items', function (Blueprint $table) {
        $table->string('stb_status', 50)->nullable()->after('barcode_number');
    });
    echo "Added stb_status column to checkin_voucher_items table.\n";
} else {
    echo "stb_status column already exists on checkin_voucher_items table.\n";
}

if (!Schema::hasColumn('checkout_voucher_items', 'stb_status')) {
    Schema::table('checkout_voucher_items', function (Blueprint $table) {
        $table->string('stb_status', 50)->nullable()->after('barcode_number');
    });
    echo "Added stb_status column to checkout_voucher_items table.\n";
} else {
    echo "stb_status column already exists on checkout_voucher_items table.\n";
}
