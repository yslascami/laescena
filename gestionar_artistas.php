<?php
session_start();

// Solo superadmin puede gestionar artistas
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ing.html");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// ── Aprobar ────────────────────────────────────────────────────
if (isset($_GET['aprobar'])) {
    $id = intval($_GET['aprobar']);
    mysqli_query($conn, "UPDATE artistas SET aprobado = 1 WHERE id = $id");
    header("Location: gestionar_artistas.php?exito=aprobado");
    exit();
}

// ── Desaprobar ─────────────────────────────────────────────────
if (isset($_GET['rechazar'])) {
    $id = intval($_GET['rechazar']);
    mysqli_query($conn, "UPDATE artistas SET aprobado = 0 WHERE id = $id");
    header("Location: gestionar_artistas.php?exito=desaprobado");
    exit();
}

// ── Eliminar artista (artista + usuario) ──────────────────────
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);

    // Obtener correo antes de borrar para eliminar también de users
    $res = mysqli_query($conn, "SELECT correo, foto_perfil FROM artistas WHERE id = $id");
    $art = mysqli_fetch_assoc($res);

    if ($art) {
        // Eliminar foto si existe
        if (!empty($art['foto_perfil']) && file_exists($art['foto_perfil'])) {
            unlink($art['foto_perfil']);
        }
        // Eliminar de artistas
        mysqli_query($conn, "DELETE FROM artistas WHERE id = $id");
        // Eliminar usuario asociado
        $correo = mysqli_real_escape_string($conn, $art['correo']);
        mysqli_query($conn, "DELETE FROM users WHERE email = '$correo'");
    }

    header("Location: gestionar_artistas.php?exito=eliminado");
    exit();
}

// ── Obtener todos los artistas ────────────────────────────────
$result = mysqli_query($conn, "SELECT * FROM artistas ORDER BY aprobado ASC, nombre ASC");

$aprobados  = [];
$pendientes = [];
while ($artista = mysqli_fetch_assoc($result)) {
    if ($artista['aprobado']) $aprobados[]  = $artista;
    else                      $pendientes[] = $artista;
}
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

        .page-header h1 { font-size: 32px; color: var(--primary); }

        .artista-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 14px;
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
            flex-shrink: 0;
        }

        .artista-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .artista-info h3 { font-size: 17px; color: var(--text); margin-bottom: 4px; }

        .artista-info .detalle {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 2px;
        }

        .artista-info .contacto {
            color: var(--primary);
            font-size: 12px;
            margin-top: 4px;
        }

        .acciones {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 130px;
        }

        .btn-aprobar {
            padding: 7px 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s;
        }

        .btn-aprobar:hover { background-color: var(--primary-dark); }

        .btn-secundario {
            padding: 7px 14px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            text-decoration: none;
            text-align: center;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-secundario:hover { border-color: var(--primary); color: var(--primary); }

        .btn-eliminar {
            padding: 7px 14px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            text-decoration: none;
            text-align: center;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-eliminar:hover { border-color: #cc0000; color: #cc0000; }

        .estado-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .aprobado  { background-color: rgba(173,102,108,0.2); color: var(--primary); }
        .pendiente { background-color: rgba(170,170,170,0.2); color: var(--text-secondary); }

        .seccion-titulo {
            font-size: 20px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin: 28px 0 16px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid var(--primary);
            background-color: rgba(173, 102, 108, 0.15);
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
                <li><a href="panel_admin.php">Panel Admin</a></li>
                <li><a href="gestionar_artistas.php" class="active">Artistas</a></li>
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
            <a href="logout.php" style="text-decoration:none; color:var(--text-secondary); font-size:13px; border:1px solid var(--border); padding:8px 16px; border-radius:4px;">Cerrar sesión</a>
        </div>

        <?php if (isset($_GET['exito'])): ?>
        <div class="alert">
            ✓ <?php
                $msgs = [
                    'aprobado'   => 'Artista aprobado correctamente.',
                    'desaprobado'=> 'Artista desaprobado.',
                    'eliminado'  => 'Perfil de artista y usuario eliminados correctamente.',
                ];
                echo $msgs[$_GET['exito']] ?? 'Operación realizada.';
            ?>
        </div>
        <?php endif; ?>

        <!-- Pendientes -->
        <?php if (!empty($pendientes)): ?>
        <h2 class="seccion-titulo">Pendientes de aprobación (<?= count($pendientes) ?>)</h2>
        <?php foreach ($pendientes as $artista): ?>
        <?php $inicial = strtoupper(mb_substr($artista['nombre'], 0, 1)); ?>
        <div class="artista-card">
            <div class="artista-avatar"><?= $inicial ?></div>
            <div class="artista-info">
                <span class="estado-badge pendiente">Pendiente</span>
                <h3><?= htmlspecialchars($artista['nombre']) ?></h3>
                <p class="detalle"><?= htmlspecialchars($artista['disciplina'] ?? 'Sin disciplina') ?></p>
                <p class="contacto">✉ <?= htmlspecialchars($artista['correo']) ?></p>
                <?php if (!empty($artista['teléfono'])): ?>
                <p class="contacto">☎ <?= htmlspecialchars($artista['teléfono']) ?></p>
                <?php endif; ?>
            </div>
            <div class="acciones">
                <a href="?aprobar=<?= $artista['id'] ?>" class="btn-aprobar">Aprobar</a>
                <a href="editar_artista.php?id=<?= $artista['id'] ?>" class="btn-secundario">Editar</a>
                <a href="?eliminar=<?= $artista['id'] ?>" class="btn-eliminar"
                   onclick="return confirm('¿Eliminar el perfil de <?= htmlspecialchars($artista['nombre']) ?> y su cuenta de usuario? Esta acción no se puede deshacer.')">
                   Eliminar
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Aprobados -->
        <?php if (!empty($aprobados)): ?>
        <h2 class="seccion-titulo">Artistas aprobados (<?= count($aprobados) ?>)</h2>
        <?php foreach ($aprobados as $artista): ?>
        <?php $inicial = strtoupper(mb_substr($artista['nombre'], 0, 1)); ?>
        <div class="artista-card">
            <div class="artista-avatar">
                <?php if (!empty($artista['foto_perfil'])): ?>
                    <img src="<?= htmlspecialchars($artista['foto_perfil']) ?>" alt="">
                <?php else: ?>
                    <?= $inicial ?>
                <?php endif; ?>
            </div>
            <div class="artista-info">
                <span class="estado-badge aprobado">Aprobado</span>
                <h3><?= htmlspecialchars($artista['nombre']) ?></h3>
                <p class="detalle"><?= htmlspecialchars($artista['disciplina'] ?? 'Sin disciplina') ?></p>
                <p class="contacto">✉ <?= htmlspecialchars($artista['correo']) ?></p>
                <?php if (!empty($artista['teléfono'])): ?>
                <p class="contacto">☎ <?= htmlspecialchars($artista['teléfono']) ?></p>
                <?php endif; ?>
            </div>
            <div class="acciones">
                <a href="editar_artista.php?id=<?= $artista['id'] ?>" class="btn-aprobar">Editar</a>
                <a href="?rechazar=<?= $artista['id'] ?>" class="btn-secundario">Desaprobar</a>
                <a href="?eliminar=<?= $artista['id'] ?>" class="btn-eliminar"
                   onclick="return confirm('¿Eliminar el perfil de <?= htmlspecialchars($artista['nombre']) ?> y su cuenta de usuario? Esta acción no se puede deshacer.')">
                   Eliminar
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (empty($pendientes) && empty($aprobados)): ?>
        <p style="color:var(--text-secondary); margin-top:40px; text-align:center;">No hay artistas registrados aún.</p>
        <?php endif; ?>
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
