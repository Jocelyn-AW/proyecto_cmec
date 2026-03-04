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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('format')->nullable()->default(null);
            $table->string('address')->nullable()->default(null);
            $table->string('additional_info')->nullable()->default(null);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('format');
            $table->dropColumn('address');
            $table->dropColumn('additional_info');
        });
    }
};
