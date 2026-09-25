<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modify stb_status column in set_top_boxes to VARCHAR(50) to support 'delivered' status
        DB::statement("ALTER TABLE set_top_boxes MODIFY COLUMN stb_status VARCHAR(50) NOT NULL DEFAULT 'complaint'");

        // 2. Create checkout_vouchers table
        Schema::create('checkout_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->date('checkout_date');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade');
            $table->integer('total_boxes')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. Create checkout_voucher_items table
        Schema::create('checkout_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_voucher_id')->constrained('checkout_vouchers')->onDelete('cascade');
            $table->foreignId('set_top_box_id')->constrained('set_top_boxes')->onDelete('cascade');
            $table->string('barcode_number');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_voucher_items');
        Schema::dropIfExists('checkout_vouchers');
    }
};
