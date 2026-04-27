<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — RentAscencio</title>
    <style>
        :root {
            --bg: #f2f5fb;
            --primary: #2063cf;
            --primary-dark: #194ea4;
            --border: #dfe6f1;
            --text: #182230;
            --muted: #66768a;
            --danger: #a13434;
            --danger-bg: #ffe6e6;
            --sidebar: #0f1b2e;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, sans-serif;
            background:
                radial-gradient(circle at 10% -15%, #dce7ff 0%, transparent 35%),
                radial-gradient(circle at 100% 0%, #e8f3ff 0%, transparent 30%),
                var(--bg);
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
            padding: 1rem;
        }

        .brand {
            text-align: center;
            margin-bottom: 1.8rem;
        }

        .brand-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--sidebar);
            letter-spacing: 0.4px;
        }

        .brand-sub {
            color: var(--muted);
            font-size: 0.88rem;
            margin-top: 0.2rem;
        }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.8rem;
            box-shadow: 0 10px 28px rgba(15, 45, 100, 0.08);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 1.4rem;
        }

        .field {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #384658;
            margin-bottom: 0.3rem;
        }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 0.58rem 0.7rem;
            border: 1px solid #ccd7e6;
            border-radius: 9px;
            font-size: 0.92rem;
            background: #fff;
            color: var(--text);
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        input:focus {
            outline: 0;
            border-color: #7aa2e8;
            box-shadow: 0 0 0 3px rgba(32, 99, 207, 0.13);
        }

        .input-error {
            border-color: #e08080 !important;
        }

        .field-error {
            color: var(--danger);
            font-size: 0.82rem;
            margin-top: 0.3rem;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.2rem;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .remember-row label {
            margin: 0;
            font-weight: 500;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .btn-login {
            width: 100%;
            padding: 0.65rem;
            background: var(--primary);
            color: #fff;
            border: 0;
            border-radius: 9px;
            font-size: 0.96rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.15s;
            letter-spacing: 0.2px;
        }

        .btn-login:hover {
            background: var(--primary-dark);
        }

        .footer-note {
            text-align: center;
            color: var(--muted);
            font-size: 0.8rem;
            margin-top: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="brand">
            <div class="brand-name">RentAscencio</div>
            <div class="brand-sub">Control de locales comerciales y propiedades</div>
        </div>

        <div class="card">
            <p class="card-title">Iniciar sesión</p>

            @if ($errors->any())
                <div style="background:#fff2f2;border:1px solid #ffd1d1;color:#a13434;padding:0.6rem 0.8rem;border-radius:8px;font-size:0.88rem;margin-bottom:1rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        autofocus
                        class="{{ $errors->has('email') ? 'input-error' : '' }}"
                        placeholder="admin@rentas.com"
                    >
                </div>

                <div class="field">
                    <label for="password">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        autocomplete="current-password"
                        class="{{ $errors->has('password') ? 'input-error' : '' }}"
                        placeholder="••••••••"
                    >
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Mantener sesión iniciada</label>
                </div>

                <button type="submit" class="btn-login">Entrar</button>
            </form>
        </div>

        <p class="footer-note">Sistema de administración de rentas · v1.0</p>
    </div>
</body>
</html>
