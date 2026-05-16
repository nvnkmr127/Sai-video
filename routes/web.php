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
    ->middleware('throttle:5,1')
    ->name('registration.store');

Route::post('/otp/send', [RegistrationController::class, 'sendOtp'])
    ->middleware('throttle:3,1')
    ->name('registration.otp.send');

Route::get('/success/{uuid}', [RegistrationController::class, 'success'])->name('registration.success');
Route::get('/qr-status/{token}', [RegistrationController::class, 'qrStatus'])
    ->middleware('throttle:30,1')
    ->name('registration.qr-status');

// QR Validation (Public with Secret Key)
Route::get('/validate', [RegistrationController::class, 'validator'])->name('registration.validator');
Route::post('/validate/check', [RegistrationController::class, 'validateToken'])
    ->middleware('throttle:60,1')
    ->name('registration.validator.check');
Route::get('/validate/stats', [RegistrationController::class, 'validatorStats'])
    ->middleware('throttle:30,1')
    ->name('registration.validator.stats');
Route::get('/validate/{uuid}', [RegistrationController::class, 'verify'])->name('registration.verify');

// Admin Authentication (Guest Only)
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])
        ->middleware('throttle:5,1');
    
    // Dev Autologin
    if (app()->isLocal()) {
        Route::get('/admin/autologin', function() {
            // Try to find an existing admin
            $user = \App\Models\User::where('is_admin', 1)->first();
            
            // If no admin found, create one for convenience
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('password'),
                    'is_admin' => 1,
                ]);
            }

            if ($user) {
                \Illuminate\Support\Facades\Auth::guard('admin')->login($user);
                return redirect()->route('admin.dashboard');
            }
            
            return 'Could not find or create an admin user.';
        })->name('admin.autologin');
    }
});

// Admin Portal (Protected)
Route::prefix('admin')->middleware(['auth:admin', 'is_admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/registrations', [AdminController::class, 'registrations'])->name('admin.registrations.index');
    Route::get('/registrations/export', [AdminController::class, 'exportRegistrations'])->name('admin.registrations.export');
    Route::get('/registrations/{id}', [AdminController::class, 'show'])->name('admin.registrations.show');
    Route::post('/registrations/{id}/checkin', [AdminController::class, 'manualCheckin'])->name('admin.registrations.checkin');
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
