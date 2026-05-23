<?php

namespace Tests\Feature;

use App\Jobs\RegistrationCreated;
use App\Models\Registration;
use App\Models\Workshop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_autologin_route_is_registered_but_disabled_by_default()
    {
        $this->assertTrue(Route::has('admin.autologin'));
        $this->get('/admin/autologin')->assertStatus(404);
    }

    public function test_database_seeder_does_not_create_default_admin_in_production()
    {
        app()->detectEnvironment(fn () => 'production');
        $this->artisan('db:seed', ['--class' => \Database\Seeders\DatabaseSeeder::class, '--force' => true])->assertExitCode(0);
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

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
        $normalizedPhone = preg_replace('/\D+/', '', (string) $normalizedPhone);
        $this->assertTrue(DB::table('otp_codes')->where('normalized_phone', $normalizedPhone)->exists());
    }

    public function test_otp_send_returns_error_when_delivery_not_configured_in_production()
    {
        app()->detectEnvironment(fn () => 'production');
        $this->withoutMiddleware();

        $phone = '+919876543210';
        $response = $this->postJson('/otp/send', ['phone' => $phone]);
        $response->assertStatus(503);
        $response->assertJson(['success' => false]);
    }

    public function test_otp_send_rate_limited_by_phone()
    {
        $phone = '+910000000000';
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/otp/send', ['phone' => $phone])->assertStatus(200);
        }
        $response = $this->postJson('/otp/send', ['phone' => $phone]);
        $response->assertStatus(429);
        $response->assertJsonFragment(['message' => 'Too many OTP requests for this number. Please wait 10 minutes.']);
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

    public function test_admin_cannot_approve_more_than_capacity()
    {
        $workshop = $this->createWorkshop(['max_seats' => 1]);
        $this->createRegistration($workshop, ['status' => 'approved']);
        $pending = $this->createRegistration($workshop, ['status' => 'pending']);

        Queue::fake();
        $this->loginAdmin();

        $response = $this->post("/admin/registrations/{$pending->id}/approve");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertEquals('pending', $pending->fresh()->status);
        Queue::assertNotPushed(RegistrationCreated::class);
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

    public function test_registration_allows_waitlist_when_workshop_is_full()
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

        $registration = Registration::where('phone', $phone)->first();
        $response->assertRedirect(route('registration.success', $registration->qr_code_token));
        $this->assertDatabaseHas('registrations', [
            'phone'       => $phone,
            'workshop_id' => $workshop->id,
            'status'      => 'pending',
        ]);
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
        config(['app.desk_secret' => 'test-secret']);
        $correctKey = config('app.desk_secret');
        
        $this->get('/validate')->assertStatus(403);
        $this->get('/validate?key=wrong')->assertStatus(403);
        $this->get('/validate?key=' . $correctKey)->assertStatus(200);
        $this->get('/validate?key=DESK_SECRET')->assertStatus(403);
    }

    public function test_validator_page_allows_admin_without_desk_key()
    {
        config(['app.desk_secret' => 'test-secret']);
        $this->loginAdmin();
        $this->get('/validate')->assertStatus(200);
    }

    public function test_qr_scan_checks_in_valid_attendee()
    {
        config(['app.desk_secret' => 'test-secret']);
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
        config(['app.desk_secret' => 'test-secret']);
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
        config(['app.desk_secret' => 'test-secret']);
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
        config(['app.desk_secret' => 'test-secret']);
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

    public function test_webhook_approved_payload_contains_links()
    {
        $workshop = $this->createWorkshop(['location' => 'Photo Studio Chicago']);
        $reg = $this->createRegistration($workshop, [
            'qr_code_token' => 'ABC123_TEST',
            'status' => 'approved'
        ]);

        // Create an active WebhookConfig
        $config = \App\Models\WebhookConfig::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'secret_token' => 'secret',
            'type' => 'registration_approved',
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Http::fake();

        // Instantiate and run the job
        $job = new \App\Jobs\SendWebhookJob($reg, $config->id, 'registration.approved');
        $job->handle();

        \Illuminate\Support\Facades\Http::assertSent(function ($request) use ($reg) {
            $data = $request->data();
            return $data['event'] === 'registration.approved'
                && $data['event_location_link'] === 'https://www.google.com/maps/search/?api=1&query=Photo+Studio+Chicago'
                && $data['online_pass_url'] === route('registration.success', ['uuid' => $reg->qr_code_token])
                && $data['online_view_of_pass'] === route('registration.success', ['uuid' => $reg->qr_code_token]);
        });
    }

    public function test_webhook_approved_payload_contains_custom_location_link()
    {
        $workshop = $this->createWorkshop([
            'location' => 'Photo Studio Chicago',
            'location_link' => 'https://custom-studio-maps.com/xyz'
        ]);
        $reg = $this->createRegistration($workshop, [
            'qr_code_token' => 'ABC123_TEST_CUSTOM',
            'status' => 'approved'
        ]);

        // Create an active WebhookConfig
        $config = \App\Models\WebhookConfig::create([
            'name' => 'Test Webhook Custom',
            'url' => 'https://example.com/webhook',
            'secret_token' => 'secret',
            'type' => 'registration_approved',
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Http::fake();

        // Instantiate and run the job
        $job = new \App\Jobs\SendWebhookJob($reg, $config->id, 'registration.approved');
        $job->handle();

        \Illuminate\Support\Facades\Http::assertSent(function ($request) use ($reg) {
            $data = $request->data();
            return $data['event'] === 'registration.approved'
                && $data['event_location_link'] === 'https://custom-studio-maps.com/xyz'
                && $data['workshop_location_link'] === 'https://custom-studio-maps.com/xyz'
                && $data['online_pass_url'] === route('registration.success', ['uuid' => $reg->qr_code_token])
                && $data['online_view_of_pass'] === route('registration.success', ['uuid' => $reg->qr_code_token]);
        });
    }

    public function test_webhook_204_is_treated_as_success_for_deduplication()
    {
        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, [
            'qr_code_token' => 'DEDUP_204',
            'status' => 'pending',
        ]);

        $config = \App\Models\WebhookConfig::create([
            'name' => 'Test Pending Webhook',
            'url' => 'https://example.com/webhook',
            'secret_token' => 'secret',
            'type' => 'registration_pending',
            'is_active' => true,
        ]);

        \App\Models\WebhookLog::create([
            'webhook_config_id' => $config->id,
            'registration_id' => $reg->id,
            'payload' => ['event' => 'registration.pending'],
            'response_status' => 204,
            'response_body' => null,
            'sent_at' => now(),
        ]);

        \Illuminate\Support\Facades\Http::fake();

        $job = new \App\Jobs\SendWebhookJob($reg, null, 'registration.pending');
        $job->handle();

        \Illuminate\Support\Facades\Http::assertNothingSent();
    }

    public function test_retry_failed_webhooks_ignores_successful_2xx()
    {
        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, [
            'qr_code_token' => 'RETRY_2XX',
            'status' => 'pending',
        ]);

        $config = \App\Models\WebhookConfig::create([
            'name' => 'Test Pending Webhook',
            'url' => 'https://example.com/webhook',
            'secret_token' => 'secret',
            'type' => 'registration_pending',
            'is_active' => true,
        ]);

        \App\Models\WebhookLog::create([
            'webhook_config_id' => $config->id,
            'registration_id' => $reg->id,
            'payload' => ['event' => 'registration.pending'],
            'response_status' => 204,
            'response_body' => null,
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \App\Models\WebhookLog::create([
            'webhook_config_id' => $config->id,
            'registration_id' => $reg->id,
            'payload' => ['event' => 'registration.pending'],
            'response_status' => 500,
            'response_body' => 'fail',
            'sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Illuminate\Support\Facades\Queue::fake();

        $this->artisan('webhooks:retry-failed')->assertExitCode(0);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendWebhookJob::class, 1);
    }

    public function test_webhook_does_not_include_qr_base64_by_default_but_includes_url()
    {
        config(['services.webhooks.include_qr_base64' => false]);

        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, [
            'qr_code_token' => 'QR_NO_B64',
            'qr_code_path' => 'qrcodes/QR_NO_B64.svg',
            'status' => 'approved',
        ]);

        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('qrcodes/QR_NO_B64.svg', '<svg></svg>');

        $config = \App\Models\WebhookConfig::create([
            'name' => 'Test Webhook',
            'url' => 'https://example.com/webhook',
            'secret_token' => 'secret',
            'type' => 'registration_approved',
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Http::fake();

        $job = new \App\Jobs\SendWebhookJob($reg, $config->id, 'registration.approved');
        $job->handle();

        \Illuminate\Support\Facades\Http::assertSent(function ($request) {
            $data = $request->data();
            return array_key_exists('qr_code_image_base64', $data)
                && $data['qr_code_image_base64'] === null
                && is_string($data['qr_code_image_url'])
                && $data['qr_code_image_url'] !== '';
        });
    }

    public function test_qr_jobs_use_dedicated_queues()
    {
        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, ['status' => 'approved']);

        $qrJob = new \App\Jobs\GenerateAndSendQrCode($reg);
        $webhookJob = new \App\Jobs\SendWebhookJob($reg);

        $this->assertEquals('qr', $qrJob->queue);
        $this->assertEquals('webhooks', $webhookJob->queue);
    }

    public function test_qr_status_ready_when_path_present_without_storage_check()
    {
        $workshop = $this->createWorkshop();
        $reg = $this->createRegistration($workshop, [
            'qr_code_token' => 'QR_STATUS_READY',
            'qr_code_path' => 'qrcodes/QR_STATUS_READY.svg',
            'status' => 'approved',
        ]);

        $response = $this->get(route('registration.qr-status', ['token' => $reg->qr_code_token]));
        $response->assertStatus(200);
        $response->assertJson([
            'ready' => true,
            'url' => '/storage/qrcodes/QR_STATUS_READY.svg',
        ]);
    }
}
