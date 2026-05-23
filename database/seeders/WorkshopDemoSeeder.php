<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Registration;
use App\Models\Workshop;
use App\Models\WebhookConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkshopDemoSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Admin using User model
        User::updateOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Demo Admin',
                'password' => bcrypt('password123'),
                'is_admin' => true,
            ]
        );

        // 2. Create Workshops
        $activeWorkshop = Workshop::updateOrCreate(
            ['title' => 'Laravel Masterclass'],
            [
                'description' => 'A deep dive into Laravel internals.',
                'date' => now()->addDays(7),
                'starts_at' => now()->addDays(7)->setTime(10, 0, 0),
                'location' => 'Main Hall A',
                'max_seats' => 50,
                'is_active' => true,
            ]
        );

        Workshop::updateOrCreate(
            ['title' => 'Old PHP Workshop'],
            [
                'description' => 'Legacy PHP patterns.',
                'date' => now()->subMonths(2),
                'starts_at' => now()->subMonths(2)->setTime(14, 30, 0),
                'location' => 'Basement Room 4',
                'max_seats' => 20,
                'is_active' => false,
            ]
        );

        // 3. Create Webhook Configs
        WebhookConfig::updateOrCreate(
            ['name' => 'Testing Endpoint'],
            [
                'url' => 'https://webhook.site/test-uuid',
                'secret_token' => Str::random(32),
                'is_active' => true,
            ]
        );

        // 4. Create sample registrations
        $attendees = [
            ['full_name' => 'Alice Smith', 'phone' => '1234567891'],
            ['full_name' => 'Bob Jones', 'phone' => '1234567892'],
            ['full_name' => 'Charlie Brown', 'phone' => '1234567893'],
        ];

        foreach ($attendees as $attendee) {
            Registration::updateOrCreate(
                ['phone' => $attendee['phone']],
                array_merge($attendee, [
                    'workshop_id' => $activeWorkshop->id,
                    'qr_code_token' => (string) Str::uuid(),
                    'organization' => 'Demo Corp',
                    'address' => '123 Demo Street, Suite 100',
                ])
            );
        }
    }
}
