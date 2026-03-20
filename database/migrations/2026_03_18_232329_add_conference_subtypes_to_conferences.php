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
        Schema::table('conferences', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->after('id')
                ->nullable()
                ->constrained('conferences')
                ->onDelete('cascade');

            $table->enum('subtype', ['conference', 'pre_conference', 'trans_conference'])
                ->after('parent_id')
                ->default('conference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropColumn('subtype');
        });
    }
};
