<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ing.html");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// Obtener estadísticas
$total_users = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users"));
$total_artistas = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM artistas"));
$total_eventos = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM eventos"));

// Obtener usuarios
$usuarios = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, email ASC");
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - La Escena</title>
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        .seccion-titulo {
            font-size: 20px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 16px;
        }

        .usuario-item {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .usuario-info h3 {
            font-size: 15px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .rol-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            background-color: rgba(173, 102, 108, 0.2);
            color: var(--primary);
        }

        .btn-editar-artista {
            padding: 6px 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Jost', sans-serif;
        }

        .btn-editar-artista:hover { background-color: var(--primary-dark); }
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
                <li><a href="panel_admin.php" class="active">Panel Admin</a></li>
                <li><a href="gestionar_artistas.php">Artistas</a></li>
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
            <h1>Panel Administrador</h1>
            <a href="logout.php" style="text-decoration:none; color: var(--text-secondary); font-size: 13px; border: 1px solid var(--border); padding: 8px 16px; border-radius: 4px;">Cerrar sesión</a>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="numero"><?= $total_users ?></div>
                <div class="label">Usuarios registrados</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_artistas ?></div>
                <div class="label">Artistas</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_eventos ?></div>
                <div class="label">Eventos</div>
            </div>
        </div>

        <!-- Lista de usuarios (solo lectura) -->
        <h2 class="seccion-titulo">Usuarios registrados</h2>
        <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
        <div class="usuario-item">
            <div class="usuario-info">
                <h3><?= htmlspecialchars($u['email']) ?></h3>
                <span class="rol-badge"><?= htmlspecialchars($u['role']) ?></span>
            </div>
            <?php if ($u['role'] == 'artista'): ?>
            <?php
            // Buscar id del artista por correo
            $correo = $u['email'];
            $res = mysqli_query($conn, "SELECT id FROM artistas WHERE correo = '$correo'");
            $art = mysqli_fetch_assoc($res);
            if ($art):
            ?>
            <a href="editar_artista.php?id=<?= $art['id'] ?>" class="btn-editar-artista">Editar perfil</a>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
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