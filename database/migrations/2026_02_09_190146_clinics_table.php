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
        //consultorios
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('caascade');
            $table->string('hospital_name')->nullable();
            $table->string('address');
            $table->integer('phone');
            $table->string('schedule'); //horario de atencion

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
