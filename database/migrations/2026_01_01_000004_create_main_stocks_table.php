<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->unique()->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_stocks');
    }
};
