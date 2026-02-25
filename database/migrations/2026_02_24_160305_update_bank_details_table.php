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
        Schema::table('bank_details', function (Blueprint $table) {


            $table->dropColumn(['event_id', 'event_type']);

            $table->string('account_number')->nullable()->change();
            $table->string('clabe_number')->nullable()->change();

            $table->foreignId('updated_by')
                ->nullable()
                ->after('subsidiary')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_details', function (Blueprint $table) {

            $table->morphs('event');

            $table->dropForeign(['updated_by']);
            $table->dropColumn('updated_by');

            $table->string('account_number')->nullable(false)->change();
            $table->string('clabe_number')->nullable(false)->change();
        });
    }
};
