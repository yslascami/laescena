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

// Aprobar o rechazar artista
if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    mysqli_query($conn, "UPDATE artistas SET aprobado = 1 WHERE id = $id");
}
if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    mysqli_query($conn, "UPDATE artistas SET aprobado = 0 WHERE id = $id");
}

// Obtener todos los artistas
$result = mysqli_query($conn, "SELECT * FROM artistas ORDER BY aprobado ASC, nombre ASC");
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Artistas - La Escena</title>
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

        .artista-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 16px;
            display: grid;
            grid-template-columns: 60px 1fr auto;
            gap: 20px;
            align-items: center;
        }

        .artista-avatar {
            width: 60px;
            height: 60px;
            border-radius: 4px;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            color: white;
            overflow: hidden;
        }

        .artista-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .artista-info h3 {
            font-size: 18px;
            color: var(--text);
            margin-bottom: 4px;
        }

        .artista-info .detalle {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 2px;
        }

        .artista-info .contacto-privado {
            color: var(--primary);
            font-size: 12px;
            margin-top: 4px;
        }

        .acciones {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 140px;
        }

        .btn-aprobar {
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s;
        }

        .btn-aprobar:hover { background-color: var(--primary-dark); }

        .btn-rechazar {
            padding: 8px 16px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            text-decoration: none;
            text-align: center;
            transition: border-color 0.2s;
        }

        .btn-rechazar:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .estado-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .aprobado { background-color: rgba(173, 102, 108, 0.2); color: var(--primary); }
        .pendiente { background-color: rgba(170, 170, 170, 0.2); color: var(--text-secondary); }

        .seccion-titulo {
            font-size: 20px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin: 24px 0 16px;
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
                <li><a href="panel_cc.php">Panel</a></li>
                <li><a href="gestionar_artistas.php" class="active">Artistas</a></li>
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
            <h1>Gestionar Artistas</h1>
            <a href="logout.php" style="text-decoration:none; color: var(--text-secondary); font-size: 13px; border: 1px solid var(--border); padding: 8px 16px; border-radius: 4px;">Cerrar sesión</a>
        </div>

        <?php
        $aprobados = [];
        $pendientes = [];
        mysqli_data_seek($result, 0);
        while ($artista = mysqli_fetch_assoc($result)) {
            if ($artista['aprobado']) {
                $aprobados[] = $artista;
            } else {
                $pendientes[] = $artista;
            }
        }

        // Pendientes primero
        if (!empty($pendientes)) {
            echo '<h2 class="seccion-titulo">Pendientes de aprobación (' . count($pendientes) . ')</h2>';
            foreach ($pendientes as $artista) {
                $inicial = strtoupper(mb_substr($artista['nombre'], 0, 1));
                echo '
                <div class="artista-card">
                    <div class="artista-avatar">' . $inicial . '</div>
                    <div class="artista-info">
                        <span class="estado-badge pendiente">Pendiente</span>
                        <h3>' . htmlspecialchars($artista['nombre']) . '</h3>
                        <p class="detalle">' . htmlspecialchars($artista['disciplina'] ?? 'Sin disciplina') . '</p>
                        <p class="contacto-privado">✉ ' . htmlspecialchars($artista['correo']) . '</p>
                        <p class="contacto-privado">☎ ' . htmlspecialchars($artista['teléfono'] ?? 'Sin teléfono') . '</p>
                    </div>
                    <div class="acciones">
                        <a href="?aprobar=' . $artista['id'] . '" class="btn-aprobar">Aprobar</a>
                        <a href="editar_artista.php?id=' . $artista['id'] . '" class="btn-rechazar">Editar</a>
                    </div>
                </div>';
            }
        }

        // Aprobados
        if (!empty($aprobados)) {
            echo '<h2 class="seccion-titulo">Artistas aprobados (' . count($aprobados) . ')</h2>';
            foreach ($aprobados as $artista) {
                $inicial = strtoupper(mb_substr($artista['nombre'], 0, 1));
                echo '
                <div class="artista-card">
                    <div class="artista-avatar">';
                if (!empty($artista['foto_perfil'])) {
                    echo '<img src="' . htmlspecialchars($artista['foto_perfil']) . '" alt="">';
                } else {
                    echo $inicial;
                }
                echo '</div>
                    <div class="artista-info">
                        <span class="estado-badge aprobado">Aprobado</span>
                        <h3>' . htmlspecialchars($artista['nombre']) . '</h3>
                        <p class="detalle">' . htmlspecialchars($artista['disciplina'] ?? 'Sin disciplina') . '</p>
                        <p class="contacto-privado">✉ ' . htmlspecialchars($artista['correo']) . '</p>
                        <p class="contacto-privado">☎ ' . htmlspecialchars($artista['teléfono'] ?? 'Sin teléfono') . '</p>
                    </div>
                    <div class="acciones">
                        <a href="?rechazar=' . $artista['id'] . '" class="btn-rechazar">Desaprobar</a>
                        <a href="editar_artista.php?id=' . $artista['id'] . '" class="btn-aprobar">Editar</a>
                    </div>
                </div>';
            }
        }
        ?>
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