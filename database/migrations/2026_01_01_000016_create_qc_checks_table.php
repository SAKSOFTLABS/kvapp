<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('set_top_box_id')->constrained('set_top_boxes')->cascadeOnDelete();
            $table->foreignId('service_transaction_id')->nullable()->constrained('service_transactions')->nullOnDelete();
            $table->foreignId('qc_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('qc_status', ['tested_ok', 'complaint', 'flash']);
            $table->date('qc_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_checks');
    }
};
