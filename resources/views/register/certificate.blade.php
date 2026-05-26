<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate of Completion | {{ $registration->full_name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-gold: #c5a880;
            --primary-dark: #121c24;
            --secondary-dark: #1e2d3b;
            --text-light: #f3f5f7;
            --border-color: #d1b58d;
            --bg-cream: #faf7f2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #1a1a1a;
            color: #333333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
            overflow-x: hidden;
        }

        /* Action Panel */
        .action-panel {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
            background: rgba(30, 45, 59, 0.85);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid rgba(197, 168, 128, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 100;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: none;
            color: var(--text-light);
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            background: var(--primary-gold);
            color: var(--primary-dark);
        }

        .btn-action-primary {
            background: var(--primary-gold);
            color: var(--primary-dark);
        }

        .btn-action-primary:hover {
            background: #d8c19e;
        }

        /* Certificate Container */
        .certificate-wrapper {
            width: 100%;
            max-width: 1050px;
            aspect-ratio: 1.414 / 1; /* A4 Landscape ratio */
            background: var(--bg-cream);
            border: 24px solid var(--primary-dark);
            padding: 20px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            overflow: hidden;
            margin-top: 50px;
        }

        /* Inner border frame */
        .certificate-inner-frame {
            border: 2px solid var(--border-color);
            width: 100%;
            height: 100%;
            padding: 40px 60px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        /* Corner Ornaments */
        .corner-ornament {
            position: absolute;
            width: 40px;
            height: 40px;
            border: 3px solid var(--border-color);
        }
        .top-left { top: 15px; left: 15px; border-right: none; border-bottom: none; }
        .top-right { top: 15px; right: 15px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 15px; left: 15px; border-right: none; border-top: none; }
        .bottom-right { bottom: 15px; right: 15px; border-left: none; border-top: none; }

        /* Content Sections */
        .header-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .org-logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 4px;
            color: var(--primary-dark);
            text-transform: uppercase;
            border-bottom: 2px solid var(--primary-gold);
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .cert-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3.5rem;
            font-weight: 400;
            letter-spacing: 2px;
            color: var(--primary-dark);
            text-transform: uppercase;
            line-height: 1;
        }

        .cert-subtitle {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 1.5rem;
            color: #666;
            margin-top: 15px;
        }

        .attendee-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 3.8rem;
            font-weight: 600;
            color: var(--primary-dark);
            border-bottom: 1.5px solid #cccccc;
            padding: 10px 40px 5px 40px;
            min-width: 60%;
            display: inline-block;
            margin: 20px 0;
            letter-spacing: 1px;
        }

        .achievement-text {
            font-family: 'Outfit', sans-serif;
            font-size: 1.05rem;
            color: #555555;
            max-width: 750px;
            line-height: 1.6;
            font-weight: 300;
        }

        .achievement-text strong {
            font-weight: 600;
            color: var(--primary-dark);
        }

        /* Footer & Signatures */
        .footer-section {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 30px;
            padding: 0 20px;
        }

        .signature-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 200px;
        }

        .signature-line {
            width: 100%;
            border-bottom: 1px solid #777777;
            margin-bottom: 8px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .signature-image {
            max-height: 35px;
            margin-bottom: -5px;
        }

        .signature-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            font-weight: 600;
        }

        .signature-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--primary-dark);
        }

        /* Golden Seal Badge */
        .seal-container {
            position: relative;
            width: 100px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .seal-svg {
            width: 100%;
            height: 100%;
            transform: rotate(-10deg);
        }

        /* Meta details */
        .cert-meta {
            position: absolute;
            bottom: 15px;
            font-size: 0.7rem;
            color: #999999;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Responsive scaling */
        @media (max-width: 900px) {
            .certificate-wrapper {
                border-width: 16px;
                margin-top: 60px;
                aspect-ratio: auto;
                height: auto;
            }
            .certificate-inner-frame {
                padding: 30px 20px;
            }
            .cert-title { font-size: 2.2rem; }
            .attendee-name { font-size: 2.5rem; margin: 15px 0; }
            .achievement-text { font-size: 0.9rem; }
            .footer-section {
                flex-direction: column;
                align-items: center;
                gap: 25px;
                margin-top: 25px;
            }
            .seal-container {
                order: -1;
            }
        }

        /* Print Media Styles */
        @media print {
            @page {
                size: landscape;
                margin: 0;
            }

            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
                display: block;
            }

            .action-panel {
                display: none !important;
            }

            .certificate-wrapper {
                margin: 0 !important;
                border: 24px solid var(--primary-dark) !important;
                box-shadow: none !important;
                width: 100vw !important;
                height: 100vh !important;
                max-width: none !important;
                aspect-ratio: 1.414 / 1 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .certificate-inner-frame {
                padding: 40px 60px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Action Panel (hidden during print) -->
    <div class="action-panel">
        <a href="{{ route('registration.success', $registration->qr_code_token) }}" class="btn-action">
            <i class="bi bi-arrow-left"></i> View Ticket Pass
        </a>
        <button onclick="window.print()" class="btn-action btn-action-primary">
            <i class="bi bi-printer"></i> Print / Save PDF
        </button>
    </div>

    <!-- Main Certificate Frame -->
    <div class="certificate-wrapper">
        <div class="certificate-inner-frame">
            <!-- Corner Frames -->
            <div class="corner-ornament top-left"></div>
            <div class="corner-ornament top-right"></div>
            <div class="corner-ornament bottom-left"></div>
            <div class="corner-ornament bottom-right"></div>

            <!-- Header -->
            <div class="header-section">
                <div class="org-logo">{{ $siteSettings['site_name'] }}</div>
                <h1 class="cert-title">Certificate of Completion</h1>
                <p class="cert-subtitle">This certificate is proudly presented to</p>
            </div>

            <!-- Recipient -->
            <div>
                <h2 class="attendee-name">{{ $registration->full_name }}</h2>
            </div>

            <!-- Achievement description -->
            <div class="achievement-text">
                for successfully attending and completing the photography workshop:
                <br>
                <strong>{{ $registration->workshop->title }}</strong>
                <br>
                conducted on <strong>{{ ($registration->workshop->starts_at ?? \Carbon\Carbon::parse($registration->workshop->date))->format('M d, Y') }}</strong> at <strong>{{ $registration->workshop->location }}</strong>.
            </div>

            <!-- Signatures & Seal -->
            <div class="footer-section">
                <!-- Instructor Signature -->
                <div class="signature-block">
                    <div class="signature-line">
                        <!-- Standard placeholder signature or text -->
                        <span style="font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 1.5rem; color: var(--primary-gold);">Workshop Team</span>
                    </div>
                    <div class="signature-name">Lead Instructor</div>
                    <div class="signature-label">{{ $siteSettings['site_name'] }}</div>
                </div>

                <!-- Gold Seal -->
                <div class="seal-container">
                    <svg class="seal-svg" viewBox="0 0 100 100">
                        <!-- Outer ribbon border -->
                        <circle cx="50" cy="50" r="45" fill="none" stroke="#c5a880" stroke-width="2" stroke-dasharray="3, 3" />
                        <!-- Seal Body -->
                        <circle cx="50" cy="50" r="38" fill="#121c24" stroke="#c5a880" stroke-width="1.5" />
                        <circle cx="50" cy="50" r="34" fill="none" stroke="#c5a880" stroke-width="0.5" />
                        <!-- Seal text -->
                        <path id="seal-text-path" d="M 18,50 A 32,32 0 1,1 82,50 A 32,32 0 1,1 18,50" fill="none" />
                        <text font-family="'Outfit', sans-serif" font-size="7.5" fill="#c5a880" font-weight="600" letter-spacing="1">
                            <textPath href="#seal-text-path" startOffset="0%">
                                • VERIFIED ATTENDEE • WORKSHOPPRO EXCELLENCE
                            </textPath>
                        </text>
                        <!-- Center Logo/Icon -->
                        <path d="M42,50 L47,55 L58,44" fill="none" stroke="#c5a880" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Organizer Signature -->
                <div class="signature-block">
                    <div class="signature-line">
                        <span style="font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 1.5rem; color: var(--primary-gold);">Verified</span>
                    </div>
                    <div class="signature-name">Event Coordinator</div>
                    <div class="signature-label">Front Desk Office</div>
                </div>
            </div>

            <!-- ID Verification code for authenticity -->
            <div class="cert-meta">
                Verification Token: {{ $registration->qr_code_token }} • Issue Date: {{ now()->format('Y-m-d') }}
            </div>
        </div>
    </div>

</body>
</html>
