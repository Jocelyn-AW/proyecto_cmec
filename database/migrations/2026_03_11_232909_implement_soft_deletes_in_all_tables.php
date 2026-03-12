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
        'attendees',
        'bank_details',
        'banners',
        'candidates',
        'clinics',
        'conferences',
        'courses',
        'directory_data',
        'event_sessions',
        'event_speakers',
        'invoice_data',
        'members',
        'memberships',
        'news',
        'payment_methods',
        'payments',
        'posts',
        'users',
        'webinars',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
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
                $table->dropSoftDeletes();
            });
        }
    }
};
