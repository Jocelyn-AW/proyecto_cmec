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
            $table->string('format')
                ->after('is_active')
                ->nullable()
                ->default(null);

            $table->string('address')
                ->after('format')
                ->nullable()
                ->default(null);

            $table->string('additional_info')
                ->after('address')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webinars', function (Blueprint $table) {
            $table->dropColumn('format');
            $table->dropColumn('address');
            $table->dropColumn('additional_info');
        });
    }
};
