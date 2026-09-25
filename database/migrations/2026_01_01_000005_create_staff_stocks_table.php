<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->unique(['staff_id', 'item_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_stocks');
    }
};
