@extends('layouts.app')

@push('head')
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Inter:wght@300;400;600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container-lowest": "#060d20",
                        "on-primary-container": "#dcdfff",
                        "tertiary-fixed": "#4cf9fc",
                        "inverse-surface": "#dbe2fd",
                        "inverse-on-surface": "#283044",
                        "on-secondary-fixed-variant": "#810080",
                        "surface-container-highest": "#2d3449",
                        "qr-bg": "#ffffff",
                        "glass-surface": "rgba(0, 0, 0, 0.2)",
                        "error": "#ffb4ab",
                        "primary-fixed-dim": "#bac3ff",
                        "surface-container": "#171f33",
                        "tertiary-container": "#006f71",
                        "surface-variant": "#2d3449",
                        "error-container": "#93000a",
                        "background": "#0b1326",
                        "inverse-primary": "#3b52ca",
                        "on-secondary-container": "#ff91f2",
                        "surface-dim": "#0b1326",
                        "on-primary": "#001f8f",
                        "surface": "#0b1326",
                        "secondary": "#ffabf2",
                        "surface-tint": "#bac3ff",
                        "secondary-fixed": "#ffd7f5",
                        "on-tertiary-container": "#49f7fa",
                        "on-surface-variant": "#c5c5d6",
                        "on-tertiary-fixed-variant": "#004f51",
                        "surface-bright": "#31394e",
                        "status-glow": "#bac3ff",
                        "on-surface": "#ffffff",
                        "primary-container": "#4158d0",
                        "on-background": "#ffffff",
                        "primary": "#bac3ff",
                        "secondary-fixed-dim": "#ffabf2",
                        "on-primary-fixed-variant": "#1d37b2",
                        "primary-fixed": "#dee0ff",
                        "on-tertiary": "#003738",
                        "on-secondary-fixed": "#380037",
                        "glass-border": "rgba(255, 255, 255, 0.15)",
                        "on-error": "#690005",
                        "surface-container-low": "#131b2e",
                        "on-primary-fixed": "#00105b",
                        "outline-variant": "#444654",
                        "on-tertiary-fixed": "#002020",
                        "surface-container-high": "#222a3e",
                        "on-error-container": "#ffdad6",
                        "tertiary-fixed-dim": "#09dcdf",
                        "outline": "#8f8f9f",
                        "tertiary": "#09dcdf",
                        "secondary-container": "#840582"
                    },
                    borderRadius: {
                        DEFAULT: "0.5rem",
                        lg: "1rem",
                        xl: "1.5rem",
                        full: "9999px"
                    },
                    spacing: {
                        "container-margin": "32px",
                        "stack-tight": "4px",
                        "base": "8px",
                        "gutter": "16px",
                        "stack-loose": "32px",
                        "card-padding": "40px"
                    },
                    fontFamily: {
                        "display-lg-mobile": ["Montserrat"],
                        "code-id": ["JetBrains Mono"],
                        "label-uppercase": ["Inter"],
                        "body-sm": ["Inter"],
                        "display-lg": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "headline-md": ["Montserrat"]
                    },
                    fontSize: {
                        "display-lg-mobile": ["32px", { lineHeight: "1.2", fontWeight: "700" }],
                        "code-id": ["14px", { lineHeight: "1", letterSpacing: "0.4em", fontWeight: "400" }],
                        "label-uppercase": ["11px", { lineHeight: "1", letterSpacing: "0.25em", fontWeight: "600" }],
                        "body-sm": ["14px", { lineHeight: "1.5", fontWeight: "300" }],
                        "display-lg": ["48px", { lineHeight: "1.1", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "body-lg": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
                        "headline-md": ["22px", { lineHeight: "1.3", fontWeight: "400" }]
                    }
                },
            },
        }
    </script>
@endpush

@section('content')
    <div class="success-page" id="successPage" data-qr-poll="{{ ($registration->status === 'approved' && !$registration->qr_code_path) ? '1' : '0' }}" data-qr-status-url="{{ route('registration.qr-status', ['token' => $registration->qr_code_token]) }}">
        <!-- Immersive Background -->
        <div class="dynamic-bg">
            @if(!empty($siteSettings['success_background']))
                <div class="bg-layer active" data-bg-url="{{ Storage::url($siteSettings['success_background']) }}"></div>
            @elseif(!empty($siteSettings['slider_images']))
                @foreach($siteSettings['slider_images'] as $index => $image)
                    <div class="bg-layer {{ $index === 0 ? 'active' : '' }}" data-bg-url="/storage/{{ $image }}"></div>
                @endforeach
            @else
                <div class="bg-layer active" style="background-image: url('/images/backgrounds/bg1.png')"></div>
                <div class="bg-layer" style="background-image: url('/images/backgrounds/bg2.png')"></div>
            @endif
            <div class="bg-overlay"></div>
        </div>
        <div class="dots-overlay"></div>

        <div class="content-wrapper">
            <div class="glass-card">
                <div class="pass-shell dark w-full max-w-[420px] flex flex-col items-center justify-center py-8 relative z-10 border-2 border-white/20 rounded-lg p-6 bg-black/40 backdrop-blur-[4px]">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-white/40 -translate-x-2 -translate-y-2"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-white/40 translate-x-2 -translate-y-2"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-white/40 -translate-x-2 translate-y-2"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-white/40 translate-x-2 translate-y-2"></div>

                    <div class="w-full flex justify-between items-center mb-8 px-2 font-code-id text-[10px] tracking-widest text-white/50">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                            REC 00:24:59:12
                        </div>
                        <div>ISO 400</div>
                        <div>4K 24FPS</div>
                    </div>

                    <div class="text-center mb-8">
                        @if($registration->status === 'approved')
                            <p class="font-code-id text-[10px] text-white/40 tracking-[0.4em] mb-2 uppercase">Production Pass</p>
                        @else
                            <p class="font-code-id text-[10px] text-white/40 tracking-[0.4em] mb-2 uppercase">STATUS: WAITING LIST</p>
                        @endif
                        <h1 class="font-display-lg-mobile text-[22px] text-white font-bold tracking-tighter uppercase leading-none">
                            {{ $registration->workshop->title }}<br><span class="text-primary">Workshop</span>
                        </h1>
                    </div>

                    <div class="relative mb-8 group w-full flex flex-col items-center">
                        @if($registration->status === 'approved')
                            <div class="qr-wrapper bg-white/5 p-2 rounded-lg relative overflow-hidden transition-transform duration-500 group-hover:scale-[1.02]">
                                <div class="absolute top-0 left-0 w-full h-[2px] bg-primary/40 animate-[scan_2s_linear_infinite] pointer-events-none z-10"></div>

                                @if($registration->qr_code_path)
                                    <img src="/storage/{{ $registration->qr_code_path }}" alt="Pass QR Code" id="qr-image" class="w-48 h-48 block relative z-0 grayscale">
                                @else
                                    <div id="qr-spinner" class="w-48 h-48 flex flex-col items-center justify-center gap-3 text-white/70 font-code-id text-[10px] tracking-widest">
                                        <span class="spinner-custom"></span>
                                        GENERATING...
                                    </div>
                                    <img id="qr-image" alt="Pass QR Code" class="w-48 h-48 block relative z-0 grayscale" style="display: none;">
                                @endif
                            </div>
                            <p class="font-code-id text-[10px] text-center mt-3 text-white/40 tracking-[0.5em]">UID: {{ strtoupper($registration->qr_code_token) }}</p>
                        @else
                            <div class="flex flex-col items-center justify-center py-10 text-center w-full">
                                <div class="mb-6 relative">
                                    <div class="w-24 h-24 rounded-full border-4 border-amber-500/30 flex items-center justify-center relative">
                                        <div class="w-20 h-20 rounded-full border-2 border-amber-500 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-amber-500 text-4xl">shield_person</span>
                                        </div>
                                        <div class="absolute inset-0 rounded-full border-4 border-amber-500/10 animate-ping"></div>
                                    </div>
                                </div>
                                <h2 class="font-display-lg-mobile text-2xl font-bold text-white mb-4 tracking-tight">Awaiting Approval</h2>
                                <p class="font-body-sm text-white/60 max-w-[280px] leading-relaxed mb-6">Your registration is being verified by our team. Your entry pass will appear here once approved.</p>
                                <div class="inline-flex items-center gap-2 bg-black/60 px-4 py-1.5 rounded-full border border-white/10">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span class="font-code-id text-[10px] text-white tracking-widest uppercase">Waiting for Approval</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @php
                        $workshop = $registration->workshop;
                        $eventStartsAt = $workshop?->starts_at;
                        if (!$eventStartsAt && $workshop) {
                            $rawDate = (string) $workshop->getRawOriginal('date');
                            if ($rawDate !== '' && preg_match('/\d:\d/', $rawDate)) {
                                try {
                                    $eventStartsAt = \Carbon\Carbon::parse($rawDate);
                                } catch (\Exception $e) {
                                }
                            }
                        }
                        $eventDate = $eventStartsAt ?: $workshop?->date;
                    @endphp

                    @if($registration->status === 'approved')
                        <div class="w-full grid grid-cols-2 gap-6 border-t border-b border-white/10 py-6">
                            <div class="flex flex-col gap-1">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Attendee</span>
                                <span class="font-code-id text-sm text-white">{{ $registration->full_name }}</span>
                            </div>
                            <div class="flex flex-col gap-1 text-right">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Date</span>
                                <span class="font-code-id text-sm text-white">{{ $eventDate?->format('d.m.Y') }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Time</span>
                                <span class="font-code-id text-sm text-white">{{ $eventStartsAt ? $eventStartsAt->format('h:i A') : 'TBD' }}</span>
                            </div>
                            <div class="flex flex-col gap-1 text-right">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Status</span>
                                <span class="font-code-id text-sm text-white">{{ strtoupper($registration->status) }}</span>
                            </div>
                            <div class="flex flex-col gap-1 col-span-2">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Location</span>
                                <a href="{{ $registration->workshop->location_link ?? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($registration->workshop->location) }}" target="_blank" class="font-code-id text-sm text-white underline decoration-white/20 underline-offset-4 hover:decoration-white/50">
                                    {{ $registration->workshop->location }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="w-full grid grid-cols-2 gap-6 border-t border-b border-white/10 py-6">
                            <div class="flex flex-col gap-1">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Attendee</span>
                                <span class="font-code-id text-sm text-white">{{ $registration->full_name }}</span>
                            </div>
                            <div class="flex flex-col gap-1 text-right">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Date</span>
                                <span class="font-code-id text-sm text-white">{{ $eventDate?->format('d.m.Y') }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Time</span>
                                <span class="font-code-id text-sm text-white">{{ $eventStartsAt ? $eventStartsAt->format('h:i A') : 'TBD' }}</span>
                            </div>
                            <div class="flex flex-col gap-1 text-right">
                                <span class="font-code-id text-[9px] text-white/40 uppercase tracking-widest">Status</span>
                                <span class="font-code-id text-sm text-white">{{ strtoupper($registration->status) }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="w-full mt-8 flex flex-col gap-3">
                        @if($registration->status === 'approved')
                            <button type="button" onclick="window.print()" class="w-full py-4 bg-white text-black font-code-id text-sm uppercase font-bold tracking-widest flex items-center justify-center gap-3 hover:bg-primary transition-colors rounded-lg">
                                <span class="material-symbols-outlined">movie</span>
                                Print Pass
                            </button>
                            <a href="{{ $registration->workshop->location_link ?? 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($registration->workshop->location) }}" target="_blank" class="w-full py-3 border border-white/20 text-white/60 font-code-id text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 hover:text-white hover:border-white/30 transition-colors">
                                <span class="material-symbols-outlined text-[16px]">directions</span>
                                Directions
                            </a>
                        @else
                            <button type="button" id="checkApprovalBtn" class="w-full py-4 bg-white text-black font-code-id text-sm uppercase font-bold tracking-widest flex items-center justify-center gap-3 hover:bg-primary transition-all rounded-lg">
                                <span class="material-symbols-outlined">sync</span>
                                CHECK APPROVAL STATUS
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .success-page {
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
            background-color: #000;
            color: #fff;
            overflow-x: hidden;
        }

        footer { display: none; }
        #toast-container { display: none; }

        .dynamic-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
        }

        .dots-overlay {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            opacity: 0.1;
            background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
            background-size: 32px 32px;
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
            background: radial-gradient(circle at center, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.6) 100%);
            backdrop-filter: blur(2px);
        }

        .content-wrapper {
            width: 100%;
            max-width: 420px;
            z-index: 1;
        }

        .glass-card {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            animation: cardAppear 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) both;
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
        @keyframes scan { 0% { top: 0; } 100% { top: 100%; } }

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

        .pending-visual-wrap {
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .status-ring-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .status-ring {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ring-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid #f59e0b;
            border-radius: 50%;
            animation: ringPulse 2s infinite;
        }

        @keyframes ringPulse {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .ring-core {
            width: 80px;
            height: 80px;
            background: #fffbeb;
            border: 2px solid #fde68a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #d97706;
            z-index: 2;
            box-shadow: 0 10px 20px rgba(217, 119, 6, 0.1);
        }

        .premium-status-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: #000;
            margin-bottom: 0.75rem;
        }

        .premium-status-desc {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.5;
            max-width: 320px;
            margin: 0 auto;
        }

        .pending-badge.premium {
            background: #000;
            color: #fff;
            padding: 0.6rem 1.25rem;
            border-radius: 2rem;
            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #f59e0b;
            border-radius: 50%;
            animation: dotPulse 1.5s infinite;
        }

        @keyframes dotPulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
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

        .pass-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn-action {
            flex: 1;
            padding: 1.15rem;
            border-radius: 1.5rem;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-action.primary {
            background: #000;
            color: #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .btn-action.secondary {
            background: #fff;
            color: #000;
            border: 1px solid #eee;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }

        .btn-action:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        @media (max-width: 600px) {
            .success-page { padding: 24px 16px; }
        }

        @media print {
            .dynamic-bg, .dots-overlay { display: none !important; }
            body { background: white !important; padding: 0 !important; color: #000 !important; }
            .success-page { display: block; padding: 0 !important; background: transparent !important; }
        }
    </style>

    <script>
        const layers = document.querySelectorAll('.bg-layer');
        layers.forEach(layer => {
            const bg = layer.getAttribute('data-bg-url');
            if (bg) {
                layer.style.backgroundImage = `url('${bg}')`;
            }
        });
        if (layers.length > 1) {
            let current = 0;
            setInterval(() => {
                layers[current].classList.remove('active');
                current = (current + 1) % layers.length;
                layers[current].classList.add('active');
            }, 6000);
        }

        const root = document.getElementById('successPage');
        const shouldPollQr = root && root.getAttribute('data-qr-poll') === '1';
        const qrStatusUrl = root ? root.getAttribute('data-qr-status-url') : null;
        const checkApprovalBtn = document.getElementById('checkApprovalBtn');

        if (shouldPollQr && qrStatusUrl) {
            const pollForQr = () => {
                fetch(qrStatusUrl)
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
        }

        if (checkApprovalBtn && qrStatusUrl) {
            checkApprovalBtn.addEventListener('click', () => {
                if (checkApprovalBtn.disabled) return;

                const original = checkApprovalBtn.innerHTML;
                checkApprovalBtn.disabled = true;
                checkApprovalBtn.innerHTML = '<span class="material-symbols-outlined">progress_activity</span> CHECKING...';

                fetch(qrStatusUrl, { headers: { 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(data => {
                        if (data?.status === 'approved') {
                            window.location.reload();
                            return;
                        }

                        checkApprovalBtn.innerHTML = '<span class="material-symbols-outlined">hourglass_empty</span> STILL PENDING';
                        setTimeout(() => {
                            checkApprovalBtn.disabled = false;
                            checkApprovalBtn.innerHTML = original;
                        }, 1500);
                    })
                    .catch(() => {
                        checkApprovalBtn.disabled = false;
                        checkApprovalBtn.innerHTML = original;
                    });
            });
        }
    </script>
