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
        Schema::table('invoice_data', function (Blueprint $table) {

            $table->enum('person_type', ['fisica', 'moral'])
                ->after('postal_code')
                ->default('fisica');

            $table->string('billable_type')
                ->after('id')
                ->nullable()
                ->default(null);

            $table->unsignedBigInteger('billable_id')
                ->after('billable_type')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_data', function (Blueprint $table) {
            $table->dropColumn('person_type');
            $table->dropColumn('billable_id');
            $table->dropColumn('billable_type');
        });
    }
};
