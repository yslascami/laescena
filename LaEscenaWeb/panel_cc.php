<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'centrocultural') {
    header("Location: ing.html");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// Contar artistas
$total_artistas = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM artistas"));

// Contar eventos
$total_eventos = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM eventos"));

// Contar artistas pendientes de aprobación
$total_pendientes = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM artistas WHERE aprobado = 0"));
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Centro Cultural - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            color: var(--primary);
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            transition: border-color 0.2s, color 0.2s;
            text-decoration: none;
        }

        .btn-logout:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            text-align: center;
        }

        .stat-card .numero {
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            color: var(--primary);
            line-height: 1;
            margin-bottom: 8px;
        }

        .stat-card .label {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .acciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .accion-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            text-decoration: none;
            color: var(--text);
            transition: transform 0.2s, border-color 0.2s;
            display: block;
        }

        .accion-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .accion-card .icono {
            font-size: 36px;
            margin-bottom: 16px;
        }

        .accion-card h3 {
            font-size: 20px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .accion-card p {
            color: var(--text-secondary);
            font-size: 13px;
            line-height: 1.5;
        }

        .seccion-titulo {
            font-size: 24px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin: 30px 0 20px;
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
                <li><a href="panel_cc.php" class="active">Panel</a></li>
                <li><a href="gestionar_artistas.php">Artistas</a></li>
                <li><a href="gestionar_eventos.php">Eventos</a></li>
                <li><a href="artistas.php">Ver catálogo</a></li>
                <li><a href="galeria.php">Galería</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Panel Centro Cultural</h1>
            <a href="logout.php" class="btn-logout">Cerrar sesión</a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="numero"><?= $total_artistas ?></div>
                <div class="label">Artistas registrados</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_eventos ?></div>
                <div class="label">Eventos activos</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_pendientes ?></div>
                <div class="label">Perfiles pendientes</div>
            </div>
        </div>

        <!-- Acciones -->
        <h2 class="seccion-titulo">Acciones</h2>
        <div class="acciones-grid">
            <a href="gestionar_artistas.php" class="accion-card">
                <div class="icono"></div>
                <h3>Gestionar Artistas</h3>
                <p>Aprueba perfiles, edita información y gestiona el catálogo de artistas.</p>
            </a>
            <a href="gestionar_eventos.php" class="accion-card">
                <div class="icono"></div>
                <h3>Gestionar Eventos</h3>
                <p>Crea, edita y elimina eventos culturales.</p>
            </a>
            <a href="galeria.php" class="accion-card">
                <div class="icono"></div>
                <h3>Ver Galería</h3>
                <p>Visualiza las galerías y exposiciones actuales.</p>
            </a>
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