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
        Schema::create('invoice_data', function (Blueprint $table) {
            $table->id();
            // $table->integer('member_id')->nullable();
            $table->string('rfc');
            $table->string('name');
            $table->string('email');
            $table->string('postal_code');
            $table->string('tax_regime'); //regimen fiscal
            $table->string('cfdi_use'); //uso cfdi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_data');
    }
};
