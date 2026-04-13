<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'superadmin' && $_SESSION['role'] != 'centrocultural')) {
    header("Location: ing.html");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

if (!isset($_GET['id'])) {
    header("Location: gestionar_artistas.php");
    exit();
}

$id = intval($_GET['id']);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];

    $foto_perfil = $_POST['foto_perfil_actual'];
    if (!empty($_FILES['foto_perfil']['name'])) {
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nuevo_nombre = 'perfil_' . $id . '.' . $ext;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], 'imagenes/' . $nuevo_nombre);
        $foto_perfil = 'imagenes/' . $nuevo_nombre;
    }

    $foto_portada = $_POST['foto_portada_actual'];
    if (!empty($_FILES['foto_portada']['name'])) {
        $ext = pathinfo($_FILES['foto_portada']['name'], PATHINFO_EXTENSION);
        $nuevo_nombre = 'portada_' . $id . '.' . $ext;
        move_uploaded_file($_FILES['foto_portada']['tmp_name'], 'imagenes/' . $nuevo_nombre);
        $foto_portada = 'imagenes/' . $nuevo_nombre;
    }

    $sql = "UPDATE artistas SET descripcion=?, foto_perfil=?, foto_portada=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $descripcion, $foto_perfil, $foto_portada, $id);
    mysqli_stmt_execute($stmt);
    $exito = true;
}

// Obtener datos del artista
$result = mysqli_query($conn, "SELECT * FROM artistas WHERE id = $id");
$artista = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Artista - La Escena</title>
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

        .btn-volver {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 13px;
            border: 1px solid var(--border);
            padding: 8px 16px;
            border-radius: 4px;
            transition: border-color 0.2s;
        }

        .btn-volver:hover { border-color: var(--primary); color: var(--primary); }

        .fotos-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .foto-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
        }

        .foto-card h3 {
            font-size: 16px;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .foto-preview {
            width: 100%;
            height: 160px;
            background-color: var(--bg);
            border: 1px dashed var(--border);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .foto-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .foto-preview .sin-foto {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .foto-card input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: none;
            color: var(--text-secondary);
            font-size: 12px;
            cursor: pointer;
        }

        .form-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
        }

        .form-card h2 {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }

        .artista-info {
            background-color: var(--bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .artista-info h3 {
            font-size: 18px;
            color: var(--text);
            margin-bottom: 6px;
        }

        .artista-info p {
            color: var(--text-secondary);
            font-size: 13px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text);
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            letter-spacing: 1px;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text);
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            height: 120px;
            resize: vertical;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn-guardar {
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
            margin-top: 8px;
        }

        .btn-guardar:hover { background-color: var(--primary-dark); }

        .alert-exito {
            background-color: rgba(173, 102, 108, 0.2);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
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
                <?php if ($_SESSION['role'] == 'superadmin'): ?>
                <li><a href="panel_admin.php">Panel Admin</a></li>
                <?php else: ?>
                <li><a href="panel_cc.php">Panel</a></li>
                <?php endif; ?>
                <li><a href="gestionar_artistas.php" class="active">Artistas</a></li>
                <li><a href="gestionar_eventos.php">Eventos</a></li>
                <li><a href="artistas.php">Ver catálogo</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Editar Artista</h1>
            <a href="gestionar_artistas.php" class="btn-volver">← Volver</a>
        </div>

        <?php if (isset($exito)): ?>
        <div class="alert-exito">✓ Artista actualizado correctamente</div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="foto_perfil_actual" value="<?= htmlspecialchars($artista['foto_perfil'] ?? '') ?>">
            <input type="hidden" name="foto_portada_actual" value="<?= htmlspecialchars($artista['foto_portada'] ?? '') ?>">

            <!-- Info del artista (solo lectura) -->
            <div class="artista-info">
                <h3><?= htmlspecialchars($artista['nombre']) ?></h3>
                <p>✉ <?= htmlspecialchars($artista['correo']) ?> | 🎨 <?= htmlspecialchars($artista['disciplina'] ?? 'Sin disciplina') ?></p>
            </div>

            <!-- Fotos -->
            <div class="fotos-grid">
                <div class="foto-card">
                    <h3>Foto de perfil</h3>
                    <div class="foto-preview">
                        <?php if (!empty($artista['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($artista['foto_perfil']) ?>" alt="Foto de perfil">
                        <?php else: ?>
                            <span class="sin-foto">Sin foto de perfil</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="foto_perfil" accept="image/*">
                </div>
                <div class="foto-card">
                    <h3>Foto de portada</h3>
                    <div class="foto-preview">
                        <?php if (!empty($artista['foto_portada'])): ?>
                            <img src="<?= htmlspecialchars($artista['foto_portada']) ?>" alt="Foto de portada">
                        <?php else: ?>
                            <span class="sin-foto">Sin foto de portada</span>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="foto_portada" accept="image/*">
                </div>
            </div>

            <!-- Solo descripción -->
            <div class="form-card">
                <h2>Editar descripción</h2>
                <div class="form-group">
                    <label>Descripción / Biografía</label>
                    <textarea name="descripcion"><?= htmlspecialchars($artista['descripcion'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-guardar">Guardar cambios</button>
            </div>
        </form>
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