<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'artista') {
    header("Location: ing.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Pendiente - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .pendiente-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 30px;
        }

        .pendiente-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .icono {
            font-size: 64px;
            margin-bottom: 24px;
        }

        .pendiente-card h1 {
            font-size: 28px;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .pendiente-card p {
            color: var(--text-secondary);
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .btn-logout {
            display: inline-block;
            padding: 12px 24px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            text-decoration: none;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-logout:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
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
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="pendiente-wrapper">
            <div class="pendiente-card">
                <div class="icono">⏳</div>
                <h1>Perfil en revisión</h1>
                <p>Tu perfil está siendo revisado por nuestro equipo. Una vez que sea aprobado podrás acceder a todas las funciones de La Escena.</p>
                <p>Te notificaremos cuando tu cuenta esté lista.</p>
                <a href="logout.php" class="btn-logout">Cerrar sesión</a>
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