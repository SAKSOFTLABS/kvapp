<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type', 50)->default('purchase');
            $table->date('date');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->string('supplier')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
