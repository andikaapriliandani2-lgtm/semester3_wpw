<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('customer_name')->default('Umum');
            $table->string('customer_phone', 30)->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->decimal('other_fee', 14, 2)->default(0);
            $table->decimal('grand_total', 14, 2);
            $table->decimal('paid_amount', 14, 2);
            $table->decimal('change_amount', 14, 2)->default(0);
            $table->string('payment_method', 30);
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });

        Schema::create('transaction_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_code')->nullable();
            $table->string('product_name');
            $table->decimal('price', 14, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
    }
};
