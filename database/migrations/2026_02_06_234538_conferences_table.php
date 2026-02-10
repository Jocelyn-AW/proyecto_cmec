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
        //Congresos
        Schema::create('conferences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('main_topic');
            $table->string('format'); //modaliad: presencial o hibrida
            $table->string('held_by'); //sede
            $table->string('address');
            $table->string('google_coords');
            $table->datetime('date');
            $table->string('organized_by');
            $table->string('specialties');
            $table->decimal('member_price', 10, 2);
            $table->decimal('guest_price', 10, 2)->nullable();
            $table->decimal('resident_price', 10, 2)->nullable();
            $table->string('additional_comments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conferences');
    }
};
