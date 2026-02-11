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
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->morphs('event');
            $table->nullableMorphs('person');

            $table->string('name');
            $table->string('phone');
            $table->string('state');
            $table->string('city');
            // $table->string('cmec_member_id');
            $table->string('status')->default('pendiente');
            $table->decimal('price', 10, 2);
            // $table->string('stripe_transaction_id');
            $table->boolean('did_attend')->default(0); //asistió
            //diploma pdf

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendees');
    }
};
