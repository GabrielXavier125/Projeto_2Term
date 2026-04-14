<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SenaiStock</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.10); padding: 40px 36px; width: 100%; max-width: 400px; }
        .form-input { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px 14px; font-size: 14px; outline: none; }
        .form-input:focus { border-color: #1a2a5e; box-shadow: 0 0 0 2px rgba(26,42,94,0.15); }
        .btn-login { width: 100%; background: #1a2a5e; color: #fff; border: none; border-radius: 8px; padding: 12px; font-size: 15px; font-weight: 700; cursor: pointer; }
        .btn-login:hover { background: #0f1c42; }
        .form-error { color: #dc2626; font-size: 12px; margin-top: 4px; }
        label { font-size: 13px; font-weight: 600; color: #374151; display: block; margin-bottom: 5px; margin-top: 16px; }
    </style>
</head>
<body>
<div class="login-card">
    <!-- Brand -->
    <div style="text-align:center;margin-bottom:28px;">
        <div style="display:inline-flex;align-items:center;gap:10px;margin-bottom:8px;">
            <div style="background:#1a2a5e;color:#fff;font-weight:900;font-size:14px;padding:6px 10px;border-radius:4px;letter-spacing:1px;">SENAI</div>
            <span style="font-size:20px;font-weight:700;color:#1a2a5e;">SenaiStock</span>
        </div>
        <p style="color:#6b7280;font-size:13px;margin:0;">Controle de Estoque de Livros Didáticos</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" class="form-input"
               value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
        @error('email')
            <div class="form-error">{{ $message }}</div>
        @enderror

        <label for="password">Senha</label>
        <input type="password" id="password" name="password" class="form-input"
               placeholder="••••••••" required>
        @error('password')
            <div class="form-error">{{ $message }}</div>
        @enderror

        <div style="display:flex;align-items:center;gap:8px;margin-top:16px;">
            <input type="checkbox" id="remember" name="remember" style="cursor:pointer;">
            <label for="remember" style="margin:0;font-weight:400;color:#6b7280;font-size:13px;cursor:pointer;">Lembrar-me</label>
        </div>

        <button type="submit" class="btn-login" style="margin-top:24px;">Entrar</button>
    </form>
</div>
</body>
</html>
