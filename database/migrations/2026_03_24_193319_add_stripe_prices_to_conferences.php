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
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_member_id')->nullable();
            $table->string('stripe_price_guest_id')->nullable();
            $table->string('stripe_price_resident_id')->nullable();
            $table->string('stripe_price_surgeon_id')->nullable();
            $table->string('stripe_price_nurse_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->dropColumn([
                'stripe_product_id',
                'stripe_price_member_id',
                'stripe_price_guest_id',
                'stripe_price_resident_id',
                'stripe_price_surgeon_id',
                'stripe_price_nurse_id',
            ]);
        });
    }
};
