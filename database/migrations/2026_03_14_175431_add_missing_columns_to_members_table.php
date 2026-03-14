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
        Schema::dropIfExists('clinics');
        Schema::dropIfExists('members');

        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->string('cmec_member_id')->nullable()->default(null);
            $table->string('name');
            $table->string('last_name')->nullable()->default(null);
            $table->string('phone');
            $table->string('email');
            $table->string('city')->nullable()->default(null);
            $table->string('state')->nullable()->default(null);
            $table->text('hospital')->nullable()->default(null);
            $table->date('inscription_date')->nullable()->default(null);
            $table->date('expiration_date')->nullable()->default(null);

            $table->foreignId('user_id')
                ->nullable()                    
                ->default(null)
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('invoice_data_id')
                ->nullable()                    
                ->default(null)
                ->constrained('invoice_data')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
