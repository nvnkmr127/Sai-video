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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone');
            $table->string('organization')->nullable();
            $table->string('qr_code_token')->unique();
            $table->string('qr_code_path')->nullable();
            $table->timestamp('webhook_sent_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->string('checked_in_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
