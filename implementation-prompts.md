# Implementation Prompts (copy/paste into your coding agent)

These prompts are written to implement your exact workflow in this Laravel 13 codebase.  
Use **one prompt per task** so the agent produces small, reviewable diffs.

---

## Prompt 0 — Bootstrap context (paste once at the start of the agent session)
You are working in a Laravel 13 (PHP 8.3) app called **WorkshopPro**.

Workflow to implement:
1) User requests OTP → OTP sent via WhatsApp provider (JSON webhook)  
2) User enters OTP → OTP verified server-side  
3) Registration created and visible in admin  
4) QR code generated (PNG) for each registration  
5) System sends `registration.created` webhook to WhatsApp provider including QR image URL (and optionally base64)  
6) On workshop day, staff scans QR to check-in (prevent duplicates)

Constraints:
- One active workshop only (admin config).
- Mobile responsive public UI with brand header/footer/images (admin configurable).
- Webhook calls must be signed with HMAC using the configured secret.
- Never return OTP in API responses except local dev.

Key files:
- `routes/web.php`
- `app/Http/Controllers/RegistrationController.php`
- `app/Http/Requests/StoreRegistrationRequest.php`
- `resources/views/register/form.blade.php`
- `app/Jobs/GenerateAndSendQrCode.php`, `app/Jobs/SendWebhookJob.php`
- `app/Models/{Workshop,Registration,WebhookConfig,WebhookLog,User}.php`
- `resources/views/layouts/admin.blade.php`, `resources/views/admin/*`

Definition of done for each change:
1) code compiles, 2) UI works on mobile, 3) tests updated/added, 4) admin UI consistent, 5) security by default.

---

## Prompt 1 — OTP: production-safe send endpoint (no OTP leaks)
Task: Harden `POST /otp/send` (RegistrationController@sendOtp).

Requirements:
- Keep generating a 6-digit OTP and storing it in cache for 10 minutes under key `otp_{phone}`.
- Send OTP via all active `WebhookConfig` records where `type = 'otp'`.
- Response JSON schema must be stable: `{ success: boolean, message: string }`.
- **Never** include the OTP value in the response unless `app()->environment('local')` is true (local dev convenience).
- Add server throttling for OTP requests by phone number (e.g. 3 requests per 10 minutes) with a clear error message.

Acceptance:
- In non-local env the OTP is not returned in JSON.
- Throttling works and returns HTTP 429 with `{success:false}`.

Files likely touched:
- `app/Http/Controllers/RegistrationController.php`
- optionally routes/middleware or use RateLimiter
- add/update tests in `tests/Feature/*`

---

## Prompt 2 — Registration submit must require OTP verification
Task: Ensure registration can only be created if OTP is valid when OTP webhooks are active.

Requirements:
- In `RegistrationController@submit`, enforce:
  - if any active OTP webhook exists, OTP must be present in request and match cache
  - OTP must be single-use: delete from cache after successful verification
- Keep current behavior that only one workshop is active; register into that workshop.
- Improve validation errors so the OTP error is shown in the form (not only generic error banners).

Acceptance:
- With active OTP webhook config: wrong/expired OTP blocks submission.
- With no OTP webhook config in local dev: allow fallback for testing (optional), but document it clearly in code.

Files likely touched:
- `StoreRegistrationRequest.php` (rules/messages)
- `RegistrationController.php`
- `resources/views/register/form.blade.php` (show OTP errors)
- tests

---

## Prompt 3 — Webhook signing (HMAC) for OTP + registration events
Task: Add an HMAC signature header for all outbound webhooks.

Requirements:
- For every webhook POST (OTP + registration), compute signature:
  - `signature = hash_hmac('sha256', raw_json_payload, webhook_secret_token)`
  - Send header: `X-Signature: sha256=<signature>`
- Keep existing headers (`X-Webhook-Secret`, `X-Event`) if needed; do not remove unless redundant.
- Ensure JSON encoding is deterministic for signing (use the same encoded string you send).
- Log the signature and payload hash only (not OTP) to avoid leaking OTP in logs.

Acceptance:
- All webhook deliveries include `X-Signature`.
- No OTP is logged.

Files likely touched:
- `RegistrationController@sendOtp`
- `app/Jobs/SendWebhookJob.php`

---

## Prompt 4 — Registration webhook payload tailored for WhatsApp provider
Task: Ensure `registration.created` webhook includes everything the provider needs to send confirmation + QR.

Requirements:
- Keep QR pipeline: generate QR PNG first, then send webhook.
- Payload must include at least:
  - attendee name, phone, email
  - workshop title/date/location
  - `qr_code_token`
  - `qr_code_image_url` (preferred)
  - optionally `qr_code_image_base64`
- Add a “message_variables” object so provider can map into templates, e.g.
  - `{ "name": "...", "date": "...", "location": "...", "qr_url": "..." }`

Acceptance:
- Provider receives a single POST per active registration webhook config after QR exists.
- Duplicate prevention still works (WebhookLog check).

Files likely touched:
- `app/Jobs/SendWebhookJob.php`
- `app/Models/WebhookLog.php` (if schema changes needed)

---

## Prompt 5 — Branding: admin-configurable header/footer + images (mobile responsive)
Task: Add branding configuration to the single workshop and render it on public pages.

Requirements:
- Add fields to `workshops` table:
  - `brand_name` (string, nullable)
  - `logo_path` (string, nullable)
  - `hero_image_path` (string, nullable)
  - `header_html` (text, nullable)
  - `footer_html` (text, nullable)
  - `primary_color` (string, nullable)
- Update admin workshop form to upload logo/hero (store in public disk).
- Update `resources/views/layouts/app.blade.php` and/or `register/*` views to:
  - show logo/brand name
  - render header/footer blocks
  - remain mobile responsive
- Sanitize header/footer HTML or restrict to safe subset (recommend: plain text + limited tags).

Acceptance:
- Admin can change branding without code changes.
- Registration form and success page use the configured branding.

Files likely touched:
- migration(s) + `Workshop` model fillable
- `Admin/WorkshopController` + `admin/workshops/form.blade.php`
- public layout/views

---

## Prompt 6 — “Registration closed” page (clean UX)
Task: If no active workshop exists, show a dedicated closed page.

Requirements:
- In `RegistrationController@showForm()`, if no active workshop:
  - render `resources/views/register/closed.blade.php`
  - include brand header/footer if available
- Do not show the multi-step form when closed.

Acceptance:
- `/` shows closed page if no active workshop.

---

## Prompt 7 — Admin: improve webhook observability (logs screen)
Task: Add an admin page to monitor webhook deliveries and failures.

Requirements:
- New admin route/page:
  - list recent webhook logs with filters (config, status code range, date)
  - show payload summary (without OTP), response code, response body preview
- Link it from admin sidebar (“Webhook Logs”).
- Ensure styling matches `layouts/admin.blade.php` (dark theme).

Acceptance:
- Admin can quickly see if WhatsApp provider is failing and why.

---

## Prompt 8 — Final integration test plan (agent should write tests)
Task: Update/introduce tests to cover the core workflow.

Requirements:
- Add feature tests for:
  - OTP send endpoint: success JSON schema; no OTP returned in non-local
  - registration submit fails on wrong OTP when OTP webhook active
  - registration creates row, QR job queued
  - check-in endpoint: first scan succeeds, second scan returns “already checked in”
- Use `Queue::fake()` and/or `Http::fake()` to avoid real external calls.

Acceptance:
- `php artisan test` passes locally.

