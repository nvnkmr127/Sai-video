# WorkshopPro (Photography Workshop) — Project Understanding

## 1) What this project is
WorkshopPro is a Laravel web app for **collecting registrations for a workshop/event**, generating a **QR-based entry pass**, and supporting **front-desk check-in** via a QR scanner page. It also supports **webhook integrations** (e.g., send registration data to a photography CRM/WhatsApp automation/Google Sheets).

Even though the code is generic “workshop”, it maps cleanly to **photography use cases**:
- Photography masterclass / photowalk / studio lighting workshop registrations
- “Ticket” delivery as a QR pass
- On-site verification + attendee check-in
- Push attendee data into a CRM, mailing list, WhatsApp automation, or spreadsheet

## 2) Tech stack & architecture (high level)
- **Backend:** Laravel (composer requires `laravel/framework ^13.8`, PHP ^8.3)
- **Database:** SQLite (repo contains `database/database.sqlite`; tests configured to sqlite in-memory)
- **Async jobs/queue:** Jobs are used for QR generation and webhook delivery
- **Frontend:** Blade templates. There is Vite/Tailwind in `package.json`, but many screens use **inline CSS** and **Bootstrap CDN** (admin & scanner).

### Core domain models
- `Workshop`: title/description/date/location/max seats/is_active
- `Registration`: attendee info + `qr_code_token`, `qr_code_path`, `checked_in_at/by`, `webhook_sent_at`
- `WebhookConfig`: `type` (`registration` or `otp`), url, secret, is_active
- `WebhookLog`: stores each delivery attempt per webhook config per registration
- `User`: used as the “admin” account (admin guard uses same provider/model)

## 3) Main user journeys / flows

### A) Public registration flow
1. Visitor opens `/` or `/register`
2. Fills a multi-step form (name → phone → OTP → email → address)
3. POST `/register` creates `Registration` with a unique `qr_code_token`
4. A queued job pipeline runs:
   - `RegistrationCreated` → `GenerateAndSendQrCode` (generates PNG to `storage/public/qrcodes/{token}.png`)
   - then `SendWebhookJob` posts registration payload to each active webhook config
5. Visitor is redirected to `/success/{uuid}` showing QR + token

### B) Front-desk check-in (scanner)
1. Staff opens `/validate?key=...` (key must match `DESK_SECRET`)
2. Scans QR (or enters token manually)
3. Page calls POST `/validate/check` with `{ token, key }`
4. Server atomically locks the registration row, rejects duplicates, and sets `checked_in_at/by`

### C) Admin portal
1. Admin login `/admin/login`
2. Dashboard stats
3. Attendee list + detail view + manual check-in + webhook re-send
4. Manage workshops (CRUD)
5. Manage webhook configs (CRUD) + test webhook

## 4) Photography-specific use cases to support
These are practical “real client” use cases you can build toward:

### Core (today)
- Collect signups for a photography workshop
- Prevent overbooking (seat limit)
- Verify attendee at the venue using QR
- Export attendee list for printing badges / calling out names
- Send attendee data to external tools (webhook)

### Next (very common in photography)
- **Multiple workshops/slots:** photowalk morning/evening, different dates/locations
- **Team registrations:** couple/family, or assistants
- **Payments:** paid workshop deposit; “paid” state gates QR activation
- **Consent/waiver capture:** model release, venue guidelines
- **Marketing automation:** WhatsApp confirmation + reminder + “what to bring”
- **Post-event delivery:** gallery link delivery, coupon codes, feedback form

