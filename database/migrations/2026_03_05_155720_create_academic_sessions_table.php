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
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();

            $table->string('topic');
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->string('duration')->nullable();

            $table->string('organized_by')->nullable();
            $table->string('sponsored_by')->nullable();

            $table->foreignId('bank_detail_id')
                ->nullable()
                ->constrained('bank_details')
                ->nullOnDelete();

            $table->decimal('member_price', 10, 2)->nullable();
            $table->decimal('guest_price', 10, 2)->nullable();
            $table->decimal('resident_price', 10, 2)->nullable();

            $table->string('link')->nullable();

            $table->boolean('is_active')->default(true);

            $table->string('format')->nullable();
            $table->string('address')->nullable();
            $table->string('additional_info')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};
