<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | WorkshopPro Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --bg-main: #0f172a;
            --bg-sidebar: #1e293b;
            --primary: #6366f1;
            --primary-glow: rgba(99, 102, 241, 0.3);
            --border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0; left: 0;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -1px;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 3rem;
            text-decoration: none;
        }
        .brand i { color: var(--primary); }

        .nav-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            margin-bottom: 1rem;
            padding-left: 0.75rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 500;
            transition: all 0.3s;
            margin-bottom: 0.25rem;
        }
        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }
        .nav-link.active {
            color: white;
            background: var(--primary);
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .nav-link i { font-size: 1.1rem; }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 3rem;
            width: calc(100% - var(--sidebar-width));
        }

        /* Top Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }
        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.5rem 1rem;
            border-radius: 1rem;
            border: 1px solid var(--border);
        }
        .avatar {
            width: 35px; height: 35px;
            background: linear-gradient(135deg, var(--primary), #06b6d4);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.9rem;
        }

        /* Dashboard Cards */
        .stat-card {
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            padding: 2rem;
            transition: all 0.3s;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .stat-icon {
            width: 50px; height: 50px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 1.5rem;
        }
        .stat-label { color: var(--text-muted); font-size: 0.9rem; font-weight: 500; margin-bottom: 0.5rem; }
        .stat-value { font-family: 'Outfit', sans-serif; font-size: 2rem; font-weight: 700; }

        /* Tables and Lists */
        .content-card {
            background: var(--bg-sidebar);
            border: 1px solid var(--border);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .table { --bs-table-bg: transparent; --bs-table-color: white; margin-bottom: 0; }
        .table thead th {
            background: rgba(0,0,0,0.2);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .table td { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .table tr:last-child td { border-bottom: none; }

        .btn-primary {
            background: var(--primary); border: none; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600;
            box-shadow: 0 4px 15px var(--primary-glow);
        }
        .btn-primary:hover { background: #4f46e5; transform: translateY(-2px); }

        .badge-pill { padding: 0.4rem 1rem; border-radius: 2rem; font-size: 0.75rem; font-weight: 700; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            <i class="bi bi-cpu-fill"></i>
            <span>{{ config('app.name') }}</span>
        </a>

        <div class="nav-label">Core Management</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <a href="{{ route('admin.registrations.index') }}" class="nav-link {{ request()->routeIs('admin.registrations.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> Attendees
        </a>
        <a href="{{ route('admin.workshops.index') }}" class="nav-link {{ request()->routeIs('admin.workshops.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event-fill"></i> Event Settings
        </a>

        <div class="nav-label mt-4">System Settings</div>
        <a href="{{ route('admin.webhooks.index') }}" class="nav-link {{ request()->routeIs('admin.webhooks.*') ? 'active' : '' }}">
            <i class="bi bi-broadcast-pin"></i> Webhook Config
        </a>
        <a href="{{ route('registration.validator', ['key' => config('app.desk_secret')]) }}" class="nav-link {{ request()->routeIs('registration.validator') ? 'active' : '' }}">
            <i class="bi bi-qr-code-scan"></i> QR Validator
        </a>
        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear-fill"></i> Site Settings
        </a>

        <div class="mt-auto pt-5">
            <div class="user-profile mb-3">
                <div class="avatar">{{ substr(auth('admin')->user()->name ?? 'A', 0, 1) }}</div>
                <div class="overflow-hidden">
                    <div class="small fw-bold text-truncate">{{ auth('admin')->user()->name ?? 'Admin' }}</div>
                    <div class="text-muted" style="font-size: 0.7rem;">System Root</div>
                </div>
            </div>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i> Logout
            </a>
            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="header">
            <div>
                <h2 class="fw-bold mb-1">@yield('title', 'Overview')</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item text-muted small">Admin</li>
                        <li class="breadcrumb-item active text-primary small" aria-current="page">@yield('title', 'Dashboard')</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-3">
                <div class="stat-value fs-5 d-none d-md-block text-muted">
                    <i class="bi bi-clock me-2"></i> {{ now()->format('M d, H:i') }}
                </div>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success mb-4 py-3 rounded-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4 py-3 rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger mb-4 py-3 rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                @foreach($errors->all() as $error) <div>{{ $error }}</div> @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
