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
        Schema::table('attendees', function (Blueprint $table) {
            $table->text('specialty')
                ->after('did_attend')
                ->nullable()
                ->default(null); //especialidad
            $table->date('birth_date')
                ->after('specialty')
                ->nullable()
                ->default(null); //fecha de nacimiento
            $table->string('special_needs')
                ->after('birth_date')
                ->nullable()
                ->default(null); //ej: silla de ruedas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn('specialty');
            $table->dropColumn('birth_date');
            $table->dropColumn('special_needs');
        });
    }
};
