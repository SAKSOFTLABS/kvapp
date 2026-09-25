<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('set_top_boxes', function (Blueprint $table) {
            $table->foreignId('box_model_id')->nullable()->after('id')->constrained('box_models')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('set_top_boxes', function (Blueprint $table) {
            $table->dropForeign(['box_model_id']);
            $table->dropColumn('box_model_id');
        });
    }
};
