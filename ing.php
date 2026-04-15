<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 30px;
        }

        .login-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }

        .login-card h1 {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .login-card p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text);
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text);
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px;
            letter-spacing: 2px;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 20px;
        }

        .btn-login:hover { background-color: var(--primary-dark); }

        .footer-links {
            text-align: center;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .footer-links a {
            color: var(--primary);
            text-decoration: none;
        }

        .footer-links a:hover { text-decoration: underline; }

        .footer-links p { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo-container">
            <img src="logo.png" alt="Logo La Escena">
            <h2>La Escena</h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="CC.php">Centro Cultural</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <li><a href="Reg.php">Registro</a></li>
                <li><a href="ing.php" class="active">Ingresar</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="login-wrapper">
            <div class="login-card">
                <h1>Iniciar Sesión</h1>
                <p>Accede a tu cuenta de La Escena</p>

                <form action="procesar_login.php" method="POST">
                    <div class="form-group">
                        <label for="usuario"> Correo</label>
                        <input type="text" id="usuario" name="usuario" placeholder="tu@correo.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-login">Entrar</button>
                </form>

                <div class="footer-links">
                    <p><a href="recuperar_password.php">¿Olvidaste tu contraseña?</a></p>
                    <p><a href="index.php">Volver al inicio</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const toggle = document.getElementById('toggle');
            const label = document.getElementById('theme-label');
            if (html.getAttribute('data-theme') === 'dark') {
                html.setAttribute('data-theme', 'light');
                toggle.classList.remove('on');
                label.textContent = 'Modo claro';
            } else {
                html.setAttribute('data-theme', 'dark');
                toggle.classList.add('on');
                label.textContent = 'Modo oscuro';
            }
            localStorage.setItem('theme', html.getAttribute('data-theme'));
        }
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        if (savedTheme === 'light') {
            document.getElementById('toggle').classList.remove('on');
            document.getElementById('theme-label').textContent = 'Modo claro';
        }
    </script>
</body>
</html>