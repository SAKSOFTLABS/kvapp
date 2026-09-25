<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_transactions MODIFY COLUMN transaction_type VARCHAR(50) NOT NULL DEFAULT 'purchase'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_transactions MODIFY COLUMN transaction_type ENUM('purchase', 'opening', 'adjustment') NOT NULL DEFAULT 'purchase'");
    }
};
