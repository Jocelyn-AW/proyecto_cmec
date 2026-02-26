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
        Schema::table('webinars', function (Blueprint $table) {
            // text es 64KB
            $table->text('description')->change();
            $table->text('objectives')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->string('description')->change();
            $table->string('objectives')->nullable()->change();
        });
    }
};
