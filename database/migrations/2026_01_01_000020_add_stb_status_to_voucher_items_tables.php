<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('checkin_voucher_items') && !Schema::hasColumn('checkin_voucher_items', 'stb_status')) {
            Schema::table('checkin_voucher_items', function (Blueprint $table) {
                $table->string('stb_status', 50)->nullable()->after('barcode_number');
            });
        }

        if (Schema::hasTable('checkout_voucher_items') && !Schema::hasColumn('checkout_voucher_items', 'stb_status')) {
            Schema::table('checkout_voucher_items', function (Blueprint $table) {
                $table->string('stb_status', 50)->nullable()->after('barcode_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('checkin_voucher_items') && Schema::hasColumn('checkin_voucher_items', 'stb_status')) {
            Schema::table('checkin_voucher_items', function (Blueprint $table) {
                $table->dropColumn('stb_status');
            });
        }

        if (Schema::hasTable('checkout_voucher_items') && Schema::hasColumn('checkout_voucher_items', 'stb_status')) {
            Schema::table('checkout_voucher_items', function (Blueprint $table) {
                $table->dropColumn('stb_status');
            });
        }
    }
};
