# Bugs, inconsistencies, and incomplete UI (static review)

## A) Broken / inconsistent behavior

### 1) `/register/{workshopId?}` route parameter is ignored
- **Where:** `routes/web.php` defines `/register/{workshopId?}` → `RegistrationController@showForm`
- **Issue:** `showForm()` does not accept or use `$workshopId`; it always loads the *first active* workshop.
- **Impact:** Can’t run multiple workshops/links; deep links like `/register/123` don’t work as expected.

### 2) “Registration closed” UX is not implemented
- **Where:** `RegistrationController@showForm()` comment says: “If no active workshop exists, show a friendly ‘closed’ message.”
- **Issue:** `register/form.blade.php` doesn’t handle `$workshop === null`. It renders the form anyway; submission later fails with “Registration is currently closed…”.
- **Impact:** Confusing UX + wasted steps for the user.

### 3) Email uniqueness is global (blocks multi-workshop reuse)
- **Where (DB):** migration `2026_05_15_150949_add_unique_to_email_in_registrations.php` makes `registrations.email` globally unique.
- **Where (validation):** `StoreRegistrationRequest` uses `unique:registrations,email`.
- **Impact:** Same person can’t register for *different* workshops with the same email.
- **Note:** `tests/Feature/RegistrationTest.php` expects the opposite (same email across different workshops should succeed). This is a spec mismatch.

### 4) Tests are out of date vs current registration flow
- **Where:** `tests/Feature/RegistrationTest.php`
- **Issue:** Tests submit registrations without OTP/address and expect session success; current code requires OTP + address and uses a different workshop-selection approach.
- **Impact:** Automated tests do not represent real behavior and will fail once test execution is available.

### 5) Demo seeder references a missing `Admin` model
- **Where:** `database/seeders/WorkshopDemoSeeder.php` imports/uses `App\Models\Admin`
- **Issue:** `app/Models/Admin.php` does not exist (admin uses `User` model).
- **Impact:** Seeding will crash.

### 6) OTP endpoint returns the OTP in API response (security risk)
- **Where:** `RegistrationController@sendOtp()`
- **Issue:** If no active OTP webhooks exist, response JSON includes `"otp": $otp`.
- **Impact:** In production this effectively bypasses OTP security. This should be restricted to local/dev only, or removed.

### 7) “Admin” guard has no role/permission gate
- **Where:** `config/auth.php` defines `admin` guard using the same `users` provider/model as normal users.
- **Issue:** Any user in `users` table could potentially authenticate as “admin” if they have valid credentials.
- **Impact:** Missing authorization boundary (should add `is_admin` or separate admin table/provider).

## B) UI / design gaps (admin & public)

### 1) Admin UI theme is inconsistent on Workshops + Webhooks pages
- **Where:** `resources/views/admin/workshops/index.blade.php`, `resources/views/admin/webhooks/index.blade.php`
- **Issue:** They use plain Bootstrap `<div class="card">` + `thead.bg-light` inside a *dark* admin layout (`layouts/admin.blade.php`).
- **Impact:** Likely unreadable/low-contrast and visually inconsistent vs “content-card/stat-card” styling used elsewhere.

### 2) “Workshop filter” is implemented server-side but missing in the UI
- **Where (server):** `AdminController@registrations()` supports `workshop_id` filtering
- **Where (UI):** `admin/registrations/index.blade.php` has no workshop dropdown
- **Impact:** Feature exists but user can’t access it.

### 3) Public pages mix multiple styling systems
- **Where:** Vite/Tailwind exists, but many screens rely on inline CSS and/or Bootstrap CDN.
- **Impact:** Hard to maintain consistency; bigger CSS payload; future UI work becomes slower.

### 4) Layout vs 100vh pages
- **Where:** `layouts/app.blade.php` wraps content in a “main container” + footer; `register/form` uses `height: 100vh`.
- **Impact:** Can cause overflow/scroll quirks and footer spacing issues on mobile.

## C) Tooling gap (environment)
I attempted to run `php artisan test`, but the runtime environment here does not have `php` installed, so I could not execute tests to confirm failures. The issues above are identified via code inspection.

