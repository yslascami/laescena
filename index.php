<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .top-header h1 {
            font-size: 28px;
            color: var(--primary);
        }

        .hero-card {
            background: linear-gradient(135deg, #3B081E, var(--card-bg));
            border-radius: 4px;
            padding: 40px;
            margin-bottom: 30px;
            border: 1px solid var(--border);
        }

        .hero-card h2 { font-size: 32px; color: white; margin-bottom: 12px; }

        .hero-card p {
            color: #AAAAAA;
            font-size: 16px;
            line-height: 1.6;
            max-width: 600px;
            margin-bottom: 24px;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
    color: var(--text);
    text-decoration: none;
}

        .card-icon { font-size: 32px; margin-bottom: 12px; }
        .card h3 { font-size: 18px; margin-bottom: 8px; color: var(--text);
    text-decoration: none;}
        .card p { color: var(--text-secondary); font-size: 14px; line-height: 1.5; }
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
                <li><a href="index.php" class="active">Inicio</a></li>
                <li><a href="CC.php">Centro Cultural</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] == 'artista'): ?>
                        <li><a href="perfil.php">Mi Perfil</a></li>
                    <?php elseif ($_SESSION['role'] == 'centrocultural'): ?>
                        <li><a href="panel_cc.php">Mi Panel</a></li>
                    <?php elseif ($_SESSION['role'] == 'superadmin'): ?>
                        <li><a href="panel_admin.php">Panel Admin</a></li>
                        <li><a href="gestionar_artistas.php">Artistas</a></li>
                        <li><a href="gestionar_usuarios.php">Usuarios</a></li>
                        <li><a href="gestionar_portafolios.php">Portafolios</a></li>
                        <li><a href="gestionar_recintos.php">Recintos</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="Reg.php">Registro</a></li>
                    <li><a href="ing.php">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
    <?php if (isset($_SESSION['role'])): ?>
    <div class="session-bar">
        <span class="user-chip"><?php
            if ($_SESSION['role'] === 'artista') echo htmlspecialchars($_SESSION['artista_nombre'] ?? 'Artista');
            elseif ($_SESSION['role'] === 'centrocultural') echo 'Centro Cultural';
            elseif ($_SESSION['role'] === 'superadmin') echo 'Superadmin';
        ?></span>
        <a href="logout.php" class="btn-cerrar-sesion">Cerrar sesión</a>
    </div>
    <?php endif; ?>
        <div class="top-header">
            <h1>Bienvenido a La Escena</h1>
        </div>

        <div class="hero-card">
            <h2>Descubre el arte y la cultura</h2>
            <p>La Escena es un espacio dedicado a la promoción de la cultura y el arte en nuestra comunidad. Aquí encontrarás información sobre eventos culturales, artistas locales y oportunidades para participar en actividades artísticas.</p>
            <a href="eventos.php" class="btn-primary">Ver eventos →</a>
        </div>

        <div class="cards-grid">
            <a href="artistas.php" class="card">
                <div class="card-icon"></div>
                <h3>Artistas</h3>
                <p>Conoce a los artistas locales y su trabajo creativo.</p>
                <span class="badge">Ver catálogo</span>
            </a>
            <a href="eventos.php" class="card">
                <div class="card-icon"></div>
                <h3>Eventos</h3>
                <p>Próximos eventos culturales y actividades artísticas.</p>
                <span class="badge">Ver agenda</span>
            </a>
            <a href="galeria.php" class="card">
                <div class="card-icon"></div>
                <h3>Galería</h3>
                <p>Exposiciones y galerías disponibles en el recinto.</p>
                <span class="badge">Ver galería</span>
            </a>
            <a href="CC.php" class="card">
                <div class="card-icon"></div>
                <h3>Centro Cultural</h3>
                <p>Información sobre el centro cultural y sus espacios.</p>
                <span class="badge">Ver más</span>
            </a>
        </div>
    </div>

    <script>
        function toggleTheme() {
            const html   = document.documentElement;
            const toggle = document.getElementById('toggle');
            const label  = document.getElementById('theme-label');
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
            document.getElementById('togg