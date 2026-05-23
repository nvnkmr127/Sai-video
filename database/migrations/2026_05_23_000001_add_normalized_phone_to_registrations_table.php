<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('normalized_phone', 32)->nullable()->after('phone');
        });

        DB::table('registrations')
            ->select(['id', 'phone'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $phone = (string) $row->phone;
                    $normalized = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));
                    $normalized = preg_replace('/\D+/', '', (string) $normalized);

                    DB::table('registrations')
                        ->where('id', $row->id)
                        ->update(['normalized_phone' => $normalized ?: null]);
                }
            });

        Schema::table('registrations', function (Blueprint $table) {
            $table->unique(['workshop_id', 'normalized_phone'], 'registrations_workshop_normalized_phone_unique');
            $table->index(['workshop_id', 'status'], 'registrations_workshop_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex('registrations_workshop_status_idx');
            $table->dropUnique('registrations_workshop_normalized_phone_unique');
            $table->dropColumn('normalized_phone');
        });
    }
};
