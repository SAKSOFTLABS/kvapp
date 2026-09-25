<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('set_top_boxes', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->after('box_model_id')->constrained('operators')->nullOnDelete();
            $table->enum('stb_status', ['complaint', 'service_done', 'tested_ok', 'flash', 'send_to_pk'])->default('complaint')->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('set_top_boxes', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropColumn(['operator_id', 'stb_status']);
        });
    }
};
