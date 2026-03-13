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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cash', 
                'debit_card', 
                'credit_card', 
                'transfer', 
                'stripe', 
                'free'
            ])->default('free')
            ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', [
                'cash', 
                'debit_card', 
                'credit_card', 
                'transfer', 
                'stripe'
            ])->default('cash')
            ->change();
        });
    }
};
