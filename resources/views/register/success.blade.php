@extends('layouts.app')

@section('content')
    <div class="success-page">
        <!-- Immersive Background -->
        <div class="dynamic-bg">
            @if(!empty($siteSettings['slider_images']))
                @foreach($siteSettings['slider_images'] as $index => $image)
                    <div class="bg-layer {{ $index === 0 ? 'active' : '' }}" style="background-image: url('/storage/{{ $image }}')"></div>
                @endforeach
            @else
                <div class="bg-layer active" style="background-image: url('/images/backgrounds/bg1.png')"></div>
                <div class="bg-layer" style="background-image: url('/images/backgrounds/bg2.png')"></div>
            @endif
            <div class="bg-overlay"></div>
        </div>

        <div class="content-wrapper">
            <div class="glass-card">
                <!-- Success Header (Tightened) -->
                <div class="success-header">
                    <div class="success-icon-wrap">
                        <div class="pulse-circle"></div>
                        <i class="bi bi-check-lg"></i>
                    </div>
                    <h1 class="gradient-text">Registered!</h1>
                    <p class="status-msg text-muted">Your digital pass is ready</p>
                </div>

                <!-- Pass Design (Enhanced & Larger) -->
                <div class="pass-container">
                    <div class="pass-top">
                        <div class="workshop-meta">
                            <span class="category-tag">OFFICIAL ENTRY PASS</span>
                            <h2 class="pass-workshop-title">{{ $registration->workshop->title }}</h2>
                        </div>

                        <div class="pass-qr-section top-qr">
                            @if($registration->status === 'approved')
                                <div class="qr-glow-wrap">
                                    <div class="qr-container">
                                        @if($registration->qr_code_path)
                                            <img src="/storage/{{ $registration->qr_code_path }}" alt="Pass QR" id="qr-image" class="qr-img">
                                        @else
                                            <div class="qr-placeholder" id="qr-spinner">
                                                <div class="spinner-custom"></div>
                                                <span>GENERATING...</span>
                                            </div>
                                            <img id="qr-image" class="qr-img" style="display: none;">
                                        @endif
                                    </div>
                                </div>
                                <div class="token-display">
                                    <span class="token-label">PASS ID</span>
                                    <span class="token-code">{{ $registration->qr_code_token }}</span>
                                </div>
                            @else
                                <div class="pending-status">
                                    <div class="pending-icon small">
                                        <i class="bi bi-clock-history"></i>
                                    </div>
                                    <div class="pending-badge mt-2">WAITING FOR APPROVAL</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="pass-divider">
                        <div class="cutout-left"></div>
                        <div class="pass-line"></div>
                        <div class="cutout-right"></div>
                    </div>

                    <div class="pass-bottom">
                        @if($registration->status !== 'approved')
                            <div class="pending-info text-center">
                                <h3 class="h6 fw-bold mb-1">Verification Pending</h3>
                                <p class="x-small text-muted mb-0">Our team is reviewing your details.</p>
                            </div>
                        @endif

                        <div class="attendee-details">
                            <div class="detail-row">
                                <div class="detail-group">
                                    <label>ATTENDEE</label>
                                    <span class="detail-value">{{ $registration->full_name }}</span>
                                </div>
                                <div class="detail-group text-end">
                                    <label>DATE</label>
                                    <span class="detail-value">{{ $registration->workshop->date->format('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="detail-row mt-3">
                                <div class="detail-group">
                                    <label>LOCATION</label>
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($registration->workshop->location) }}" target="_blank" class="detail-value location-link">
                                        <i class="bi bi-geo-alt me-1"></i>{{ $registration->workshop->location }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pass-actions">
                    <button onclick="window.print()" class="btn-action secondary">
                        <i class="bi bi-printer me-2"></i> Print / Save Pass
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Premium Glassmorphism Theme - Enhanced for Large QR */
        .success-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        .dynamic-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
        }

        .bg-layer {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 2.5s ease-in-out;
        }

        .bg-layer.active { opacity: 1; }

        .bg-overlay {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.6) 100%);
            backdrop-filter: blur(8px);
        }

        .content-wrapper {
            width: 100%;
            max-width: 580px; /* Widened to fit larger QR */
            z-index: 1;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(40px) saturate(180%);
            -webkit-backdrop-filter: blur(40px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 3rem;
            padding: 2.5rem 2rem; /* Reduced padding to remove empty space */
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.15);
            animation: cardAppear 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
            text-align: center;
        }

        @keyframes cardAppear {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .success-header {
            margin-bottom: 2rem;
        }

        .success-icon-wrap {
            position: relative;
            width: 70px;
            height: 70px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            color: #fff;
            border-radius: 50%;
            font-size: 2.25rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .pulse-circle {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 2px solid #000;
            border-radius: 50%;
            animation: pulse 2.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.8); opacity: 0; }
        }

        .gradient-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 0.25rem;
            background: linear-gradient(135deg, #000 0%, #555 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        .status-msg {
            font-size: 0.9rem;
            font-weight: 500;
            opacity: 0.7;
        }

        /* Pass Styling */
        .pass-container {
            background: #fff;
            border-radius: 2rem;
            overflow: hidden;
            box-shadow: 0 15px 45px rgba(0,0,0,0.06);
            margin-bottom: 2rem;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .pass-top {
            padding: 2rem;
            background: #fdfdfd;
        }

        .category-tag {
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 2.5px;
            color: #aaa;
            display: block;
            margin-bottom: 0.5rem;
        }

        .pass-workshop-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #000;
            margin: 0 0 1.5rem 0;
            line-height: 1.2;
        }

        .qr-glow-wrap {
            position: relative;
            display: inline-block;
            margin: 0 auto;
        }

        .qr-glow-wrap::after {
            content: '';
            position: absolute;
            inset: -10px;
            background: radial-gradient(circle, rgba(0,0,0,0.03) 0%, transparent 70%);
            z-index: -1;
        }

        .qr-container {
            width: 440px; /* Massive QR */
            height: 440px;
            margin: 0 auto 1.5rem;
            padding: 1rem;
            border: 1px solid #f0f0f0;
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }

        .qr-img { width: 100%; height: 100%; object-fit: contain; }

        .qr-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            font-size: 0.8rem;
            font-weight: 800;
            color: #ddd;
        }

        .spinner-custom {
            width: 40px;
            height: 40px;
            border: 3px solid #f5f5f5;
            border-top-color: #000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .token-display {
            background: #f8f8f8;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            display: inline-flex;
            flex-direction: column;
            border: 1px solid #f0f0f0;
        }

        .token-label { font-size: 0.6rem; font-weight: 800; color: #bbb; margin-bottom: 0.15rem; letter-spacing: 1px; }
        .token-code { font-family: 'Monaco', 'Consolas', monospace; font-size: 0.95rem; font-weight: 700; color: #000; letter-spacing: 1px; }

        .pass-divider {
            height: 40px;
            display: flex;
            align-items: center;
            position: relative;
            background: #fff;
        }

        .pass-line {
            flex-grow: 1;
            border-top: 2px dashed #f0f0f0;
            margin: 0 1.5rem;
        }

        .cutout-left, .cutout-right {
            width: 30px;
            height: 30px;
            background: #e9ecef;
            border-radius: 50%;
            position: absolute;
        }
        .cutout-left { left: -15px; box-shadow: inset -5px 0 8px rgba(0,0,0,0.02); }
        .cutout-right { right: -15px; box-shadow: inset 5px 0 8px rgba(0,0,0,0.02); }

        .pass-bottom {
            padding: 1.5rem 2rem;
            background: #fff;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            text-align: left;
        }

        .detail-group {
            flex: 1;
        }

        .detail-group label {
            display: block;
            font-size: 0.65rem;
            font-weight: 800;
            color: #ccc;
            letter-spacing: 1.5px;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }

        .detail-value {
            font-weight: 700;
            color: #222;
            font-size: 0.95rem;
            display: block;
        }

        .location-link {
            color: #000;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .location-link:hover {
            color: #555;
            text-decoration: underline;
        }

        .pass-qr-section.top-qr {
            padding-bottom: 0.5rem;
        }

        .pending-status .pending-icon.small {
            font-size: 3rem;
            color: #f59e0b;
            margin-bottom: 0.5rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .pending-badge {
            display: inline-block;
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .btn-action {
            width: 100%;
            padding: 1.15rem;
            border-radius: 1.5rem;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
            color: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .btn-action:hover {
            background: #222;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            .glass-card { padding: 1.5rem; border-radius: 2rem; }
            .qr-container { width: 100%; height: auto; aspect-ratio: 1/1; }
            .detail-row { flex-direction: column; gap: 1rem; }
            .detail-group.text-end { text-align: left !important; }
        }

        @media print {
            .dynamic-bg, .success-header, .pass-actions { display: none; }
            body { background: white; padding: 0; }
            .success-page { display: block; padding: 0; }
            .glass-card { background: none; box-shadow: none; border: none; padding: 0; }
            .pass-container { border: 1px solid #eee; width: 100%; max-width: 500px; margin: 0 auto; }
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const layers = document.querySelectorAll('.bg-layer');
        if (layers.length > 1) {
            let current = 0;
            setInterval(() => {
                layers[current].classList.remove('active');
                current = (current + 1) % layers.length;
                layers[current].classList.add('active');
            }, 6000);
        }

        @if($registration->status === 'approved' && !$registration->qr_code_path)
        const pollForQr = () => {
            fetch('/qr-status/{{ $registration->qr_code_token }}')
                .then(res => res.json())
                .then(data => {
                    if (data.ready) {
                        const spinner = document.getElementById('qr-spinner');
                        const img = document.getElementById('qr-image');
                        if (spinner) spinner.style.display = 'none';
                        if (img) {
                            img.src = data.url;
                            img.style.display = 'block';
                            img.classList.add('animate__animated', 'animate__fadeIn');
                        }
                    } else {
                        setTimeout(pollForQr, 2000);
                    }
                })
                .catch(() => setTimeout(pollForQr, 5000));
        };
        setTimeout(pollForQr, 2000);
        @endif
    });
    </script>
@endsection

