# WorkshopPro — Workflow-Aligned Plan (OTP → Register → WhatsApp Webhook → QR → Check-in)

This document aligns the existing codebase to your **planned workflow**:
1) user submits OTP request → 2) user verifies OTP → 3) registration saved + visible in admin → 4) QR generated → 5) WhatsApp provider notified via webhook to send confirmation w/ QR → 6) on workshop day: QR scan → check-in.

Assumptions confirmed:
- **One workshop only** (admin configures everything; at most one active workshop).
- OTP delivery via **WhatsApp provider**.
- WhatsApp integration is **JSON webhook** (provider sends messages).
- QR delivery via **WhatsApp message**.
- Public UI should be **mobile responsive**, with configurable header/footer/images/branding.

---

## 1) What already exists in the repo (good news)
- Public multi-step registration UI with OTP step (`resources/views/register/form.blade.php`).
- OTP endpoint (`POST /otp/send`) with webhook type `otp`.
- Registration create (`POST /register`) with OTP verification (cache-based) and DB persistence.
- Async pipeline:
  - Generate QR PNG (`GenerateAndSendQrCode`)
  - Send webhooks (`SendWebhookJob`) with logs (`WebhookLog`)
- Admin portal:
  - Attendee list + detail page + manual check-in
  - Webhook config CRUD + “test webhook”
- Front desk scanner page with secret key (`/validate?key=...`) that calls `/validate/check`.

---

## 2) The minimal changes needed to exactly match your workflow

### A) OTP delivery + verification (production-safe)
**Goal:** OTP must be sent via WhatsApp provider, stored server-side, never leaked.
- Keep cache-based OTP storage (`Cache::put('otp_{phone}', otp, 10min)`).
- Remove “OTP returned in response” except local/dev.
- Ensure UI handles “OTP send failed” and “OTP expired” cleanly.
- Add resend cooldown + rate limiting (server + UI).

### B) Registration submit must depend on OTP verification
**Goal:** a user can’t submit registration without successful OTP.
- Already present: if any active OTP webhook configs exist, OTP must match cache.
- Harden: even if OTP webhook is configured but delivery fails, still allow retries; don’t allow fallback bypass.

### C) WhatsApp confirmation webhook should only fire after QR is ready
**Goal:** provider gets QR (image or URL) so the message can include it.
- Current behavior is correct: `RegistrationCreated` → `GenerateAndSendQrCode` → then `SendWebhookJob`.
- Ensure storage disk `public` is actually served in production (Laravel `storage:link` and correct base URL).
- Keep sending both:
  - `qr_code_image_url` (preferred for WhatsApp media)
  - `qr_code_image_base64` (fallback if provider supports base64)

### D) Admin can configure messaging + branding
**Goal:** admin controls WhatsApp endpoints and workshop “look”.
- Webhook configs already exist; extend if needed with:
  - `label/template_name`
  - `extra_headers` (JSON)
  - `payload_mapping` or `message_variables`
- Branding: store on `workshops` table (since you have “one workshop”):
  - `hero_image_path`, `logo_path`
  - `header_html`, `footer_html` (or text fields)
  - `theme_primary_color`, `theme_secondary_color`

### E) Check-in reliability
**Goal:** scanning is fast, rejects duplicates, and logs activity.
- Current implementation uses DB transaction + row lock: good.
- Add a “scan history” / audit log in admin (optional but useful).

---

## 3) Recommended webhook payloads

### A) OTP webhook (`type = otp`, event = `otp.send`)
Request body (example):
```json
{
  "event": "otp.send",
  "timestamp": "2026-05-15T12:00:00Z",
  "phone": "+919999999999",
  "otp": "123456",
  "message": "Your verification code is 123456",
  "meta": {
    "otp_ttl_minutes": 10,
    "app": "WorkshopPro",
    "workshop_title": "Your Workshop Name"
  }
}
```

### B) Registration confirmation webhook (`type = registration`, event = `registration.created`)
```json
{
  "event": "registration.created",
  "timestamp": "2026-05-15T12:05:00Z",
  "registration_id": 123,
  "full_name": "Alice Smith",
  "phone": "+919999999999",
  "email": "alice@example.com",
  "address": "…",
  "qr_code_token": "uuid-token",
  "qr_code_image_url": "https://your-domain.com/storage/qrcodes/uuid-token.png",
  "qr_code_image_base64": "iVBORw0K…",
  "workshop": {
    "title": "Photography Workshop",
    "date": "2026-06-01",
    "location": "Studio A"
  }
}
```

**Security recommendation:** sign every webhook with an HMAC header, e.g. `X-Signature: sha256=...` using `WebhookConfig.secret_token`.

---

## 4) Micro-tasks (implementation-ready)

### Epic 1 — OTP correctness + UX
1. **Remove OTP echo-back in non-local environments**
   - Update `RegistrationController@sendOtp()` so it never returns `otp` unless `app()->environment('local')`.
   - Acceptance: production response does not include OTP.
2. **Add resend OTP flow + server throttling**
   - Enforce per-phone rate limits (e.g., 3/min) and show clear UI timer.
   - Acceptance: resend works; abuse is throttled.
3. **Make OTP errors first-class UI (no `alert()` only)**
   - Replace `alert()` with inline error banner/toast style matching your brand.

### Epic 2 — Webhook hardening for WhatsApp provider
4. **Add webhook signature header (HMAC)**
   - Acceptance: every webhook includes `X-Signature`.
5. **Add per-config retry visibility in admin**
   - Add a small admin view for webhook logs filtered by config & status.

### Epic 3 — Branding & mobile responsive public UI
6. **Workshop branding fields**
   - Migration + admin form updates to upload logo/hero, set colors, header/footer.
   - Acceptance: registration page renders brand assets, responsive on mobile.
7. **Closed-registration UX**
   - If no active workshop exists, show a friendly closed page instead of rendering the form.

### Epic 4 — Admin panel completeness
8. **Add “Workshop Settings” as the single source of truth**
   - Since you have one workshop, make admin enforce only one active workshop.
9. **Add attendee search + export polish**
   - Confirm CSV includes phone/address/token/check-in status.

### Epic 5 — Check-in day experience
10. **Scanner performance & UX**
   - Add clear large success/fail states, vibration (mobile), and “ready to scan” state.
11. **Optional: check-in audit log**
   - Store who checked-in, method (scanner/manual), device/IP.

---

## 5) “Vibe coding agent” prompt (tailored to your workflow)
Copy/paste into your coding agent:

> You are implementing a workshop registration system in Laravel 13. Flow: send OTP via WhatsApp provider webhook → verify OTP → create registration → generate QR PNG → send `registration.created` webhook to WhatsApp provider including QR URL/image → store everything in admin panel → front desk scans QR for check-in.  
>  
> Constraints: one active workshop only; mobile responsive; admin can configure branding (logo/hero/header/footer/colors) and webhook endpoints/secrets; webhooks must be signed (HMAC).  
>  
> Deliverables per task: update controller/model/migrations, update Blade UI, update/introduce tests, and ensure admin UI uses the existing dark theme components.
