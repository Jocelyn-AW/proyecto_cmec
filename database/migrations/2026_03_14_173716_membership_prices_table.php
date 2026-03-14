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
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('price');
        });

        Schema::create('membership_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')
                ->nullable()                    
                ->default(null)
                ->constrained('memberships')
                ->onDelete('cascade');
            $table->date('start_date')->nullable()->default(null);
            $table->date('end_date')->nullable()->default(null);
            $table->decimal('amount', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->default(null);
        });

        Schema::table('membership_prices', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->dropColumn('membership_id');
        });

        Schema::dropIfExists('membership_prices');

    }
};
