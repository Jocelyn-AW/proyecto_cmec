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
            $table->dropColumn('date');
            $table->dropColumn('specialties');
        
            $table->decimal('surgeon_price', 10, 2)
                ->nullable()
                ->default(null)
                ->after('resident_price');
            
                $table->decimal('nurse_price', 10, 2)
                ->nullable()
                ->default(null)
                ->after('surgeon_price');
            
            $table->text('description')->nullable()->default(null)->after('main_topic');
            $table->string('google_coords')->nullable()->default(null)->change();
            $table->string('link')->nullable()->default(null)->after('google_coords');
            $table->boolean('is_active')->default(true)->after('nurse_price');

            $table->foreignId('bank_detail_id')
                ->nullable()                    
                ->default(null)                 
                ->after('is_active')
                ->constrained('bank_details')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conferences', function (Blueprint $table) {
            $table->datetime('date')->nullable()->default(null);
            $table->string('specialties')->nullable()->default(null);
            $table->dropColumn('surgeon_price');
            $table->dropColumn('nurse_price');
            $table->dropColumn('link');
            $table->dropColumn('is_active');
            $table->dropColumn('description');
            $table->dropForeign(['bank_detail_id']);
            $table->dropColumn('bank_detail_id');
        });
    }
};
