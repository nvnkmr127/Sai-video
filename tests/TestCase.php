<?php

namespace Tests;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function createWorkshop(array $overrides = []): Workshop
    {
        return Workshop::factory()->create(array_merge(['is_active' => true, 'max_seats' => 120], $overrides));
    }

    protected function createRegistration(Workshop $workshop, array $overrides = []): Registration
    {
        if (array_key_exists('phone', $overrides) && !array_key_exists('normalized_phone', $overrides)) {
            $normalized = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', (string) $overrides['phone']));
            $overrides['normalized_phone'] = preg_replace('/\D+/', '', (string) $normalized);
        }

        return Registration::factory()->create(array_merge(['workshop_id' => $workshop->id], $overrides));
    }

    protected function loginAdmin(): User
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    protected function setOtp(string $phone, int $otp = 123456): void
    {
        $normalizedPhone = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));
        $normalizedPhone = preg_replace('/\D+/', '', (string) $normalizedPhone);

        DB::table('otp_codes')->where('normalized_phone', $normalizedPhone)->delete();
        DB::table('otp_codes')->insert([
            'normalized_phone' => $normalizedPhone,
            'otp_hash' => hash_hmac('sha256', $normalizedPhone . '|' . (string) $otp, (string) config('app.key')),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
