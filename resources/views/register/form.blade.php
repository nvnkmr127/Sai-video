@extends('layouts.app')

@section('content')
<div class="typeform-container">
    <div class="progress-bar-container">
        <div class="progress-bar-fill" id="progressBar"></div>
    </div>

    <!-- Background Slider -->
    <div class="slider-bg">
        @if(!empty($siteSettings['slider_images']))
            @foreach($siteSettings['slider_images'] as $index => $image)
                <div class="slider-item {{ $index === 0 ? 'active' : '' }}" style="background-image: url('{{ Storage::url($image) }}')"></div>
            @endforeach
        @else
            <div class="slider-item active" style="background-image: url('/images/backgrounds/bg1.png')"></div>
            <div class="slider-item" style="background-image: url('/images/backgrounds/bg2.png')"></div>
        @endif
        <div class="slider-overlay"></div>
    </div>

    <!-- Logo Overlay -->
    <div class="logo-overlay">
        @if($siteSettings['logo'])
            <img src="{{ Storage::url($siteSettings['logo']) }}" alt="Logo">
        @else
            <span class="fs-4 fw-bold">WorkshopPro</span>
        @endif
    </div>

    @if(!$workshop)
        <div class="tf-step active">
            <div class="tf-content text-center">
                <div class="mb-4">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 5rem;"></i>
                </div>
                <h1 class="tf-question">Registration Closed</h1>
                <p class="tf-description">Sorry, there are no active workshops open for registration at this time. Please check back later or contact the administrator.</p>
                <a href="/" class="tf-next-btn d-inline-block text-decoration-none">Back to Home</a>
            </div>
        </div>
    @else
    <form action="{{ route('registration.store') }}" method="POST" id="typeform">
        @csrf
        <input type="hidden" name="workshop_id" value="{{ $workshop->id }}">
        
        <!-- Step 1: Welcome -->
        <div class="tf-step active" data-step="1">
            <div class="tf-content">
                <span class="tf-number">01 →</span>
                <h1 class="tf-question">Let's get started. What's your full name?</h1>
                <p class="tf-description">Registration for: <strong>{{ $workshop->title }}</strong>. We'll use this for your event badge.</p>
                <input type="text" name="full_name" class="tf-input @error('full_name') is-invalid @enderror" placeholder="Type your answer here..." value="{{ old('full_name') }}" required autofocus>
                @error('full_name')
                    <div class="tf-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
                <div class="tf-nav-hint">Press <strong>ENTER</strong> to continue</div>
                <button type="button" class="tf-next-btn">OK <i class="bi bi-check2"></i></button>
            </div>
        </div>

        <!-- Step 2: Phone -->
        <div class="tf-step" data-step="2" id="phoneStep">
            <div class="tf-content">
                <span class="tf-number">02 →</span>
                <h1 class="tf-question">Great! What's your mobile number?</h1>
                <p class="tf-description text-primary fw-bold" style="color: #000 !important; opacity: 0.8;">Please provide <strong>WhatsApp number only</strong>.</p>
                <div class="input-action-wrapper">
                    <input type="tel" id="phone" name="phone" class="tf-input @error('phone') is-invalid @enderror" placeholder="+91" value="{{ old('phone') }}" required>
                    @error('phone')
                        <div class="tf-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                    @enderror
                </div>
                <div class="tf-nav-hint">We'll send an OTP to this number when you click next</div>
                <button type="button" class="tf-next-btn" id="phoneNextBtn">Next</button>
            </div>
        </div>

        <!-- Step 3: OTP -->
        <div class="tf-step" data-step="3">
            <div class="tf-content">
                <span class="tf-number">03 →</span>
                <h1 class="tf-question">Verify your number. Enter the 6-digit OTP.</h1>
                <p class="tf-description">Check your WhatsApp / SMS for the code.</p>
                <input type="text" name="otp" class="tf-input @error('otp') is-invalid @enderror" placeholder="000000" maxlength="6" required>
                @error('otp')
                    <div class="tf-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
                <div id="otpTimer" class="tf-description mt-2" style="display: none;">Resend in <span id="timerCount">30</span>s</div>
                <button type="button" class="tf-next-btn">Verify</button>
            </div>
        </div>


        <!-- Step 4: Address -->
        <div class="tf-step" data-step="4">
            <div class="tf-content">
                <span class="tf-number">04 →</span>
                <h1 class="tf-question">Finally, where should we send your materials?</h1>
                <p class="tf-description">Enter your full mailing address.</p>
                <textarea name="address" class="tf-input tf-textarea @error('address') is-invalid @enderror" placeholder="Type your address here..." required>{{ old('address') }}</textarea>
                @error('address')
                    <div class="tf-error"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div>
                @enderror
                <div class="mt-4">
                    <button type="submit" class="tf-submit-btn">Submit My Registration</button>
                </div>
            </div>
        </div>
    </form>
    @endif

    <div class="tf-controls">
        <button type="button" id="prevStep" class="tf-control-btn" disabled><i class="bi bi-chevron-up"></i></button>
        <button type="button" id="nextStep" class="tf-control-btn"><i class="bi bi-chevron-down"></i></button>
    </div>
</div>

<style>
    .typeform-container {
        width: 100%;
        height: 100vh;
        background: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .progress-bar-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: #f0f0f0;
        z-index: 1001;
    }

    .progress-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #6366f1, #a855f7);
        width: 20%;
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.5);
        transition: width 0.8s cubic-bezier(0.65, 0, 0.35, 1);
    }

    .tf-step {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 4rem 2rem 2rem; /* Increased top padding for mobile */
    }

    .tf-step.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .tf-step.prev {
        transform: translateY(-50px);
        opacity: 0;
    }

    .tf-content {
        max-width: 700px;
        width: 100%;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        padding: 4rem;
        border-radius: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .tf-number {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        color: #cccccc;
        margin-bottom: 1rem;
        display: block;
    }

    .tf-question {
        font-family: 'Outfit', sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1rem;
        color: #000000;
    }

    .tf-description {
        font-size: 1.1rem;
        color: #666666;
        margin-bottom: 2rem;
    }

    .tf-input {
        width: 100%;
        background: transparent;
        border: none;
        border-bottom: 2px solid #eeeeee;
        font-size: 2rem;
        padding: 0.5rem 0;
        color: #000000;
        transition: border-color 0.3s;
        border-radius: 0;
    }

    .tf-input:focus {
        outline: none;
        border-color: #000000;
    }

    .tf-textarea {
        font-size: 1.5rem;
        min-height: 150px;
        resize: none;
    }

    .tf-nav-hint {
        margin-top: 2rem;
        font-size: 0.8rem;
        color: #999999;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .tf-error {
        color: #ff0000;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        font-weight: 500;
        animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
    }

    @keyframes shake {
        10%, 90% { transform: translate3d(-1px, 0, 0); }
        20%, 80% { transform: translate3d(2px, 0, 0); }
        30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
        40%, 60% { transform: translate3d(4px, 0, 0); }
    }

    .tf-next-btn, .tf-submit-btn {
        background: linear-gradient(135deg, #000000, #1e293b);
        color: #ffffff;
        border: none;
        padding: 1.25rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 700;
        border-radius: 1rem;
        margin-top: 2.5rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .tf-next-btn:hover, .tf-submit-btn:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
        background: linear-gradient(135deg, #1e293b, #0f172a);
    }

    .tf-controls {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        display: flex;
        gap: 0.5rem;
    }

    .tf-control-btn {
        background: #000000;
        color: #ffffff;
        border: none;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
    }

    .tf-control-btn:disabled {
        background: #eeeeee;
        color: #cccccc;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .tf-question { font-size: 1.8rem; }
        .tf-input { font-size: 1.4rem; }
        .logo-overlay { top: 1rem; left: 1rem; }
        .logo-overlay img { max-height: 30px; }
        .tf-step { padding: 5rem 1.5rem 2rem; align-items: flex-start; } /* Align to top on mobile to avoid keyboard overlap */
        .tf-content { padding-top: 10vh; }
    }

    /* Slider Styles */
    .slider-bg {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        z-index: 0;
    }
    .slider-item {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transition: opacity 2s ease-in-out;
    }
    .slider-item.active { opacity: 1; }
    .slider-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle at center, rgba(255,255,255,0.4) 0%, rgba(255,255,255,0.8) 100%);
    }

    .logo-overlay {
        position: fixed;
        top: 2rem;
        left: 2rem;
        z-index: 1002;
    }
    .logo-overlay img { max-height: 40px; }

    .typeform-container { z-index: 1; }
    #typeform { position: relative; z-index: 2; }
    .tf-controls { z-index: 1002; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const steps = document.querySelectorAll('.tf-step');
    const totalSteps = steps.length;
    
    // Determine starting step (if there are errors, jump to the first error)
    const errorSteps = [
        @error('full_name') 0, @enderror
        @error('phone') 1, @enderror
        @error('otp') 2, @enderror
        @error('address') 3, @enderror
    ];
    
    let currentStepIndex = errorSteps.length > 0 ? errorSteps[0] : 0;
    
    // Initialize active step
    steps.forEach((s, i) => {
        s.classList.remove('active');
        if(i === currentStepIndex) s.classList.add('active');
    });

    const progressBar = document.getElementById('progressBar');
    const nextBtn = document.getElementById('nextStep');
    const prevBtn = document.getElementById('prevStep');
    const tfNextBtns = document.querySelectorAll('.tf-next-btn');

    // OTP Resend Logic
    const timerText = document.getElementById('otpTimer');
    const countSpan = document.getElementById('timerCount');
    const phoneInput = document.getElementById('phone');

    function sendOTP() {
        if (!phoneInput.value) return false;
        
        // Visual feedback for resend timer
        timerText.style.display = 'block';
        let count = 30;
        const timer = setInterval(() => {
            count--;
            countSpan.innerText = count;
            if (count <= 0) {
                clearInterval(timer);
                timerText.style.display = 'none';
            }
        }, 1000);

        // Actual backend call
        fetch("{{ route('registration.otp.send') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ phone: phoneInput.value })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showToast("Verification Error", data.message || "Failed to send OTP.", "error");
            } else {
                showToast("OTP Sent", "Please check your WhatsApp for the code.", "success");
            }
        })
        .catch(err => {
            console.error("OTP send error:", err);
        });

        return true;
    }

    function updateProgress() {
        const progress = ((currentStepIndex + 1) / totalSteps) * 100;
        progressBar.style.width = progress + '%';
        
        prevBtn.disabled = currentStepIndex === 0;
        nextBtn.disabled = currentStepIndex === totalSteps - 1;
    }

    function goToStep(index) {
        if (index < 0 || index >= totalSteps) return;

        // If leaving the phone step to go to OTP step, trigger OTP send
        if (currentStepIndex === 1 && index === 2) {
            if (!phoneInput.value || phoneInput.value.length < 10) {
                showToast("Input Required", "Please enter a valid WhatsApp number.", "error");
                return;
            }
            sendOTP();
        }

        // Restrict OTP step (Step 3)
        if (currentStepIndex === 2 && index === 3) {
            const otpInput = steps[2].querySelector('input[name="otp"]');
            if (!otpInput.value || otpInput.value.length < 4) {
                showToast("Verification Required", "Please enter the OTP sent to your phone.", "error");
                otpInput.focus();
                return;
            }
        }

        steps[currentStepIndex].classList.remove('active');
        if (index > currentStepIndex) {
            steps[currentStepIndex].classList.add('prev');
        } else {
            steps[currentStepIndex].classList.remove('prev');
        }

        currentStepIndex = index;
        steps[currentStepIndex].classList.add('active');
        steps[currentStepIndex].classList.remove('prev');

        const input = steps[currentStepIndex].querySelector('.tf-input');
        if (input) {
            // Small timeout to ensure visibility transition has started
            setTimeout(() => input.focus(), 100);
        }

        updateProgress();
    }

    tfNextBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            goToStep(currentStepIndex + 1);
        });
    });

    nextBtn.addEventListener('click', () => goToStep(currentStepIndex + 1));
    prevBtn.addEventListener('click', () => goToStep(currentStepIndex - 1));

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const currentStep = steps[currentStepIndex];
            const isTextarea = currentStep.querySelector('textarea');
            if (isTextarea && e.shiftKey) return; // Allow Shift+Enter in textarea
            
            if (currentStepIndex < totalSteps - 1) {
                e.preventDefault();
                goToStep(currentStepIndex + 1);
            }
        }
    });
    // Background Slider Auto-play
    const sliderItems = document.querySelectorAll('.slider-item');
    if (sliderItems.length > 1) {
        let sliderIndex = 0;
        setInterval(() => {
            sliderItems[sliderIndex].classList.remove('active');
            sliderIndex = (sliderIndex + 1) % sliderItems.length;
            sliderItems[sliderIndex].classList.add('active');
        }, 5000);
    }
});
</script>
@endsection
