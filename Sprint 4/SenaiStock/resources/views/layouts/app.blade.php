<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SenaiStock') — SENAI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        .navbar { background: #1a2a5e; }
        .navbar a { color: #fff; }
        .btn-orange { background: #f59e0b; color: #fff; font-weight: 600; border: none; border-radius: 6px; padding: 8px 18px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-orange:hover { background: #d97706; color: #fff; text-decoration: none; }
        .btn-navy { background: #1a2a5e; color: #fff; font-weight: 600; border: none; border-radius: 6px; padding: 8px 18px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-navy:hover { background: #0f1c42; color: #fff; text-decoration: none; }
        .btn-danger { background: #dc2626; color: #fff; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-edit { background: none; border: none; color: #1a2a5e; cursor: pointer; padding: 4px; }
        .btn-edit:hover { color: #f59e0b; }
        .table-container { border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .table-header { background: #1a2a5e; color: #fff; }
        .table-row-odd { background: #fff; }
        .table-row-even { background: #f3f6fb; }
        .table-row-selected { background: #dbeafe !important; }
        .alert-success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
        .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
        .alert-warning { background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 50; display: flex; align-items: center; justify-content: center; }
        .modal-box { background: #fff; border-radius: 12px; padding: 28px; width: 100%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .form-label { font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 4px; }
        .form-input { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 8px 12px; font-size: 14px; outline: none; box-sizing: border-box; }
        .form-input:focus { border-color: #1a2a5e; box-shadow: 0 0 0 2px rgba(26,42,94,0.15); }
        .form-error { color: #dc2626; font-size: 12px; margin-top: 3px; }
        .badge-low { background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
        .qty-low { color: #dc2626; font-weight: 700; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar" style="padding: 0 24px; display: flex; align-items: center; height: 60px; gap: 32px;">
    <!-- Brand -->
    <div style="display:flex;align-items:center;gap:10px;min-width:160px;">
        <div style="background:#fff;color:#1a2a5e;font-weight:900;font-size:13px;padding:4px 8px;border-radius:4px;letter-spacing:1px;">SENAI</div>
        <span style="color:#fff;font-weight:700;font-size:16px;">SENAI</span>
    </div>

    <!-- Nav links -->
    <div style="display:flex;gap:8px;flex:1;">
        <a href="{{ route('livros.index') }}"
           style="padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;
                  {{ request()->routeIs('livros.*') ? 'background:#f59e0b;color:#fff;' : 'color:#ccd6f6;' }}">
            📚 Biblioteca
        </a>
        <a href="{{ route('reservas.index') }}"
           style="padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;
                  {{ request()->routeIs('reservas.*') ? 'background:#f59e0b;color:#fff;' : 'color:#ccd6f6;' }}">
            📋 Reservas
        </a>
        @if(auth()->user()->isAlmoxarife())
        <a href="{{ route('estoque.alertas') }}"
           style="padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px;
                  {{ request()->routeIs('estoque.*') ? 'background:#f59e0b;color:#fff;' : 'color:#ccd6f6;' }}">
            ⚠️ Alertas
        </a>
        @endif
    </div>

    <!-- User / Logout -->
    <div style="display:flex;align-items:center;gap:16px;">
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" style="background:none;border:none;color:#ccd6f6;cursor:pointer;font-size:14px;font-weight:600;padding:8px 12px;display:flex;align-items:center;gap:6px;">
                ↪ Sair
            </button>
        </form>
        <div style="text-align:right;">
            <div style="color:#fff;font-size:13px;font-weight:600;">{{ ucfirst(Auth::user()->role) }}</div>
            <div style="color:#93c5fd;font-size:11px;">{{ Auth::user()->email }}</div>
        </div>
        <div style="background:#3b5bdb;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
    </div>
</nav>

<!-- Main content -->
<main style="max-width: 1200px; margin: 32px auto; padding: 0 24px;">
    @if(session('success'))
        <div class="alert-success">✔ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert-error">✖ {{ session('error') }}</div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
