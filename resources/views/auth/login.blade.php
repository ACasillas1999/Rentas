<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — Arrendamientos Grupo Ascencio</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('arrrendamientos.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            /* Patrón de puntos geométrico elegante y moderno (SaaS Style) */
            background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px);
            background-size: 28px 28px;
            position: relative;
            overflow: hidden;
        }

        /* Ambient glowing orbs para profundidad */
        body::before {
            content: '';
            position: absolute;
            top: -10vw; left: -10vw;
            width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.06) 0%, transparent 65%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        
        body::after {
            content: '';
            position: absolute;
            bottom: -10vw; right: -5vw;
            width: 45vw; height: 45vw;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.05) 0%, transparent 60%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }

        .login-wrap {
            width: 100%;
            max-width: 460px;
            padding: 2rem;
            animation: fadeIn 0.8s ease-out;
            position: relative;
            z-index: 10;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .brand img {
            max-width: 100%;
            width: 250px;
            height: auto;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.05));
            transition: transform 0.4s ease;
        }

        .brand img:hover {
            transform: scale(1.02);
        }

        .card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 1);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: -0.02em;
        }

        .field { margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.88rem; font-weight: 600; color: #475569; margin-bottom: 0.6rem; padding-left: 0.2rem; }
        input[type="email"], input[type="password"] { width: 100%; padding: 1rem 1.2rem; border: 1px solid #e2e8f0; border-radius: 16px; font-size: 1rem; background: #fff; color: var(--text-main); transition: all 0.2s ease; font-family: inherit; }
        input:focus { outline: 0; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        .input-error { border-color: #ef4444 !important; background: #fff5f5 !important; }
        .remember-row { display: flex; align-items: center; gap: 0.75rem; margin: 1.8rem 0; }
        .remember-row input[type="checkbox"] { width: 20px; height: 20px; accent-color: var(--primary); cursor: pointer; }
        .remember-row label { margin: 0; font-weight: 500; color: var(--text-muted); font-size: 0.95rem; cursor: pointer; }
        
        .btn-login { 
            width: 100%; padding: 1.1rem; background: var(--primary); color: #fff; 
            border: 0; border-radius: 16px; font-size: 1.1rem; font-weight: 700; 
            cursor: pointer; transition: all 0.3s ease; 
            box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.25); 
        }
        .btn-login:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 12px 20px -4px rgba(37, 99, 235, 0.35); }
        .btn-login:active { transform: translateY(0); }
        
        .footer-note { text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-top: 2.5rem; font-weight: 500; opacity: 0.8; }
        .error-alert { background: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 16px; font-size: 0.9rem; margin-bottom: 1.5rem; font-weight: 500; }

        /* --- Success Overlay Animation --- */
        .success-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: var(--primary);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }
        .success-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .success-content {
            text-align: center;
            color: #fff;
            transform: scale(0.8);
            transition: transform 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .success-overlay.active .success-content {
            transform: scale(1);
        }

        .success-content p {
            margin-top: 25px;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            opacity: 0;
            animation: fadeInText 0.5s ease 0.6s forwards;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #fff;
            stroke-miterlimit: 10;
            margin: 0 auto;
            box-shadow: inset 0px 0px 0px #fff;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #fff;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.6s forwards;
        }

        @keyframes stroke { 100% { stroke-dashoffset: 0; } }
        @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
        @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 30px rgba(255,255,255,0.15); } }
        @keyframes fadeInText { 100% { opacity: 1; transform: translateY(0); } 0% { opacity: 0; transform: translateY(15px); } }

        /* --- CSS Particles --- */
        .particles {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
            margin: 0; padding: 0;
        }

        .particles li {
            position: absolute;
            display: block;
            list-style: none;
            width: 20px;
            height: 20px;
            background: rgba(37, 99, 235, 0.08); /* Más visible */
            border: 2px solid rgba(37, 99, 235, 0.35); /* Borde más grueso y notorio */
            backdrop-filter: blur(3px); /* Efecto de cristal esmerilado flotante */
            -webkit-backdrop-filter: blur(3px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.15); /* Sombra suave */
            animation: animateShapes 25s linear infinite;
            bottom: -150px;
        }

        .particles li:nth-child(1) { left: 25%; width: 80px; height: 80px; animation-delay: 0s; }
        .particles li:nth-child(2) { left: 10%; width: 30px; height: 30px; animation-delay: 2s; animation-duration: 12s; }
        .particles li:nth-child(3) { left: 70%; width: 25px; height: 25px; animation-delay: 4s; }
        .particles li:nth-child(4) { left: 40%; width: 60px; height: 60px; animation-delay: 0s; animation-duration: 18s; }
        .particles li:nth-child(5) { left: 65%; width: 35px; height: 35px; animation-delay: 0s; }
        .particles li:nth-child(6) { left: 75%; width: 110px; height: 110px; animation-delay: 3s; }
        .particles li:nth-child(7) { left: 35%; width: 150px; height: 150px; animation-delay: 7s; }
        .particles li:nth-child(8) { left: 50%; width: 45px; height: 45px; animation-delay: 15s; animation-duration: 45s; }
        .particles li:nth-child(9) { left: 20%; width: 20px; height: 20px; animation-delay: 2s; animation-duration: 35s; }
        .particles li:nth-child(10) { left: 85%; width: 120px; height: 120px; animation-delay: 0s; animation-duration: 11s; }
        .particles li:nth-child(11) { left: 55%; width: 50px; height: 50px; animation-delay: 5s; animation-duration: 17s; }
        .particles li:nth-child(12) { left: 80%; width: 70px; height: 70px; animation-delay: 8s; animation-duration: 20s; }

        @keyframes animateShapes {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; border-radius: 0; }
            100% { transform: translateY(-120vh) rotate(720deg); opacity: 0; border-radius: 50%; }
        }

    </style>
</head>
<body>
    <!-- Success Animation Overlay -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-content">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
            <p>¡Acceso Concedido!</p>
        </div>
    </div>

    <!-- Background Particles -->
    <ul class="particles">
        <li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li>
        <li></li><li></li><li></li><li></li>
    </ul>

    <div class="login-wrap">
        <div class="card">
            <div class="brand">
                <img src="{{ asset('arrrendamientos.png') }}" alt="Logo">
            </div>

            <p class="card-title">Iniciar sesión</p>

            @if ($errors->any())
                <div class="error-alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
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

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
            const btn = form.querySelector('.btn-login');
            const originalText = btn.innerText;
            
            // Limpiar errores previos
            const existingAlert = document.querySelector('.error-alert');
            if (existingAlert) existingAlert.remove();
            
            btn.innerText = 'Verificando...';
            btn.style.opacity = '0.8';
            btn.disabled = true;

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.status === 422) {
                    const data = await response.json();
                    showError(data.message || 'Datos inválidos.');
                    resetBtn();
                    return;
                }

                // Laravel redirige internamente al fallar el login hacia /login. 
                // Fetch sigue la redirección.
                if (response.url.includes('/login')) {
                    showError('El correo o la contraseña son incorrectos.');
                    resetBtn();
                } else {
                    // ¡Login exitoso! Reproducir animación y redirigir
                    document.getElementById('successOverlay').classList.add('active');
                    
                    setTimeout(() => {
                        window.location.href = response.url;
                    }, 1600);
                }
            } catch (err) {
                showError('Ocurrió un error. Verifica tu conexión.');
                resetBtn();
            }

            function resetBtn() {
                btn.innerText = originalText;
                btn.style.opacity = '1';
                btn.disabled = false;
            }

            function showError(msg) {
                const errDiv = document.createElement('div');
                errDiv.className = 'error-alert';
                errDiv.innerText = msg;
                form.parentNode.insertBefore(errDiv, form);
            }
        });
    </script>
</body>
</html>
