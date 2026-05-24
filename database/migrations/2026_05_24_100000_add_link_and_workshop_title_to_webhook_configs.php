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
        Schema::table('webhook_configs', function (Blueprint $table) {
            $table->string('link')->nullable()->after('type');
            $table->string('workshop_title')->nullable()->after('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_configs', function (Blueprint $table) {
            $table->dropColumn(['link', 'workshop_title']);
        });
    }
};
