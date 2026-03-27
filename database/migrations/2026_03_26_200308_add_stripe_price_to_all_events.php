<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    protected $tables = [
        'academic_sessions',
        'courses',
        'webinars',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('stripe_product_id')->nullable();
                $table->string('stripe_price_member_id')->nullable();
                $table->string('stripe_price_guest_id')->nullable();
                $table->string('stripe_price_resident_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn([
                    'stripe_product_id',
                    'stripe_price_member_id',
                    'stripe_price_guest_id',
                    'stripe_price_resident_id',
                ]);
            });
        }
    }
};
