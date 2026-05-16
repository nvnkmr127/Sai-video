# Roadmap + Micro-tasks (Photography Workshop Product)

## 0) North-star goals
1. **Frictionless registration** (mobile-first, minimal steps, clear status)
2. **Trustworthy check-in** (fast scans, no duplicates, offline-ish resilience)
3. **Operational clarity** (admin can see capacity, attendance, webhook health)
4. **Photography-specific growth loops** (WhatsApp reminders, consent, delivery)

---

## 1) Enhancements (grouped by impact)

### Phase 1 — Fix correctness + unblock ops (quick wins)
- Implement proper “Registration closed” screen when no active workshop
- Fix workshop selection (support `workshopId` param + workshop dropdown)
- Decide and enforce email uniqueness rule:
  - **Recommended:** unique `(workshop_id, email)` not global email unique
- Fix demo seeder + tests to match current flow
- Remove OTP echo-back in production (security)
- Add workshop filter to admin attendees UI
- Unify admin UI components (use `content-card` styling consistently)

### Phase 2 — Photography workshop product features
- Payment status / “paid” gating of QR activation
- WhatsApp/email confirmations + reminders (via webhook or mail)
- Custom fields per workshop (camera type, experience level, gear checklist)
- Waiver/consent checkbox + captured timestamp/IP
- Capacity management improvements: waiting list, overbooking rules

### Phase 3 — Scale + reliability
- Observability: webhook delivery dashboard, retries, per-config status
- Multi-admin roles (super admin vs desk staff)
- Audit log (who checked-in whom, config edits)
- Rate limit & abuse protections for OTP/registration

---

## 2) Micro-tasks (ready for implementation)
Each micro-task includes acceptance criteria so it can be done independently.

### Epic A — Workshop selection & “closed” UX

**A1. Show “registration closed” page if no active workshop**
- Update `RegistrationController@showForm()`: if no active workshop, return a new view `register/closed.blade.php`
- Create `resources/views/register/closed.blade.php` with:
  - message (“Registrations are closed right now”)
  - optional contact link / next date placeholder
- **Acceptance:** Visiting `/` shows a clean closed page when `workshops.is_active = false` for all rows.

**A2. Support `workshopId` route param**
- Update route/controller to accept `$workshopId` and load that workshop if provided, else active workshop.
- Validate that workshop exists and is open for registration.
- **Acceptance:** `/register/123` shows workshop 123; `/register` shows active workshop; invalid id returns 404 or friendly error.

**A3. Add workshop selector to public form (optional if multiple active)**
- Add a dropdown or workshop “cards” at the start when multiple active workshops exist.
- **Acceptance:** User can pick a workshop without editing the URL; selected workshop persists through steps and submit.

### Epic B — Data model & validation alignment

**B1. Change uniqueness from global email → per workshop**
- Migration:
  - drop unique index on `registrations.email`
  - add composite unique index `(workshop_id, email)`
- Update `StoreRegistrationRequest` rule to `Rule::unique('registrations','email')->where('workshop_id', ...)`
- **Acceptance:** Same email can register for different workshops; duplicate within same workshop fails.

**B2. Bring tests in sync with current registration flow**
- Update `tests/Feature/RegistrationTest.php`:
  - include `otp` and `address` (or disable OTP requirement in tests by mocking webhook config)
  - assert redirect to success page
- **Acceptance:** `php artisan test` passes locally.

**B3. Fix `WorkshopDemoSeeder`**
- Replace `App\Models\Admin` usage with `App\Models\User` (or create an Admin model if desired).
- Ensure required fields are filled (`WebhookConfig.type` is ok due to default).
- **Acceptance:** `php artisan db:seed` runs without errors.

### Epic C — OTP security + UX

**C1. Remove OTP echo-back outside local/dev**
- In `sendOtp()`, only include OTP in response when `app()->environment('local')` (or behind explicit config flag).
- **Acceptance:** In production env, API never returns the OTP; in local dev it can.

**C2. Add resend OTP + error UI**
- Add “Resend code” UI with proper cooldown and messaging.
- Handle rate limits / webhook failure gracefully (toast/banner instead of `alert()`).
- **Acceptance:** User can resend after cooldown; failure messaging is clear.

### Epic D — Admin UI consistency & missing controls

**D1. Add Workshop filter dropdown to attendees list**
- UI: Add `<select name="workshop_id">` using `$workshops` already provided.
- Controller already supports it; ensure it’s wired.
- **Acceptance:** Filtering changes list + stats + export CSV.

**D2. Re-style Workshops + Webhooks pages to match dark admin theme**
- Replace `<div class="card">` with `<div class="content-card">` patterns, or explicitly set Bootstrap cards to dark.
- **Acceptance:** Tables are readable and visually consistent with dashboard/attendees pages.

### Epic E — Admin auth boundary (minimum viable)

**E1. Add `is_admin` flag + restrict admin login**
- Migration: add `users.is_admin` boolean default false
- Seeder: set admin user `is_admin = true`
- LoginController: only allow guard login when `is_admin = true`
- **Acceptance:** Non-admin users cannot access `/admin/*` even with valid credentials.

---

## 3) Suggested sequencing (execution plan)
1. Epic A (closed UX + workshop selection) → reduces user confusion immediately
2. Epic B (uniqueness + tests + seeders) → aligns spec + prevents surprises
3. Epic C (OTP security) → closes security hole
4. Epic D (admin UI fixes) → improves operations
5. Epic E (admin boundary) → hardens access control

