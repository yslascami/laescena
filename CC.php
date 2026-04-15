<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro Cultural - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            color: var(--primary);
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 6px;
        }

        .hero-cc {
            background: linear-gradient(135deg, #3B081E, var(--card-bg));
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .hero-cc h2 {
            font-size: 28px;
            color: white;
            margin-bottom: 16px;
        }

        .hero-cc p {
            color: #AAAAAA;
            font-size: 15px;
            line-height: 1.8;
            max-width: 700px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            transition: transform 0.2s, border-color 0.2s;
        }

        .info-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .info-card h3 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .info-card p {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
        }

        .seccion-titulo {
            font-size: 24px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .descripcion {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .descripcion p {
            color: var(--text-secondary);
            font-size: 15px;
            line-height: 1.8;
            margin-bottom: 16px;
        }

        .descripcion p:last-child { margin-bottom: 0; }
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
                <li><a href="CC.php" class="active">Centro Cultural</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] == 'artista'): ?>
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <li><a href="portafolio.php">Mi Portafolio</a></li>
                        <li><a href="mensajes.php">Mensajes</a></li>
                    <?php elseif ($_SESSION['role'] == 'centrocultural'): ?>
                        <li><a href="panel_cc.php">Mi Panel</a></li>
                        <li><a href="mensajes.php">Mensajes</a></li>
                    <?php elseif ($_SESSION['role'] == 'superadmin'): ?>
                        <li><a href="panel_admin.php">Panel Admin</a></li>
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
        <div class="page-header">
            <h1>Centro Cultural</h1>
            
        </div>

        <div class="hero-cc">
    <h2>Centro Cultural Ricardo Garibay</h2>
    <p>Un espacio dedicado a la promoción y difusión de la cultura local, ofreciendo eventos artísticos, exposiciones y talleres para toda la comunidad.</p>
    <a href="https://sic.cultura.gob.mx/ficha.php?table=centro_cultural&table_id=442" 
       target="_blank" class="btn-primary" style="margin-top: 20px; display: inline-block;">
        Ver sitio oficial ↗
    </a>
</div>

        <div class="info-grid">
            <div class="info-card">
                <h3>Ubicación</h3>
                <p>Ubicado en el corazón de la ciudad, un punto de encuentro para artistas y amantes de la cultura.</p>
            </div>
            <div class="info-card">
                <h3>Actividades</h3>
                <p>Ofrecemos eventos artísticos, exposiciones, talleres y actividades culturales durante todo el año.</p>
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
      