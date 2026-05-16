@extends('layouts.app')

@section('content')
<div class="typeform-container">
    <!-- Background Slider -->
    <div class="slider-bg">
        @if(!empty($siteSettings['slider_images']))
            @foreach($siteSettings['slider_images'] as $index => $image)
                <div class="slider-item {{ $index === 0 ? 'active' : '' }}" style="background-image: url('/storage/{{ $image }}')"></div>
            @endforeach
        @else
            <div class="slider-item active" style="background-image: url('/images/backgrounds/bg1.png')"></div>
            <div class="slider-item" style="background-image: url('/images/backgrounds/bg2.png')"></div>
        @endif
        <div class="slider-overlay"></div>
    </div>

    <div class="tf-step active">
        <div class="ticket-container">
            <!-- Ticket Top -->
            <div class="ticket-header">
                <div class="workshop-label">OFFICIAL ENTRY PASS</div>
                <h1 class="workshop-name">{{ $registration->workshop->title }}</h1>
                <div class="workshop-date">
                    <i class="bi bi-calendar3 me-2"></i> {{ $registration->workshop->date->format('M d, Y') }} 
                    <span class="mx-2">|</span>
                    <i class="bi bi-geo-alt me-2"></i> {{ $registration->workshop->location }}
                </div>
            </div>

            <!-- Perforation -->
            <div class="ticket-divider">
                <div class="circle circle-left"></div>
                <div class="dashed-line"></div>
                <div class="circle circle-right"></div>
            </div>

            <!-- Ticket Body -->
            <div class="ticket-body text-center">
                <div class="attendee-info mb-4">
                    <div class="small text-muted text-uppercase letter-spacing-2">Attendee</div>
                    <div class="attendee-name h3 fw-bold">{{ $registration->full_name }}</div>
                </div>

                @if($registration->status === 'approved')
                    <div class="qr-zone mb-4">
                        <div class="qr-wrapper">
                            @if($registration->qr_code_path)
                                <img src="/storage/{{ $registration->qr_code_path }}" alt="QR Code" id="qr-image">
                            @else
                                <img id="qr-image" style="display: none; width: 200px; height: 200px;" alt="QR Code">
                                <div class="qr-loading" id="qr-spinner">
                                    <div class="spinner-border text-primary mb-2"></div>
                                    <div>GENERATING YOUR QR CODE...</div>
                                </div>
                            @endif
                        </div>
                        <div id="qr-actions" style="display: {{ $registration->qr_code_path ? 'block' : 'none' }};"></div>
                    </div>
                @else
                    <div class="status-zone py-5 mb-4">
                        <div class="icon-box mb-3">
                            <i class="bi bi-hourglass-split text-warning fs-1"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Registration Pending</h4>
                        <p class="text-muted small px-4">Your registration is in our waiting list. Our team will verify your details and notify you once approved.</p>
                        <div class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill">
                            <i class="bi bi-clock-fill me-1"></i> WAITING FOR APPROVAL
                        </div>
                    </div>
                @endif

                <div class="token-zone">
                    <div class="token-label">Access Token</div>
                    <div class="token-value">{{ $registration->qr_code_token }}</div>
                </div>
            </div>

            <!-- Ticket Footer -->
            <div class="ticket-footer">
                <a href="{{ route('registration.index') }}" class="btn-register-more">
                    <i class="bi bi-plus-circle me-2"></i> Register Another Person
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .typeform-container {
        width: 100%;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4rem 1.5rem;
    }

    .ticket-container {
        max-width: 450px;
        width: 100%;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(20px) saturate(180%);
        border-radius: 2.5rem;
        box-shadow: 0 40px 100px -20px rgba(0,0,0,0.3);
        overflow: hidden;
        animation: ticketEntrance 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
    }

    @keyframes ticketEntrance {
        from { opacity: 0; transform: translateY(40px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .ticket-header {
        padding: 3rem 2.5rem 2rem;
        background: linear-gradient(135deg, rgba(255,255,255,0.4), rgba(255,255,255,0.1));
        text-align: center;
    }

    .workshop-label {
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 4px;
        color: #6366f1;
        margin-bottom: 1rem;
    }

    .workshop-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 1rem;
        color: #0f172a;
    }

    .workshop-date {
        font-size: 0.9rem;
        color: #64748b;
        font-weight: 500;
    }

    .ticket-divider {
        position: relative;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dashed-line {
        width: 100%;
        border-top: 2px dashed rgba(0,0,0,0.1);
        margin: 0 20px;
    }

    .circle {
        position: absolute;
        width: 40px;
        height: 40px;
        background: #f8fafc; /* Should match bg if not transparent */
        border-radius: 50%;
        top: 0;
    }

    .circle-left { left: -20px; box-shadow: inset -5px 0 10px rgba(0,0,0,0.05); }
    .circle-right { right: -20px; box-shadow: inset 5px 0 10px rgba(0,0,0,0.05); }

    .ticket-body {
        padding: 2rem 2.5rem;
    }

    .attendee-name {
        color: #0f172a;
        font-size: 1.5rem;
    }

    .qr-wrapper {
        background: #ffffff;
        padding: 1.5rem;
        border-radius: 2rem;
        display: inline-block;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .qr-wrapper img {
        width: 200px;
        height: 200px;
        display: block;
    }

    .qr-loading {
        width: 200px;
        height: 200px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.7rem;
        color: #94a3b8;
        letter-spacing: 1px;
    }

    .token-zone {
        background: rgba(0,0,0,0.03);
        padding: 1rem;
        border-radius: 1rem;
        margin-top: 2rem;
    }

    .token-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #94a3b8;
        margin-bottom: 0.25rem;
    }

    .token-value {
        font-family: monospace;
        font-size: 0.85rem;
        font-weight: 700;
        color: #1e293b;
        word-break: break-all;
    }

    .ticket-footer {
        padding: 2.5rem;
        text-align: center;
    }

    .btn-register-more {
        display: inline-block;
        background: #0f172a;
        color: #ffffff;
        text-decoration: none;
        padding: 1.25rem 2rem;
        border-radius: 1.25rem;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s ease;
        width: 100%;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .btn-register-more:hover {
        background: #1e293b;
        transform: translateY(-2px);
        color: #fff;
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    }

    @media (max-width: 480px) {
        .ticket-container { border-radius: 2rem; }
        .workshop-name { font-size: 1.5rem; }
        .qr-wrapper img, .qr-loading { width: 160px; height: 160px; }
    }

    /* Background Slider */
    .slider-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1; }
    .slider-item { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: cover; background-position: center; opacity: 0; transition: opacity 2s ease; }
    .slider-item.active { opacity: 1; }
    .slider-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.7) 100%); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Background slider logic
    const sliderItems = document.querySelectorAll('.slider-item');
    if (sliderItems.length > 1) {
        let index = 0;
        setInterval(() => {
            sliderItems[index].classList.remove('active');
            index = (index + 1) % sliderItems.length;
            sliderItems[index].classList.add('active');
        }, 5000);
    }

    // Poll every 2 seconds until QR is ready
    @if(!$registration->qr_code_path)
    function pollForQr() {
        fetch('/qr-status/{{ $registration->qr_code_token }}')
            .then(r => r.json())
            .then(data => {
                if (data.ready) {
                    const spinner = document.getElementById('qr-spinner');
                    const img = document.getElementById('qr-image');
                    const actions = document.getElementById('qr-actions');
                    
                    if (spinner) spinner.style.display = 'none';
                    if (img) {
                        img.src = data.url;
                        img.style.display = 'block';
                    }
                    if (actions) actions.style.display = 'block';
                } else {
                    setTimeout(pollForQr, 2000);
                }
            })
            .catch(err => {
                console.error("Polling error:", err);
                setTimeout(pollForQr, 5000); // Retry after 5s on error
            });
    }
    setTimeout(pollForQr, 2000);
    @endif
});
</script>
@endsection
