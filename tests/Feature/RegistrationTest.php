<?php

namespace Tests\Feature;

use App\Jobs\RegistrationCreated;
use App\Models\Registration;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_loads_with_active_workshop()
    {
        $workshop = $this->createWorkshop(['title' => 'The Great Workshop']);
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('The Great Workshop');
    }

    public function test_registration_form_shows_closed_when_no_active_workshop()
    {
        Workshop::query()->update(['is_active' => false]);
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Registration Closed');
    }

    public function test_otp_send_returns_success()
    {
        $phone = '+919876543210';
        $response = $this->postJson('/otp/send', ['phone' => $phone]);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $normalizedPhone = preg_replace('/^(\+91|91|0)/', '', str_replace(' ', '', $phone));
        $this->assertTrue(Cache::has('otp_' . $normalizedPhone));
    }

    public function test_otp_send_rate_limited_by_phone()
    {
        $phone = '+910000000000';
        // Send 3 times successfully (route throttle is 3,1)
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/otp/send', ['phone' => $phone])->assertStatus(200);
        }
        // 4th time should fail (hit route throttle)
        $response = $this->postJson('/otp/send', ['phone' => $phone]);
        $response->assertStatus(429);
        $response->assertJsonFragment(['message' => 'Too Many Attempts.']);
    }

    public function test_successful_registration_creates_record_and_redirects()
    {
        $workshop = $this->createWorkshop();
        $phone = '+919876543210';
        $this->setOtp($phone, 123456);
        Queue::fake();

        $response = $this->post('/register', [
            'full_name'   => 'Test User',
            'phone'       => $phone,
            'otp'         => '123456',
            'address'     => '123 Test Street',
            'workshop_id' => $workshop->id,
        ]);

        $registration = Registration::where('phone', $phone)->first();
        $response->assertRedirect(route('registration.success', $registration->qr_code_token));
        $this->assertDatabaseHas('registrations', [
            'phone'       => $phone,
            'workshop_id' => $workshop->id,
            'status'      => 'pending',
        ]);
        Queue::assertNotPushed(RegistrationCreated::class);
        Queue::assertPushed(\App\Jobs\SendWebhookJob::class);

        // Log in as admin and approve the registration
        $admin = $this->loginAdmin();
        $approveResponse = $this->post("/admin/registrations/{$registration->id}/approve");
        $approveResponse->assertRedirect();

        $this->assertEquals('approved', $registration->fresh()->status);
        Queue::assertPushed(RegistrationCreated::class);
    }

    public function test_wrong_otp_fails_registration()
    {
        $workshop = $this->createWorkshop();
        $phone = '+919876543210';
        $this->setOtp($phone, 123456);

        $response = $this->post('/register', [
            'full_name'   => 'Test User',
            'phone'       => $phone,
            'otp'         => '000000',
            'address'     => '123 Test Street',
            'workshop_id' => $workshop->id,
        ]);

        $response->assertSessionHasErrors(['otp']);
    }

    public function test_no_otp_in_cache_fails_registration()
    {
        $workshop = $this->createWorkshop();
        $phone = '+919876543211';
        // Do NOT set OTP in cache

        $response = $this->post('/register', [
            'full_name'   => 'Test User',
            'phone'       => $phone,
            'otp'         => '123456',
            'address'     => '123 Test Street',
            'workshop_id' => $workshop->id,
        ]);

        $response->assertSessionHasErrors(['otp']);
    }

    public function test_duplicate_phone_same_workshop_fails()
    {
        $workshop = $this->createWorkshop();
        $phone = '+919876543210';
        $this->createRegistration($workshop, ['phone' => $phone]);
        $this->setOtp($phone, 123456);

        $response = $this->post('/register', [
            'full_name'   => 'Test User 2',
            'phone'       => $phone,
            'otp'         => '123456',
            'address'     => '123 Test Street',
            'workshop_id' => $workshop->id,
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_registration_fails_when_workshop_is_full()
    {
        $workshop = $this->createWorkshop(['max_seats' => 1]);
        $this->createRegistration($workshop);
        
        $phone = '+918888888888';
        $this->setOtp($phone, 123456);

        $response = $this->post('/register', [
            'full_name'   => 'Waitlist User',
            'phone'       => $phone,
            'otp'         => '123456',
            'address'     => '123 Test Street',
            'workshop_id' => $workshop->id,
        ]);

        $response->assertRedirect(route('registration.index'));
        $response->assertSessionHas('error');
    }

    public function test_missing_required_fields_returns_errors()
    {
        $response = $this->post('/register', []);
        $response->assertSessionHasErrors(['full_name', 'phone', 'otp', 'address', 'workshop_id']);
    }

    public function test_success_page_shows_qr_code()
    {
        $workshop = $this->createWorkshop();
        $registration = $this->createRegistration($workshop, [
            'full_name'    => 'QR Test User',
            'qr_code_path' => 'qrcodes/test.png'
        ]);

        $response = $this->get(route('registration.success', $registration->qr_code_token));
        $response->assertStatus(200);
        $response->assertSee('QR Test User');
    }

    public function test_validator_page_requires_desk_key()
    {
        $correctKey = config('app.desk_secret');
        
        $this->get('/validate')->assertStatus(403);
        $this->get('/validate?key=wrong')->assertStatus(403);
        $this->get('/validate?key=' . $correctKey)->assertStatus(200);
    }

    public function test_qr_scan_checks_in_valid_attendee()
    {
        $workshop = $this->createWorkshop();
        $token = 'test-token-1234';
        $registration = $this->createRegistration($workshop, [
            'qr_code_token' => $token,
            'status'        => 'approved'
        ]);
        $key = config('app.desk_secret');

        $response = $this->postJson('/validate/check', [
            'token' => $token,
            'key'   => $key
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotNull($registration->fresh()->checked_in_at);
    }

    public function test_qr_scan_blocks_unapproved_registration()
    {
        $workshop = $this->createWorkshop();
        $token = 'test-token-unapproved';
        $registration = $this->createRegistration($workshop, [
            'qr_code_token' => $token,
            'status'        => 'pending'
        ]);
        $key = config('app.desk_secret');

        $response = $this->postJson('/validate/check', [
            'token' => $token,
            'key'   => $key
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false, 'code' => 'NOT_APPROVED']);
    }

    public function test_qr_scan_blocks_duplicate_checkin()
    {
        $workshop = $this->createWorkshop();
        $token = 'test-token-dup';
        $this->createRegistration($workshop, [
            'qr_code_token'   => $token,
            'checked_in_at'   => now(),
            'status'          => 'approved'
        ]);
        $key = config('app.desk_secret');

        $response = $this->postJson('/validate/check', [
            'token' => $token,
            'key'   => $key
        ]);

        $response->assertStatus(409);
        $response->assertJson(['success' => false, 'code' => 'ALREADY_CHECKED_IN']);
    }

    public function test_qr_scan_rejects_invalid_token()
    {
        $key = config('app.desk_secret');
        $response = $this->postJson('/validate/check', [
            'token' => 'nonexistent-uuid',
            'key'   => $key
        ]);

        $response->assertStatus(404);
        $response->assertJson(['success' => false, 'code' => 'INVALID_TOKEN']);
    }

    public function test_admin_login_success()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/admin/login', [
            'email'    => $admin->email,
            'password' => 'password123'
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_login_failure()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->post('/admin/login', [
            'email'    => $admin->email,
            'password' => 'wrong-password'
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_admin_dashboard_accessible_when_authenticated()
    {
        $this->loginAdmin();
        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    public function test_admin_registrations_list_shows_phone()
    {
        $this->loginAdmin();
        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, ['phone' => '+911234567890']);

        $response = $this->get('/admin/registrations');
        $response->assertStatus(200);
        $response->assertSee('+911234567890');
    }

    public function test_admin_manual_checkin()
    {
        $admin = $this->loginAdmin();
        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, [
            'status' => 'approved'
        ]);

        $response = $this->postJson("/admin/registrations/{$reg->id}/checkin");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotNull($reg->fresh()->checked_in_at);
        $this->assertEquals($admin->name, $reg->fresh()->checked_in_by);
    }
}
