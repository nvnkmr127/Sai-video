@extends('layouts.app')

@section('content')
<div class="registration-page">
    <!-- Progress Indicator -->
    <div class="page-progress">
        <div class="progress-fill" id="progressBar"></div>
    </div>

    <div class="layout-wrapper">
        <!-- Visual Pane (Left/Top) -->
        <div class="visual-pane">
            <div class="slider-container">
                @if(!empty($siteSettings['slider_images']))
                    @foreach($siteSettings['slider_images'] as $index => $image)
                        <div class="slider-slide {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ Storage::url($image) }}')"></div>
                    @endforeach
                @else
                    <div class="slider-slide active" style="background-image: url('https://images.unsplash.com/photo-1540575861501-7cf05a4b125a?auto=format&fit=crop&q=80&w=2070')"></div>
                    <div class="slider-slide" style="background-image: url('https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&q=80&w=2012')"></div>
                @endif
                <div class="slider-overlay"></div>
            </div>

            <div class="visual-content">
                <div class="brand-box">
                    @if($siteSettings['logo'])
                        <img src="{{ Storage::url($siteSettings['logo']) }}" alt="Logo" class="brand-logo">
                    @else
                        <span class="brand-text">WorkshopPro</span>
                    @endif
                </div>
                
                @if($workshop)
                <div class="workshop-info">
                    <span class="info-badge">Join the event</span>
                    <h1 class="workshop-title">{{ $workshop->title }}</h1>
                    <div class="info-meta">
                        @if($workshop->scheduled_at)
                        <div class="meta-item">
                            <i class="bi bi-calendar3"></i>
                            <span>{{ $workshop->scheduled_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                        <div class="meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>{{ $workshop->location ?? 'Global Event' }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Form Pane (Right/Bottom) -->
        <div class="form-pane">
            @if(!$workshop)
                <div class="empty-state">
                    <div class="icon-circle">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <h2>Registration Closed</h2>
                    <p>Sorry, there are no active workshops open for registration at this time. Please check back later or contact the administrator.</p>
                    <a href="/" class="btn-primary">Back to Home</a>
                </div>
            @else
                <form action="{{ route('registration.store') }}" method="POST" id="registrationForm" class="step-form">
                    @csrf
                    <input type="hidden" name="workshop_id" value="{{ $workshop->id }}">
                    
                    <!-- Step 1: Full Name -->
                    <div class="step-item active" data-step="1">
                        <div class="step-header">
                            <span class="step-number">01</span>
                            <h3>Let's start with your name</h3>
                            <p>We'll use this for your event badge and certificate.</p>
                        </div>
                        <div class="input-group-custom">
                            <input type="text" name="full_name" id="full_name" class="custom-input @error('full_name') is-invalid @enderror" placeholder="Enter your full name" value="{{ old('full_name') }}" required autofocus>
                            <label for="full_name">Full Name</label>
                            @error('full_name')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="step-actions">
                            <button type="button" class="btn-next">Continue <i class="bi bi-arrow-right"></i></button>
                            <span class="shortcut-hint">Press <strong>Enter</strong> ↵</span>
                        </div>
                    </div>

                    <!-- Step 2: Phone -->
                    <div class="step-item" data-step="2">
                        <div class="step-header">
                            <span class="step-number">02</span>
                            <h3>Mobile Number</h3>
                            <p>Please provide your <strong>WhatsApp number</strong> for updates.</p>
                        </div>
                        <div class="input-group-custom">
                            <input type="tel" name="phone" id="phone" class="custom-input @error('phone') is-invalid @enderror" placeholder="+91" value="{{ old('phone') }}" required>
                            <label for="phone">WhatsApp Number</label>
                            @error('phone')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="step-actions">
                            <button type="button" class="btn-next">Get OTP <i class="bi bi-shield-check"></i></button>
                            <span class="shortcut-hint">Press <strong>Enter</strong> ↵</span>
                        </div>
                    </div>

                    <!-- Step 3: OTP -->
                    <div class="step-item" data-step="3">
                        <div class="step-header">
                            <span class="step-number">03</span>
                            <h3>Verify Identity</h3>
                            <p>Enter the 6-digit code sent to your WhatsApp.</p>
                        </div>
                        <div class="input-group-custom">
                            <input type="text" name="otp" id="otp" class="custom-input otp-input @error('otp') is-invalid @enderror" placeholder="000000" maxlength="6" inputmode="numeric" required>
                            <label for="otp">Verification Code</label>
                            @error('otp')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div id="otpTimerContainer" class="timer-display" style="display: none;">
                            <i class="bi bi-clock-history"></i> Resend code in <span id="timerValue">30</span>s
                        </div>
                        <div class="step-actions">
                            <button type="button" class="btn-next">Verify Code <i class="bi bi-check-lg"></i></button>
                        </div>
                    </div>

                    <!-- Step 4: Address -->
                    <div class="step-item" data-step="4">
                        <div class="step-header">
                            <span class="step-number">04</span>
                            <h3>Mailing Address</h3>
                            <p>Where should we send your workshop materials?</p>
                        </div>
                        <div class="input-group-custom">
                            <textarea name="address" id="address" class="custom-input textarea-input @error('address') is-invalid @enderror" placeholder="Enter full address..." required>{{ old('address') }}</textarea>
                            <label for="address">Full Address</label>
                            @error('address')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        <div class="step-actions">
                            <button type="submit" class="btn-submit">Submit Registration <i class="bi bi-send-fill"></i></button>
                        </div>
                    </div>
                </form>
            @endif

            <div class="form-navigation">
                <button type="button" id="prevStepBtn" class="nav-btn" disabled title="Previous Step">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="step-indicator-dots">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <button type="button" id="nextStepBtn" class="nav-btn" title="Next Step">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reset & Base */
    :root {
        --primary: #000000;
        --accent: #6366f1;
        --bg-glass: rgba(255, 255, 255, 0.8);
        --text-dark: #0f172a;
        --text-light: #64748b;
        --error: #ef4444;
        --border-radius: 1.5rem;
        --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .registration-page {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100vh;
        background: #f8fafc;
        z-index: 100;
        overflow: hidden;
    }

    /* Progress Bar */
    .page-progress {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 6px;
        background: #e2e8f0;
        z-index: 1001;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--accent), #a855f7);
        width: 25%;
        transition: width 0.6s ease;
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
    }

    /* Layout */
    .layout-wrapper {
        display: flex;
        height: 100%;
        width: 100%;
    }

    /* Visual Pane */
    .visual-pane {
        flex: 1.2;
        position: relative;
        background: #000;
        overflow: hidden;
    }
    .slider-container {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
    }
    .slider-slide {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transform: scale(1.1);
        transition: opacity 1.5s ease, transform 10s linear;
    }
    .slider-slide.active {
        opacity: 1;
        transform: scale(1.0);
    }
    .slider-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.2) 100%);
    }

    .visual-content {
        position: relative;
        z-index: 2;
        height: 100%;
        padding: 4rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        color: white;
    }
    .brand-text {
        font-family: 'Outfit', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -1px;
    }
    .brand-logo { max-height: 50px; filter: brightness(0) invert(1); }

    .workshop-info { max-width: 600px; }
    .info-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }
    .workshop-title {
        font-family: 'Outfit', sans-serif;
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 2rem;
        text-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    .info-meta { display: flex; gap: 2rem; }
    .meta-item { display: flex; align-items: center; gap: 0.75rem; font-size: 1.1rem; opacity: 0.9; }

    /* Form Pane */
    .form-pane {
        flex: 1;
        background: white;
        padding: 4rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
    }

    .step-item {
        display: none;
        animation: slideUp 0.6s ease forwards;
    }
    .step-item.active { display: block; }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .step-header { margin-bottom: 3rem; }
    .step-number {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--accent);
        margin-bottom: 0.5rem;
        display: block;
    }
    .step-header h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 0.75rem;
    }
    .step-header p { font-size: 1.1rem; color: var(--text-light); }

    /* Custom Inputs */
    .input-group-custom {
        position: relative;
        margin-bottom: 2.5rem;
    }
    .custom-input {
        width: 100%;
        padding: 1.5rem 0 0.5rem;
        font-size: 1.75rem;
        font-weight: 500;
        border: none;
        border-bottom: 2px solid #e2e8f0;
        background: transparent;
        transition: var(--transition);
        color: var(--text-dark);
        outline: none;
        border-radius: 0;
    }
    .custom-input::placeholder { color: transparent; }
    .custom-input:focus { border-color: var(--accent); }

    .input-group-custom label {
        position: absolute;
        top: 1.5rem; left: 0;
        font-size: 1.25rem;
        color: var(--text-light);
        pointer-events: none;
        transition: var(--transition);
    }
    .custom-input:focus ~ label,
    .custom-input:not(:placeholder-shown) ~ label {
        top: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--accent);
    }

    .textarea-input { min-height: 120px; font-size: 1.25rem; resize: none; }
    .otp-input { letter-spacing: 0.5rem; font-family: monospace; font-size: 2.5rem; text-align: center; }

    .field-error { color: var(--error); font-size: 0.9rem; margin-top: 0.5rem; font-weight: 500; }

    /* Buttons */
    .step-actions { display: flex; align-items: center; gap: 2rem; margin-top: 3rem; }
    .btn-next, .btn-submit, .btn-primary {
        padding: 1.25rem 2.5rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 1rem;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .btn-next:hover, .btn-submit:hover, .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        background: #1e293b;
    }
    .btn-submit { background: var(--accent); }
    .btn-submit:hover { background: #4f46e5; }

    .shortcut-hint { font-size: 0.85rem; color: var(--text-light); }

    /* Navigation */
    .form-navigation {
        position: absolute;
        bottom: 3rem; left: 4rem; right: 4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .nav-btn {
        width: 3.5rem; height: 3.5rem;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        cursor: pointer;
        transition: var(--transition);
    }
    .nav-btn:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); transform: scale(1.1); }
    .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

    .step-indicator-dots { display: flex; gap: 0.75rem; }
    .dot { width: 8px; height: 8px; background: #e2e8f0; border-radius: 50%; transition: var(--transition); }
    .dot.active { width: 24px; background: var(--accent); border-radius: 4px; }

    /* Empty State */
    .empty-state { text-align: center; max-width: 400px; margin: 0 auto; }
    .icon-circle {
        width: 80px; height: 80px; background: #f1f5f9; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem; color: var(--text-light); margin: 0 auto 2rem;
    }
    .empty-state h2 { font-size: 2rem; margin-bottom: 1rem; }
    .empty-state p { margin-bottom: 2rem; color: var(--text-light); }

    .timer-display { margin-top: 1rem; font-size: 0.9rem; color: var(--text-light); display: flex; align-items: center; gap: 0.5rem; }

    /* Responsive */
    @media (max-width: 1200px) {
        .workshop-title { font-size: 3rem; }
    }

    @media (max-width: 992px) {
        .layout-wrapper { flex-direction: column; overflow-y: auto; height: 100%; }
        .visual-pane { flex: none; height: 35vh; min-height: 250px; }
        .form-pane { flex: none; padding: 2rem 1.5rem 7rem; min-height: 65vh; }
        .workshop-title { font-size: 2.25rem; }
        .visual-content { padding: 1.5rem; }
        .step-header { margin-bottom: 2rem; }
        .step-header h3 { font-size: 1.75rem; }
        .input-group-custom { margin-bottom: 2rem; }
        .custom-input { font-size: 1.5rem; padding: 1.25rem 0 0.5rem; }
        .form-navigation { position: fixed; bottom: 0; left: 0; width: 100%; padding: 1.25rem 1.5rem; background: white; border-top: 1px solid #e2e8f0; z-index: 1002; }
    }

    @media (max-width: 576px) {
        .visual-pane { height: 25vh; min-height: 180px; }
        .step-header { margin-bottom: 1.5rem; }
        .step-header h3 { font-size: 1.5rem; }
        .step-header p { font-size: 0.95rem; }
        .custom-input { font-size: 1.25rem; }
        .btn-next, .btn-submit { width: 100%; justify-content: center; padding: 1rem 1.5rem; }
        .step-actions { margin-top: 2rem; }
        .shortcut-hint { display: none; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.step-item');
    const dots = document.querySelectorAll('.dot');
    const progressBar = document.getElementById('progressBar');
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const phoneInput = document.getElementById('phone');
    const timerValue = document.getElementById('timerValue');
    const timerContainer = document.getElementById('otpTimerContainer');
    
    let currentStep = 0;
    const totalSteps = steps.length;

    // Handle initial state if errors exist
    const errorFields = {
        'full_name': 0,
        'phone': 1,
        'otp': 2,
        'address': 3
    };

    @foreach($errors->keys() as $key)
        if (errorFields['{{ $key }}'] !== undefined) {
            currentStep = errorFields['{{ $key }}'];
        }
    @endforeach

    function updateUI() {
        steps.forEach((step, idx) => {
            step.classList.toggle('active', idx === currentStep);
            if (idx === currentStep) {
                const input = step.querySelector('input, textarea');
                if (input) setTimeout(() => input.focus(), 100);
            }
        });

        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === currentStep);
        });

        const progress = ((currentStep + 1) / totalSteps) * 100;
        progressBar.style.width = progress + '%';

        prevBtn.disabled = currentStep === 0;
        nextBtn.disabled = currentStep === totalSteps - 1;
    }

    function sendOTP() {
        if (!phoneInput.value) return;

        timerContainer.style.display = 'flex';
        let seconds = 30;
        const interval = setInterval(() => {
            seconds--;
            timerValue.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                timerContainer.style.display = 'none';
            }
        }, 1000);

        fetch("{{ route('registration.otp.send') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ phone: phoneInput.value })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showToast("Error", data.message || "Failed to send OTP", "error");
            } else {
                showToast("OTP Sent", "Code sent to your WhatsApp", "success");
            }
        })
        .catch(err => console.error("OTP Error:", err));
    }

    async function validateCurrentStep() {
        const input = steps[currentStep].querySelector('input, textarea');
        if (input && !input.checkValidity()) {
            input.reportValidity();
            return false;
        }
        
        // Special logic for phone -> OTP (Step 2, index 1)
        if (currentStep === 1) {
            if (phoneInput.value.length < 10) {
                showToast("Invalid Phone", "Please enter a valid WhatsApp number", "error");
                return false;
            }

            // Check for duplicate BEFORE sending OTP
            try {
                const btn = steps[currentStep].querySelector('.btn-next');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Checking...';

                const response = await fetch("{{ route('registration.check-duplicate') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ 
                        phone: phoneInput.value,
                        workshop_id: "{{ $workshop->id }}"
                    })
                });
                
                const data = await response.json();
                
                if (data.exists) {
                    showToast("Already Registered", data.message, "error");
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    return false;
                }

                // If not duplicate, send OTP and proceed
                sendOTP();
                btn.disabled = false;
                btn.innerHTML = originalText;
            } catch (err) {
                console.error("Duplicate check error:", err);
                // Fallback: allow if check fails? Or block? Let's block and show error.
                showToast("System Error", "Unable to verify number. Please try again.", "error");
                return false;
            }
        }

        // Special logic for OTP verification step (index 2)
        if (currentStep === 2) {
            const otpInput = document.getElementById('otp');
            if (otpInput.value.length < 4) {
                showToast("Incomplete OTP", "Please enter the 6-digit code", "error");
                return false;
            }
        }

        return true;
    }

    async function goToNext() {
        if (currentStep < totalSteps - 1) {
            const isValid = await validateCurrentStep();
            if (isValid) {
                currentStep++;
                updateUI();
            }
        }
    }

    function goToPrev() {
        if (currentStep > 0) {
            currentStep--;
            updateUI();
        }
    }

    // Event Listeners
    document.querySelectorAll('.btn-next').forEach(btn => btn.addEventListener('click', goToNext));
    nextBtn.addEventListener('click', goToNext);
    prevBtn.addEventListener('click', goToPrev);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const activeTag = document.activeElement.tagName;
            if (activeTag === 'TEXTAREA') return; // Allow newlines in textarea
            
            if (currentStep < totalSteps - 1) {
                e.preventDefault();
                goToNext();
            }
        }
    });

    // Background Slider
    const slides = document.querySelectorAll('.slider-slide');
    if (slides.length > 1) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 6000);
    }

    updateUI();
});
</script>
@endsection
