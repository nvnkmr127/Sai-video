<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | {{ $siteSettings['site_name'] ?? 'WorkshopPro' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg-dark: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 2rem;
            padding: 3.5rem 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), #a855f7);
            border-radius: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .form-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border);
            color: white;
            padding: 0.85rem 1.25rem;
            border-radius: 1rem;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            color: white;
        }

        .form-control::placeholder {
            color: #475569;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            padding: 1rem;
            border-radius: 1rem;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.5px;
            margin-top: 1rem;
            transition: all 0.3s;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(99, 102, 241, 0.4);
            filter: brightness(1.1);
        }

        .form-check-input {
            background-color: rgba(15, 23, 42, 0.5);
            border-color: var(--border);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            border-radius: 1rem;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            margin-top: 2rem;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--text-main);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-5">
            <div class="brand-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h2 class="fw-bold" style="font-family: 'Outfit', sans-serif;">Admin Portal</h2>
            <p class="text-muted small">Authorized personnel only.</p>
        </div>

        @if($errors->any())
            <div class="alert p-3">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-muted ms-1" for="remember">Keep me signed in</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Enter Dashboard <i class="bi bi-arrow-right ms-2"></i>
            </button>

            @php
                $devAutologinEnabled = (bool) config('app.dev_autologin_enabled');
                $allowInProduction = (bool) config('app.dev_autologin_allow_production');
                $allowedIps = (array) config('app.dev_autologin_allowed_ips', []);
                $ipAllowed = !$allowedIps || in_array(request()->ip(), $allowedIps, true);
                $showAutologin = $devAutologinEnabled
                    && (!app()->environment('production') || $allowInProduction)
                    && $ipAllowed;
            @endphp

            @if($showAutologin)
                <div class="mt-4 text-center">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="flex-grow-1 border-top border-secondary opacity-25"></div>
                        <span class="text-muted small text-uppercase fw-bold opacity-50" style="font-size: 0.65rem;">Development</span>
                        <div class="flex-grow-1 border-top border-secondary opacity-25"></div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.autologin', ['as' => 'admin']) }}" class="btn btn-outline-info w-100 border-2 py-2 fw-bold" style="border-radius: 1rem;">
                            <i class="bi bi-magic me-2"></i> Autologin (Admin)
                        </a>
                        <a href="{{ route('admin.autologin', ['as' => 'desk']) }}" class="btn btn-outline-secondary w-100 border-2 py-2 fw-bold" style="border-radius: 1rem;">
                            <i class="bi bi-qr-code-scan me-2"></i> Autologin (Desk)
                        </a>
                    </div>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('registration.index') }}" class="back-link">
                    <i class="bi bi-arrow-left"></i> Return to Registration
                </a>
            </div>
        </form>
    </div>
</body>
</html>
