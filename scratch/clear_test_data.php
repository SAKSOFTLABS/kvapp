<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Clearing test data...\n";

Schema::disableForeignKeyConstraints();

// Truncate Transaction tables
DB::table('checkin_voucher_items')->truncate();
echo "- Cleared checkin_voucher_items\n";

DB::table('checkin_vouchers')->truncate();
echo "- Cleared checkin_vouchers\n";

DB::table('service_items')->truncate();
echo "- Cleared service_items\n";

DB::table('service_transactions')->truncate();
echo "- Cleared service_transactions\n";

DB::table('qc_checks')->truncate();
echo "- Cleared qc_checks\n";

if (Schema::hasTable('stock_transfer_items')) {
    DB::table('stock_transfer_items')->truncate();
    echo "- Cleared stock_transfer_items\n";
}

DB::table('stock_transfers')->truncate();
echo "- Cleared stock_transfers\n";

DB::table('stock_transactions')->truncate();
echo "- Cleared stock_transactions\n";

DB::table('staff_stocks')->truncate();
echo "- Cleared staff_stocks\n";

DB::table('activity_logs')->truncate();
echo "- Cleared activity_logs\n";

// Reset Main Stock quantities to 0
DB::table('main_stocks')->update(['quantity' => 0]);
echo "- Reset main_stocks quantities to 0\n";

// Reset STB Box statuses to 'complaint' and clear operator assignment
DB::table('set_top_boxes')->update([
    'operator_id' => null,
    'stb_status' => 'complaint',
    'remarks' => null
]);
echo "- Reset set_top_boxes status to 'complaint'\n";

Schema::enableForeignKeyConstraints();

echo "\nAll test data cleared successfully!\n";
