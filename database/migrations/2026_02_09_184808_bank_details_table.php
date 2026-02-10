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
        Schema::create('bank_details', function (Blueprint $table){
            $table->id();
            $table->morphs('event'); //curso, congreso, webinar
            $table->string('bank');
            $table->string('account_number');
            $table->string('clabe_number');
            $table->string('reference')->nullable();
            $table->string('beneficiary')->nullable(); //a nombre de
            $table->string('subsidiary')->nullable(); //sucursal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_details');
    }
};
