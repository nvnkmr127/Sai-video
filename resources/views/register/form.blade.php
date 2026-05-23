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
                        @if($workshop->date)
                        <div class="meta-item">
                            <i class="bi bi-calendar3"></i>
                            <span>{{ $workshop->date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        <div class="meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <span>Hyderabad</span>
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
                <form action="{{ route('registration.store') }}" method="POST" id="registrationForm" class="step-form" data-error-keys='@json($errors->keys())'>
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
                            <input type="text" name="full_name" id="full_name" class="custom-input @error('full_name') is-invalid @enderror" placeholder="Enter your full name" value="{{ old('full_name') }}" required minlength="2" autofocus>
                            <label for="full_name">Full Name</label>
                            @error('full_name')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="field-error" id="field-error-full_name" style="display: none;"></div>
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
                            <input type="tel" name="phone" id="phone" class="custom-input @error('phone') is-invalid @enderror" placeholder="+91" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel">
                            <label for="phone">WhatsApp Number</label>
                            @error('phone')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="field-error" id="field-error-phone" style="display: none;"></div>
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
                            <input type="text" name="otp" id="otp" class="custom-input otp-input @error('otp') is-invalid @enderror" placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
                            <label for="otp">Verification Code</label>
                            @error('otp')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="field-error" id="field-error-otp" style="display: none;"></div>
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
                            <textarea name="address" id="address" class="custom-input textarea-input @error('address') is-invalid @enderror" placeholder="Enter full address..." required minlength="10">{{ old('address') }}</textarea>
                            <label for="address">Full Address</label>
                            @error('address')
                                <div class="field-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                            @enderror
                            <div class="field-error" id="field-error-address" style="display: none;"></div>
                        </div>
                        <div class="step-actions">
                            <button type="submit" class="btn-submit">Submit Registration <i class="bi bi-send-fill"></i></button>
                        </div>
                    </div>
                </form>
            @endif

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
        top: 0; left: 0; width: 100%;
        height: 100svh;
        min-height: 100vh;
        background: #f8fafc;
        z-index: 100;
        overflow-x: hidden;
        overflow-y: auto;
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
        .form-pane { flex: none; padding: 2rem 1.5rem 7rem; min-height: 25vh; }
        .workshop-title { font-size: 2.25rem; }
        .visual-content { padding: 1.5rem; }
        .step-header { margin-bottom: 2rem; }
        .step-header h3 { font-size: 1.75rem; }
        .input-group-custom { margin-bottom: 2rem; }
        .custom-input { font-size: 1.5rem; padding: 1.25rem 0 0.5rem; }
    }

    @media (max-width: 576px) {
        .visual-pane { height: 25vh; min-height: 380px; }
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
    const progressBar = document.getElementById('progressBar');
    const formEl = document.getElementById('registrationForm');
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
    let errorKeys = [];
    try {
        const form = document.getElementById('registrationForm');
        const raw = form?.getAttribute('data-error-keys') || '[]';
        errorKeys = JSON.parse(raw);
    } catch (e) {
        errorKeys = [];
    }

    if (Array.isArray(errorKeys)) {
        errorKeys.forEach((key) => {
            if (errorFields[key] !== undefined) {
                currentStep = errorFields[key];
            }
        });
    }

    function setFieldError(field, message) {
        const input = document.getElementById(field);
        if (input) {
            input.classList.add('is-invalid');
            input.setAttribute('aria-invalid', 'true');
        }

        const errorEl = document.getElementById(`field-error-${field}`);
        if (errorEl) {
            errorEl.style.display = 'block';
            errorEl.innerHTML = `<i class="bi bi-exclamation-circle"></i> ${message}`;
        }
    }

    function clearFieldError(field) {
        const input = document.getElementById(field);
        if (input) {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        }

        const errorEl = document.getElementById(`field-error-${field}`);
        if (errorEl) {
            errorEl.style.display = 'none';
            errorEl.textContent = '';
        }
    }

    ['full_name', 'phone', 'otp', 'address'].forEach((field) => {
        const el = document.getElementById(field);
        if (!el) return;
        el.addEventListener('input', () => clearFieldError(field));
        el.addEventListener('change', () => clearFieldError(field));
    });

    const otpInput = document.getElementById('otp');
    if (otpInput) {
        otpInput.addEventListener('input', () => {
            const next = String(otpInput.value || '').replace(/\D+/g, '').slice(0, 6);
            if (otpInput.value !== next) otpInput.value = next;
        });
    }

    const phoneInputEl = document.getElementById('phone');
    if (phoneInputEl) {
        phoneInputEl.addEventListener('input', () => {
            const raw = String(phoneInputEl.value || '');
            const trimmed = raw.replace(/[^\d+\-\s]/g, '');
            if (phoneInputEl.value !== trimmed) phoneInputEl.value = trimmed;
        });
    }

    function updateUI() {
        steps.forEach((step, idx) => {
            step.classList.toggle('active', idx === currentStep);
            if (idx === currentStep) {
                const input = step.querySelector('input, textarea');
                if (input) setTimeout(() => input.focus(), 100);
            }
        });

        const progress = ((currentStep + 1) / totalSteps) * 100;
        progressBar.style.width = progress + '%';
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
        if (input) {
            if (input.id === 'otp') {
                clearFieldError('otp');
                const value = String(input.value || '').trim();
                if (!/^\d{6}$/.test(value)) {
                    setFieldError('otp', 'Please enter the 6-digit code.');
                    input.focus();
                    return false;
                }
                return true;
            }

            clearFieldError(input.id);
            if (!input.checkValidity()) {
                setFieldError(input.id, input.validationMessage || 'Please enter a valid value.');
                input.focus();
                return false;
            }
        }

        if (currentStep === 3) {
            const addressInput = document.getElementById('address');
            if (addressInput) {
                clearFieldError('address');
                const value = String(addressInput.value || '').trim();
                if (value.length < 10) {
                    setFieldError('address', 'Please enter your full mailing address (at least 10 characters).');
                    addressInput.focus();
                    return false;
                }
            }
        }

        // Special logic for phone -> OTP (Step 2, index 1)
        if (currentStep === 1) {
            clearFieldError('phone');
            const rawPhone = String(phoneInput?.value || '').trim();
            const digits = rawPhone.replace(/\D+/g, '');
            if (digits.length < 7 || digits.length > 15) {
                setFieldError('phone', 'Please enter a valid WhatsApp number (7–15 digits).');
                phoneInput?.focus();
                return false;
            }

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
                        phone: rawPhone,
                        workshop_id: "{{ $workshop?->id }}"
                    })
                });
                
                const data = await response.json();
                
                btn.disabled = false;
                btn.innerHTML = originalText;

                if (data.exists) {
                    setFieldError('phone', data.message || 'This number is already registered.');
                    phoneInput?.focus();
                    return false;
                }

                sendOTP();
            } catch (err) {
                setFieldError('phone', 'Unable to verify number. Please try again.');
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

    async function validateAllBeforeSubmit() {
        const order = ['full_name', 'phone', 'otp', 'address'];
        for (const field of order) {
            const el = document.getElementById(field);
            if (!el) continue;
            clearFieldError(field);

            if (!el.checkValidity()) {
                setFieldError(field, el.validationMessage || 'Please enter a valid value.');
                currentStep = errorFields[field] ?? 0;
                updateUI();
                el.focus();
                return false;
            }
        }

        const otp = String(document.getElementById('otp')?.value || '').trim();
        if (!/^\d{6}$/.test(otp)) {
            setFieldError('otp', 'Please enter the 6-digit code.');
            currentStep = 2;
            updateUI();
            document.getElementById('otp')?.focus();
            return false;
        }

        const address = String(document.getElementById('address')?.value || '').trim();
        if (address.length < 10) {
            setFieldError('address', 'Please enter your full mailing address (at least 10 characters).');
            currentStep = 3;
            updateUI();
            document.getElementById('address')?.focus();
            return false;
        }

        return true;
    }

    async function submitViaAjax() {
        if (!formEl) return;
        const ok = await validateAllBeforeSubmit();
        if (!ok) return;

        const btn = formEl.querySelector('.btn-submit');
        const originalText = btn?.innerHTML;
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
        }

        try {
            const res = await fetch(formEl.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: new FormData(formEl),
            });

            const json = await res.json().catch(() => null);

            if (res.ok && json?.redirect) {
                window.location.href = json.redirect;
                return;
            }

            if (res.status === 422 && json?.errors) {
                const entries = Object.entries(json.errors);
                for (const [field, messages] of entries) {
                    if (['full_name', 'phone', 'otp', 'address'].includes(field)) {
                        const msg = Array.isArray(messages) ? messages[0] : String(messages);
                        setFieldError(field, msg);
                    }
                }

                const firstField = entries.find(([field]) => ['full_name', 'phone', 'otp', 'address'].includes(field))?.[0];
                if (firstField) {
                    currentStep = errorFields[firstField] ?? 0;
                    updateUI();
                    document.getElementById(firstField)?.focus();
                }
                return;
            }

            setFieldError('address', (json && (json.message || json.error)) ? (json.message || json.error) : 'Submission failed. Please try again.');
        } catch (e) {
            setFieldError('address', 'Submission failed. Please try again.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    if (formEl) {
        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            submitViaAjax();
        });
    }

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
