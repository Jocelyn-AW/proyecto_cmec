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
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('user');
            $table->nullableMorphs('event_payed');
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payer_phone')->nullable();
            $table->enum('payment_method', ['cash', 'debit_card', 'credit_card', 'transfer', 'stripe']);
            $table->decimal('amount', 10, 2);
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->date('payment_date');
            $table->boolean('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
