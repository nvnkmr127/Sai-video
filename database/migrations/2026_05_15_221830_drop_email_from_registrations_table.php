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
        if (! Schema::hasColumn('registrations', 'email')) {
            return;
        }

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropUnique('registrations_email_unique');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('email')->nullable()->after('full_name');
        });
    }
};
