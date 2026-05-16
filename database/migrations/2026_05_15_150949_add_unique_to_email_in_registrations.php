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
        Schema::table('registrations', function (Blueprint $table) {
            // Drop the plain index, replace with unique constraint
            $table->dropIndex('registrations_email_index');
            $table->unique('email', 'registrations_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique('registrations_email_unique');
            $table->index('email', 'registrations_email_index');
        });
    }
};
