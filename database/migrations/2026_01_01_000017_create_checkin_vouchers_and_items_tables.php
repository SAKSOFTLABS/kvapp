<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkin_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number')->unique();
            $table->date('checkin_date');
            $table->foreignId('operator_id')->constrained('operators')->onDelete('cascade');
            $table->integer('total_boxes')->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('checkin_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkin_voucher_id')->constrained('checkin_vouchers')->onDelete('cascade');
            $table->foreignId('set_top_box_id')->constrained('set_top_boxes')->onDelete('cascade');
            $table->string('barcode_number');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkin_voucher_items');
        Schema::dropIfExists('checkin_vouchers');
    }
};
