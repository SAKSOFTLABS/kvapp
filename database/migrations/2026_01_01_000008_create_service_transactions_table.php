<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->date('service_date');
            $table->foreignId('set_top_box_id')->constrained('set_top_boxes')->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // Technician
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_transactions');
    }
};
