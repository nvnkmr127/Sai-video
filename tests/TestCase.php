<?php

namespace Tests;

use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function createWorkshop(array $overrides = []): Workshop
    {
        return Workshop::factory()->create(array_merge(['is_active' => true, 'max_seats' => 120], $overrides));
    }

    protected function createRegistration(Workshop $workshop, array $overrides = []): Registration
    {
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
        Cache::put('otp_' . $normalizedPhone, $otp, 600);
    }
}
