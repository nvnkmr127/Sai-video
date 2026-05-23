<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\WorkshopController;
use App\Http\Controllers\Admin\WebhookConfigController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

// Public Registration
Route::get('/', [RegistrationController::class, 'showForm'])->name('registration.index');
Route::get('/register/{workshopId?}', [RegistrationController::class, 'showForm'])->name('registration.form');
Route::post('/register', [RegistrationController::class, 'submit'])
    ->name('registration.store');

Route::post('/otp/send', [RegistrationController::class, 'sendOtp'])
    ->name('registration.otp.send');

Route::post('/registration/check-duplicate', [RegistrationController::class, 'checkDuplicate'])
    ->middleware('throttle:10,1')
    ->name('registration.check-duplicate');

Route::get('/success/{uuid}', [RegistrationController::class, 'success'])->name('registration.success');
Route::get('/qr-status/{token}', [RegistrationController::class, 'qrStatus'])
    ->middleware('throttle:30,1')
    ->name('registration.qr-status');

// QR Validation (Public with Secret Key)
Route::get('/validate', [RegistrationController::class, 'validator'])->name('registration.validator');
Route::post('/validate/check', [RegistrationController::class, 'validateToken'])
    ->name('registration.validator.check');
Route::get('/validate/stats', [RegistrationController::class, 'validatorStats'])
    ->name('registration.validator.stats');
Route::get('/validate/{uuid}', [RegistrationController::class, 'verify'])->name('registration.verify');

// Admin Authentication (Guest Only)
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])
        ->middleware('throttle:5,1');
    
    // Dev Autologin
    Route::get('/admin/autologin', function () {
        $enabled = filter_var(env('DEV_AUTOLOGIN_ENABLED', false), FILTER_VALIDATE_BOOL);
        $allowInProduction = filter_var(env('DEV_AUTOLOGIN_ALLOW_PRODUCTION', false), FILTER_VALIDATE_BOOL);

        $isProduction = app()->environment('production');
        if ($isProduction && !$allowInProduction) {
            abort(404);
        }

        if (!$enabled) {
            abort($isProduction ? 404 : 403, 'DEV_AUTOLOGIN_ENABLED must be set to true.');
        }

        if (!config('app.debug') && !$allowInProduction) {
            abort($isProduction ? 404 : 403, 'APP_DEBUG must be set to true.');
        }

        $allowedIps = array_values(array_filter(array_map('trim', explode(',', (string) env('DEV_AUTOLOGIN_ALLOWED_IPS', '127.0.0.1,::1')))));
        if ($allowedIps && !in_array(request()->ip(), $allowedIps, true)) {
            abort($isProduction ? 404 : 403, 'IP not allowed for autologin.');
        }

        $email = env('DEV_AUTOLOGIN_EMAIL', 'admin@example.com');
        $password = env('DEV_AUTOLOGIN_PASSWORD', 'password');

        $user = \App\Models\User::where('is_admin', 1)->first();
        if (!$user) {
            $user = \App\Models\User::create([
                'name' => 'Admin User',
                'email' => $email,
                'password' => bcrypt($password),
                'is_admin' => 1,
            ]);
        }

        \Illuminate\Support\Facades\Auth::guard('admin')->login($user);
        return redirect()->route('admin.dashboard');
    })->name('admin.autologin');
});

// Admin Portal (Protected)
Route::prefix('admin')->middleware(['auth:admin', 'is_admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/registrations', [AdminController::class, 'registrations'])->name('admin.registrations.index');
    Route::post('/registrations/{id}/approve', [AdminController::class, 'approve'])->name('admin.registrations.approve');
    Route::get('/registrations/export', [AdminController::class, 'exportRegistrations'])->name('admin.registrations.export');
    Route::get('/registrations/{id}', [AdminController::class, 'show'])->name('admin.registrations.show');
    Route::put('/registrations/{id}', [AdminController::class, 'update'])->name('admin.registrations.update');
    Route::post('/registrations/{id}/checkin', [AdminController::class, 'manualCheckin'])->name('admin.registrations.checkin');
    Route::post('/registrations/{id}/uncheckin', [AdminController::class, 'manualUncheckin'])->name('admin.registrations.uncheckin');
    Route::get('/registrations/live', [AdminController::class, 'liveRegistrations'])->name('admin.registrations.live');
    Route::get('/registrations/live-stats', [AdminController::class, 'liveRegistrationsStats'])->name('admin.registrations.live-stats');
    Route::get('/registrations/{id}/live', [AdminController::class, 'liveRegistration'])->name('admin.registrations.live-one');
    Route::delete('/registrations/{id}', [AdminController::class, 'destroy'])->name('admin.registrations.destroy');
    Route::post('/registrations/{id}/resend-webhook', [AdminController::class, 'resendWebhook'])->name('admin.registrations.resend-webhook');
    Route::post('/checkin/{uuid}', [AdminController::class, 'checkin'])->name('admin.checkin');

    // Workshops CRUD
    Route::resource('workshops', WorkshopController::class)->names('admin.workshops');

    // Webhook Configs CRUD
    Route::resource('webhooks', WebhookConfigController::class)->names('admin.webhooks');
    Route::post('webhooks/{webhook}/test', [WebhookConfigController::class, 'test'])
        ->middleware('throttle:5,1')
        ->name('admin.webhooks.test');
    
    // Webhook Logs
    Route::get('webhook-logs', [App\Http\Controllers\Admin\WebhookLogController::class, 'index'])->name('admin.webhooks.logs');
    Route::get('webhook-logs/{log}', [App\Http\Controllers\Admin\WebhookLogController::class, 'show'])->name('admin.webhooks.log-show');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('settings/remove-slider', [SettingsController::class, 'removeSliderImage'])->name('admin.settings.remove-slider');

    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('admin.logout');
});
