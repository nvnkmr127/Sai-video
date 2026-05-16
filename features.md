# 🚀 System Features

The Workshop Registration System is a production-ready platform designed for seamless attendee management.

## 📋 Core Features

### 1. Registration Portal
*   **Typeform-Inspired UI**: A premium, multi-step registration experience.
*   **Dynamic Branding**: Centralized management for logos and background sliders.
*   **Real-time Progress**: Visual progress bar tracking user completion.
*   **Mobile-First Design**: Optimized for any device.

### 2. Verification System
*   **WhatsApp OTP**: Secure 6-digit OTP verification via WhatsApp API.
*   **Automated Rate Limiting**: Protection against OTP spamming.
*   **Session-based Security**: Temporary OTP storage with auto-expiry.

### 3. Check-in & QR System
*   **Auto-QR Generation**: Unique QR codes generated for every attendee upon successful registration.
*   **Structured Data**: QR codes contain encrypted/structured JSON for fast scanning.
*   **Real-time Validation**: Mobile-friendly scanner for event staff.
*   **Atomic Transactions**: Database-level locking to prevent duplicate check-ins.

### 4. Admin Command Center
*   **Live Dashboard**: Real-time stats on registrations and check-ins.
*   **Attendee Management**: Searchable, filterable list with CSV export capability.
*   **Webhook Control**: Configure multiple endpoints for different events (OTP, Registration).
*   **Manual Overrides**: Admin can manually check-in attendees if needed.
