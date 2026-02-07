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
            $table->morphs('attendable');

            $table->unsignedBigInteger('person_id')->nullable();
            $table->string('person_type')->default('miembro');
            $table->string('name');
            $table->string('phone');
            $table->string('state');
            $table->string('city');
            // $table->string('cmec_member_id');
            $table->string('status')->default('pendiente');
            $table->decimal('price', 10, 2);
            $table->string('stripe_transaction_id');

            //diploma pdf
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
