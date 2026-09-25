<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('set_top_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('box_name');
            $table->string('barcode_number')->unique()->index();
            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('set_top_boxes');
    }
};
