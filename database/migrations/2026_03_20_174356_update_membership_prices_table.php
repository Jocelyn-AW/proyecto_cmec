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
        Schema::table('membership_prices', function (Blueprint $table) {
            $table->decimal('amount_general', 10, 2)->default(0)->after('end_date');
            $table->decimal('amount_preferential', 10, 2)->default(0)->after('amount_general');

            $table->dropColumn('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_prices', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0);

            $table->dropColumn('amount_general');
            $table->dropColumn('amount_preferential');
        });
    }
};
