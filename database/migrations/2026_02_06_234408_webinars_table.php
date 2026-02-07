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
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->string('topic');
            $table->string('description');
            $table->string('objectives')->nullable();
            $table->date('date');
            $table->string('duration');
            $table->string('organized_by');
            $table->string('sponsored_by')->nullable();
            $table->decimal('member_price', 10, 2);
            $table->decimal('guess_price', 10, 2)->nullable();
            $table->decimal('resident_price', 10, 2)->nullable();
            $table->string('link')->nullable();

            //todo
            //patrocinado por
            //image
            //expositor
            //info expositor
            //documento pdf (opcional)
            //si es virtual. link
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webinars');
    }
};
