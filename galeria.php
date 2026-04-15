<?php
session_start();
$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) die("Error de conexión: " . mysqli_connect_error());
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería - La Escena</title>
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

        .galeria-titulo {
            font-size: 24px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 20px;
            margin-top: 30px;
        }

        .galeria-descripcion {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .galeria-artista {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 24px;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .galeria-artista a {
            color: var(--primary);
            text-decoration: none;
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s;
        }

        .galeria-artista a:hover {
            border-bottom-color: var(--primary);
        }

        .fotos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .foto-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
        }

        .foto-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .foto-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
        }

        .foto-card .pie-foto {
            padding: 12px 16px;
            color: var(--text-secondary);
            font-size: 13px;
            font-style: italic;
            font-family: 'Cormorant Garamond', serif;
        }

        .no-galerias {
            text-align: center;
            color: var(--text-secondary);
            font-size: 18px;
            margin-top: 40px;
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
                <li><a href="CC.php">Centro Cultural</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php" class="active">Galería</a></li>
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
            <h1>Galerías</h1>
            <p>Exposiciones y muestras artísticas en La Escena</p>
        </div>

        <?php
        $sql_galerias = "SELECT DISTINCT titulo, descripcion, artista FROM galerias";
        $result_galerias = mysqli_query($conn, $sql_galerias);

        if (mysqli_num_rows($result_galerias) > 0) {
            while ($galeria = mysqli_fetch_assoc($result_galerias)) {
                echo '<h2 class="galeria-titulo">' . htmlspecialchars($galeria['titulo']) . '</h2>';

                if (!empty($galeria['descripcion'])) {
                    echo '<p class="galeria-descripcion">' . htmlspecialchars($galeria['descripcion']) . '</p>';
                }

                // Mostrar artista como enlace si existe en la tabla artistas
                if (!empty($galeria['artista'])) {
                    $nombre_artista = mysqli_real_escape_string($conn, $galeria['artista']);
                    $res_art = mysqli_query($conn, "SELECT id FROM artistas WHERE nombre = '$nombre_artista' LIMIT 1");
                    $art_row = mysqli_fetch_assoc($res_art);
                    if ($art_row) {
                        echo '<p class="galeria-artista">Artista: <a href="ver_artista.php?id=' . $art_row['id'] . '">' . htmlspecialchars($galeria['artista']) . '</a></p>';
                    } else {
                        echo '<p class="galeria-artista">Artista: <span>' . htmlspecialchars($galeria['artista']) . '</span></p>';
                    }
                }

                $titulo = mysqli_real_escape_string($conn, $galeria['titulo']);
                $sql_fotos = "SELECT * FROM galerias WHERE titulo = '$titulo'";
                $result_fotos = mysqli_query($conn, $sql_fotos);

                echo '<div class="fotos-grid">';
                while ($foto = mysqli_fetch_assoc($result_fotos)) {
                    echo '<div class="foto-card">
                        <img src="' . htmlspecialchars($foto['imagen']) . '" alt="' . htmlspecialchars($foto['titulo']) . '">';
                    if (!empty($foto['pie_foto'])) {
                        echo '<p class="pie-foto">' . htmlspecialchars($foto['pie_foto']) . '</p>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            }
        } else {
            echo '<p class="no-galerias">No hay galerías disponibles.</p>';
        }
        mysqli_close($conn);
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