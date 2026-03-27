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
            $table->string('stripe_price_general_id')->nullable();
            $table->string('stripe_price_preferential_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_prices', function (Blueprint $table) {
            $table->dropColumn(['stripe_price_general_id', 'stripe_price_preferential_id']);
        });
    }
};
