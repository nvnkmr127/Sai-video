# Prompts for a “Vibe Coding” Agent (Laravel 13 / WorkshopPro)

Use these prompts with Cursor/Claude/Copilot agents. They’re written to drive consistent changes: update code + UI + tests together.

---

## Prompt 0 — Project context (paste once at the top of your agent session)
You are working in a Laravel 13 app called **WorkshopPro** (PHP 8.3). The product is a **photography workshop registration + QR pass + check-in scanner + admin portal**.

Key files:
- Routes: `routes/web.php`
- Public registration: `app/Http/Controllers/RegistrationController.php`, `app/Http/Requests/StoreRegistrationRequest.php`, `resources/views/register/*`
- Admin: `app/Http/Controllers/AdminController.php`, `resources/views/admin/*`, `app/Http/Controllers/Admin/*`
- Jobs: `app/Jobs/GenerateAndSendQrCode.php`, `app/Jobs/SendWebhookJob.php`
- Models: `app/Models/{Workshop,Registration,WebhookConfig,WebhookLog,User}.php`

Constraints:
- Prefer small, reviewable commits.
- Don’t change unrelated formatting.
- When you change behavior, update tests and seeders accordingly.
- Keep UI consistent with `resources/views/layouts/admin.blade.php` (dark theme, uses `content-card`/`stat-card`).

Definition of done for each task:
1) feature works end-to-end, 2) validation/messages are user-friendly, 3) tests updated or added, 4) no broken routes/views, 5) code is secure-by-default.

---

## Prompt 1 — Fix “Registration closed” UX
Implement a proper “registrations closed” screen when there is no active workshop.

Requirements:
- If `Workshop::where('is_active', true)->first()` is null, render a new Blade view `resources/views/register/closed.blade.php`.
- Closed page should be minimal, mobile-first, and match the public style in `layouts/app.blade.php`.
- The registration form should not render when registrations are closed.
- Add/adjust a feature test to cover the closed-page behavior.

Deliverables:
- Code changes (controller + new view + test)
- Brief note in PR description: how to simulate closed mode locally.

---

## Prompt 2 — Make workshop selection real (`/register/{workshopId}`)
Implement workshop selection properly.

Requirements:
- `GET /register/{workshopId?}` must show:
  - the requested workshop if present and active (or, if inactive, show a friendly “closed for this workshop” message)
  - otherwise the default active workshop
- `POST /register` must register *for the selected workshop*, not “first active workshop”.
- Ensure capacity check is done for the chosen workshop.
- Update validation to require `workshop_id` (hidden field in the form) and ensure it exists.

Tests:
- Add tests for:
  - registering for workshop A vs workshop B
  - invalid workshop id
  - seat capacity full

---

## Prompt 3 — Fix email uniqueness to be per-workshop
Align the system so the same email can register for different workshops, but not twice for the same one.

Requirements:
- Database: drop global unique constraint on `registrations.email`, add composite unique `(workshop_id, email)`.
- Validation: update `StoreRegistrationRequest` to enforce the same composite uniqueness.
- Update any impacted code paths and tests.

Acceptance:
- Two registrations with same email but different `workshop_id` succeed.
- Duplicate within same workshop fails with a clear error message.

---

## Prompt 4 — Remove OTP echo-back in production
Harden OTP handling.

Requirements:
- `RegistrationController@sendOtp()` must **never** return OTP in the response unless the environment is explicitly local/dev (choose one: `app()->environment('local')` or a config flag).
- Make error handling user-friendly: return consistent JSON schema `{ success, message }`.
- Add a small test for the “OTP not returned” behavior (if feasible).

---

## Prompt 5 — Fix admin UI inconsistencies (Workshops + Webhooks)
Bring admin UI pages in line with the dark layout theme.

Requirements:
- Update:
  - `resources/views/admin/workshops/index.blade.php`
  - `resources/views/admin/webhooks/index.blade.php`
- Replace plain Bootstrap `card` usage with the existing `content-card` look, and ensure tables have readable contrast.
- Keep layout responsive and consistent with `admin/registrations/index.blade.php`.

Acceptance:
- Pages are readable on dark background with consistent spacing/typography.

---

## Prompt 6 — Add workshop filter to admin attendees list
Expose already-implemented backend filtering in the UI.

Requirements:
- In `admin/registrations/index.blade.php`, add a “Workshop” dropdown using `$workshops`.
- Preserve `search`, `status`, and `workshop_id` query params across pagination and export.
- Ensure scoped stats reflect the filters.

---

## Prompt 7 — Fix seeders to run cleanly
Make demo data seeding work.

Requirements:
- `WorkshopDemoSeeder` currently references a missing `App\\Models\\Admin`. Replace with `App\\Models\\User` OR create an Admin model/table (choose minimal change).
- Ensure seeded admin can log in via `/admin/login`.
- Seed at least:
  - one active workshop
  - one registration
  - one webhook config

Acceptance:
- `php artisan migrate:fresh --seed` succeeds locally.

