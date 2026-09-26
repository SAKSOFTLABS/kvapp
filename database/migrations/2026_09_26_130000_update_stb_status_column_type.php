<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure stb_status column in set_top_boxes table is VARCHAR(50)
        DB::statement("ALTER TABLE set_top_boxes MODIFY COLUMN stb_status VARCHAR(50) NOT NULL DEFAULT 'complaint'");
    }

    public function down(): void
    {
        // No-op
    }
};
