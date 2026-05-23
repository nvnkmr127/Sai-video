<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_throttles', function (Blueprint $table) {
            $table->string('normalized_phone', 32)->primary();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('reset_at')->index();
            $table->timestamps();
        });

        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('normalized_phone', 32)->index();
            $table->string('otp_hash', 64);
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
        Schema::dropIfExists('otp_throttles');
    }
};
