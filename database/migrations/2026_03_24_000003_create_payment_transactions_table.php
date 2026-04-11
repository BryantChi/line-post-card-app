<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('renewal_orders')->onDelete('cascade');
            $table->string('transaction_no', 50)->nullable();
            $table->string('payment_method', 30);
            $table->unsignedInteger('amount');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
