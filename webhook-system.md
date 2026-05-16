# 🔗 Mobile-Responsive Webhook System

The system features a robust, event-driven webhook architecture designed for third-party integrations (WhatsApp, SMS, CRM).

## 📱 How it Works
1.  **Event Trigger**: A user completes a step (OTP) or finishes registration.
2.  **Job Dispatch**: An asynchronous job is sent to the queue (`database` driver).
3.  **Payload Construction**: A rich JSON payload is built, including attendee details and QR code URLs/Base64.
4.  **Delivery**: The system attempts to POST the data to all active endpoints.

## 🚀 Mobile Responsiveness
*   **Real-time Delivery**: Webhooks are processed in the background, ensuring the mobile registration UI remains snappy and fast.
*   **Payload Optimization**: Payloads include direct image URLs and Base64 strings, allowing mobile apps to display QR codes without extra network calls.
*   **X-Headers**: Includes `X-Webhook-Secret` and `X-Event` for easy routing on the receiver side.

## 🛠️ Configuration
Admins can manage endpoints in **Admin > Webhook Config**:
*   **OTP Type**: Dedicated endpoints for sending verification codes.
*   **Registration Type**: Endpoints for sending final tickets and confirmations.
*   **Test Feature**: Built-in testing tool to verify endpoint connectivity.

## 📊 Audit Trail
Every webhook attempt is logged with:
*   Response Status (e.g., 200, 500)
*   Full Response Body
*   Timestamp of attempt
*   Payload used
