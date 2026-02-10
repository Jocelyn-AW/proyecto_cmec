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
        Schema::create('payments', function (Blueprint $table){
            $table->id();
            $table->string('payment_method');
            $table->decimal('quantity', 10, 2);
            $table->date('payment_date');
            //si se crea table payment_methods
            // $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            
            $table->boolean('created_by_user');
            $table->unsignedBigInteger('user_id')->nullable();
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
