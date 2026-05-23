<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WorkshopPro | Registration</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @stack('head')
    <style>
        :root {
            --primary: #000000;
            --primary-dark: #1a1a1a;
            --bg-main: #ffffff;
            --bg-card: #f8f9fa;
            --text-main: #000000;
            --text-muted: #6c757d;
            --border: #e9ecef;
            --accent: #212529;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 5%;
            display: flex;
            justify-content: center; /* Centered brand for minimal look */
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--text-main);
            text-decoration: none;
            letter-spacing: -1px;
            text-transform: uppercase;
        }

        /* Main Container */
        .main-container {
            flex-grow: 1;
            width: 100%;
            margin: 0 auto;
            position: relative;
        }

        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            max-width: 400px;
            width: calc(100% - 4rem);
        }
        .toast-item {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.5s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .animate-in {
            animation: toastIn 0.5s forwards;
        }
        .animate-out {
            animation: toastOut 0.5s forwards;
        }
        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .toast-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        .toast-success .toast-icon { color: #10b981; }
        .toast-error .toast-icon { color: #ef4444; }
        .toast-content { flex-grow: 1; }
        .toast-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: #000000;
        }
        .toast-message {
            font-size: 0.875rem;
            color: #666666;
            line-height: 1.4;
        }
        .toast-close {
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            color: #999999;
            font-size: 1.25rem;
            display: flex;
        }
        .toast-close:hover { color: #000000; }

        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.75rem;
            background: #ffffff;
        }

        @media (max-width: 768px) {
            .page-title { font-size: 2.25rem; }
        }
    </style>
</head>
<body>
    <div id="toast-container" class="toast-container">
        @if(session('success'))
            <div class="toast-item toast-success animate-in">
                <div class="toast-icon"><i class="bi bi-check2-circle"></i></div>
                <div class="toast-content">
                    <div class="toast-title">Success</div>
                    <div class="toast-message">{{ session('success') }}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            </div>
        @endif

        @if(session('error'))
            <div class="toast-item toast-error animate-in">
                <div class="toast-icon"><i class="bi bi-exclamation-circle"></i></div>
                <div class="toast-content">
                    <div class="toast-title">Error</div>
                    <div class="toast-message">{{ session('error') }}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            </div>
        @endif

        @if($errors->any() && !request()->routeIs('registration.index'))
            <div class="toast-item toast-error animate-in">
                <div class="toast-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div class="toast-content">
                    <div class="toast-title">Validation Error</div>
                    <div class="toast-message">{{ $errors->first() }}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            </div>
        @endif
    </div>

    <script>
        function showToast(title, message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast-item toast-${type} animate-in`;
            
            const iconClass = type === 'success' ? 'bi-check2-circle' : 'bi-exclamation-circle';
            
            toast.innerHTML = `
                <div class="toast-icon"><i class="bi ${iconClass}"></i></div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button type="button" class="toast-close" onclick="this.parentElement.remove()"><i class="bi bi-x"></i></button>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.replace('animate-in', 'animate-out');
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        }

        // Auto-hide existing toasts
        document.querySelectorAll('.toast-item').forEach(toast => {
            setTimeout(() => {
                toast.classList.replace('animate-in', 'animate-out');
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        });
    </script>

    <main class="main-container">
        @yield('content')
    </main>

    <footer>
        <p>{{ $siteSettings['footer_text'] ?? '© ' . date('Y') . ' WorkshopPro. All rights reserved.' }}</p>
    </footer>
</body>
</html>
