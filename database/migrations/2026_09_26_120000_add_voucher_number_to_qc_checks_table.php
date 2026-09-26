<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('qc_checks', 'voucher_number')) {
            Schema::table('qc_checks', function (Blueprint $table) {
                $table->string('voucher_number', 100)->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('qc_checks', 'voucher_number')) {
            Schema::table('qc_checks', function (Blueprint $table) {
                $table->dropColumn('voucher_number');
            });
        }
    }
};
