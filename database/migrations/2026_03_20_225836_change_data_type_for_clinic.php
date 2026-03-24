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
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('phone')->nullable()->default(null)->change();
            $table->string('address')->nullable()->default(null)->change();
            $table->string('schedule')->nullable()->default(null)->change();
        });

        Schema::table('directory_data', function(Blueprint $table) {
            $table->string('specialty')->nullable()->default(null)->change();
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
