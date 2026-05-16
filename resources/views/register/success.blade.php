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
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon-wrap">
                    <div class="pulse-circle"></div>
                    <i class="bi bi-check-lg"></i>
                </div>
                <h1 class="gradient-text">Registration Success!</h1>
                <p class="status-msg text-muted">Your entry pass is ready for the workshop</p>
            </div>

            <!-- Pass Design -->
            <div class="pass-container">
                <div class="pass-top">
                    <div class="workshop-meta">
                        <span class="category-tag">OFFICIAL ENTRY PASS</span>
                        <h2 class="pass-workshop-title">{{ $registration->workshop->title }}</h2>
                    </div>
                </div>

                <div class="pass-divider">
                    <div class="cutout-left"></div>
                    <div class="pass-line"></div>
                    <div class="cutout-right"></div>
                </div>

                <div class="pass-bottom">
                    <div class="attendee-details">
                        <div class="detail-row">
                            <div class="detail-group">
                                <label>ATTENDEE</label>
                                <span class="detail-value">{{ $registration->full_name }}</span>
                            </div>
                        </div>
                        <div class="detail-row mt-3">
                            <div class="detail-group">
                                <label>DATE</label>
                                <span class="detail-value">{{ $registration->workshop->date->format('M d, Y') }}</span>
                            </div>
                            <div class="detail-group">
                                <label>LOCATION</label>
                                <span class="detail-value">{{ $registration->workshop->location }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="pass-qr-section">
                        @if($registration->status === 'approved')
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
                            <div class="token-display">
                                <span class="token-label">PASS IDENTIFIER</span>
                                <span class="token-code">{{ $registration->qr_code_token }}</span>
                            </div>
                        @else
                            <div class="pending-status py-4">
                                <div class="pending-icon">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <h3 class="h5 fw-bold mb-2">Verification Pending</h3>
                                <p class="small text-muted mb-3">Our team is reviewing your registration. You will receive your digital pass once approved.</p>
                                <div class="pending-badge">WAITING LIST</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pass-actions">
                <button onclick="window.print()" class="btn-action secondary">
                    <i class="bi bi-printer me-2"></i> Print or Save Pass
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Premium Glassmorphism Theme */
    .success-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 1.5rem;
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
        max-width: 480px;
        z-index: 1;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(40px) saturate(180%);
        -webkit-backdrop-filter: blur(40px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 3rem;
        padding: 3.5rem 2.5rem;
        box-shadow: 0 40px 100px rgba(0, 0, 0, 0.15);
        animation: cardAppear 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
        text-align: center;
    }

    @keyframes cardAppear {
        from { opacity: 0; transform: translateY(40px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .success-icon-wrap {
        position: relative;
        width: 90px;
        height: 90px;
        margin: 0 auto 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        color: #fff;
        border-radius: 50%;
        font-size: 3rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
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
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, #000 0%, #555 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        letter-spacing: -1px;
    }

    .status-msg {
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 3rem;
        opacity: 0.8;
    }

    /* Pass Styling */
    .pass-container {
        background: #fff;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: 0 15px 45px rgba(0,0,0,0.06);
        margin-bottom: 2.5rem;
        border: 1px solid rgba(0,0,0,0.03);
    }

    .pass-top {
        padding: 2.5rem 2rem;
        background: #fdfdfd;
        border-bottom: 1px solid #f8f8f8;
    }

    .category-tag {
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 3px;
        color: #a0a0a0;
        display: block;
        margin-bottom: 0.75rem;
    }

    .pass-workshop-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.4rem;
        font-weight: 700;
        color: #000;
        margin: 0;
        line-height: 1.2;
    }

    .pass-divider {
        height: 40px;
        display: flex;
        align-items: center;
        position: relative;
        background: #fff;
    }

    .pass-line {
        flex-grow: 1;
        border-top: 2px dashed #eee;
        margin: 0 1.5rem;
    }

    .cutout-left, .cutout-right {
        width: 34px;
        height: 34px;
        background: #e9ecef; /* Slightly darker to show depth in glass */
        border-radius: 50%;
        position: absolute;
    }
    .cutout-left { left: -17px; box-shadow: inset -5px 0 10px rgba(0,0,0,0.03); }
    .cutout-right { right: -17px; box-shadow: inset 5px 0 10px rgba(0,0,0,0.03); }

    .pass-bottom {
        padding: 2.5rem 2rem;
    }

    .detail-row {
        display: flex;
        gap: 1.5rem;
        text-align: left;
    }

    .detail-group {
        flex: 1;
    }

    .detail-group label {
        display: block;
        font-size: 0.7rem;
        font-weight: 800;
        color: #b0b0b0;
        letter-spacing: 1.5px;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
    }

    .detail-value {
        font-weight: 600;
        color: #222;
        font-size: 1rem;
        display: block;
    }

    .pass-qr-section {
        margin-top: 1.5rem;
        padding-top: 2rem;
        border-top: 1px solid #f5f5f5;
    }

    .qr-container {
        width: 190px;
        height: 190px;
        margin: 0 auto 1.5rem;
        padding: 1.25rem;
        border: 1px solid #eee;
        border-radius: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.03);
    }

    .qr-img { width: 100%; height: 100%; object-fit: contain; }

    .qr-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        font-size: 0.75rem;
        font-weight: 800;
        color: #ccc;
        letter-spacing: 1px;
    }

    .spinner-custom {
        width: 32px;
        height: 32px;
        border: 3px solid #f0f0f0;
        border-top-color: #000;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .token-display {
        background: #f9f9f9;
        padding: 1rem;
        border-radius: 1.25rem;
        display: inline-flex;
        flex-direction: column;
        min-width: 160px;
        border: 1px solid #f0f0f0;
    }

    .token-label { font-size: 0.65rem; font-weight: 800; color: #aaa; margin-bottom: 0.25rem; letter-spacing: 1px; }
    .token-code { font-family: 'Monaco', 'Consolas', monospace; font-size: 0.9rem; font-weight: 700; color: #000; }

    .pending-status .pending-icon {
        font-size: 3.5rem;
        color: #f59e0b;
        margin-bottom: 1.25rem;
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
        padding: 0.6rem 1.5rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 800;
        letter-spacing: 1px;
    }

    .btn-action {
        width: 100%;
        padding: 1.25rem;
        border-radius: 1.5rem;
        font-weight: 700;
        font-size: 1rem;
        border: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
        color: #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .btn-action:hover {
        background: #222;
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.25);
    }

    .btn-action:active { transform: translateY(-1px); }

    @media (max-width: 480px) {
        .glass-card { padding: 2.5rem 1.5rem; border-radius: 2.5rem; }
        .gradient-text { font-size: 2rem; }
        .detail-row { flex-direction: column; gap: 1rem; }
        .qr-container { width: 160px; height: 160px; }
    }

    @media print {
        .dynamic-bg, .success-header, .pass-actions { display: none; }
        body { background: white; }
        .success-page { padding: 0; }
        .glass-card { background: none; box-shadow: none; border: none; padding: 0; }
        .pass-container { border: 1px solid #ddd; border-radius: 1rem; box-shadow: none; }
        .cutout-left, .cutout-right { background: white; border: 1px solid #ddd; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Background Layer Rotation
    const layers = document.querySelectorAll('.bg-layer');
    if (layers.length > 1) {
        let current = 0;
        setInterval(() => {
            layers[current].classList.remove('active');
            current = (current + 1) % layers.length;
            layers[current].classList.add('active');
        }, 6000);
    }

    // QR Code Generation Polling
    @if($registration->status === 'approved' && !$registration->qr_code_path)
    const pollForQr = () => {
        fetch('/qr-status/{{ $registration->qr_code_token }}')
            .then(res => {
                if (!res.ok) throw new Error();
                return res.json();
            })
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

