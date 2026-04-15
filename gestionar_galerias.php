<?php
session_start();

// Solo centrocultural puede acceder — artistas y demás roles quedan bloqueados
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'centrocultural') {
    header("Location: ing.html");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// ── Eliminar una foto ──────────────────────────────────────────
if (isset($_GET['eliminar_foto'])) {
    $id = intval($_GET['eliminar_foto']);
    $foto = mysqli_fetch_assoc(mysqli_query($conn, "SELECT imagen FROM galerias WHERE id = $id"));
    if ($foto && !empty($foto['imagen']) && file_exists($foto['imagen'])) {
        unlink($foto['imagen']);
    }
    mysqli_query($conn, "DELETE FROM galerias WHERE id = $id");
    header("Location: gestionar_galerias.php?exito=foto_eliminada");
    exit();
}

// ── Eliminar galería completa ──────────────────────────────────
if (isset($_GET['eliminar_galeria'])) {
    $titulo = $_GET['eliminar_galeria'];
    $fotos_res = mysqli_query($conn, "SELECT imagen FROM galerias WHERE titulo = '" . mysqli_real_escape_string($conn, $titulo) . "'");
    while ($f = mysqli_fetch_assoc($fotos_res)) {
        if (!empty($f['imagen']) && file_exists($f['imagen'])) unlink($f['imagen']);
    }
    mysqli_query($conn, "DELETE FROM galerias WHERE titulo = '" . mysqli_real_escape_string($conn, $titulo) . "'");
    header("Location: gestionar_galerias.php?exito=galeria_eliminada");
    exit();
}

// ── POST: crear / editar / agregar foto ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Crear nueva galería
    if ($accion === 'nueva_galeria') {
        $titulo      = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $artista_sel = trim($_POST['artista_galeria']);

        if (!empty($_FILES['imagen']['name'])) {
            if (!is_dir('imagenes')) mkdir('imagenes', 0777, true);
            $ext          = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $nuevo_nombre = 'galeria_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['imagen']['tmp_name'], 'imagenes/' . $nuevo_nombre);
            $imagen_path = 'imagenes/' . $nuevo_nombre;
            $pie         = trim($_POST['pie_foto'] ?? '');

            $stmt = mysqli_prepare($conn, "INSERT INTO galerias (titulo, descripcion, artista, imagen, pie_foto) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $titulo, $descripcion, $artista_sel, $imagen_path, $pie);
            mysqli_stmt_execute($stmt);
            header("Location: gestionar_galerias.php?exito=galeria_creada");
            exit();
        }

    // Editar metadatos de galería existente
    } elseif ($accion === 'editar_galeria') {
        $titulo_original = $_POST['titulo_original'];
        $titulo_nuevo    = trim($_POST['titulo_nuevo']);
        $descripcion     = trim($_POST['descripcion_edit']);
        $artista_sel     = trim($_POST['artista_edit']);

        $stmt = mysqli_prepare($conn, "UPDATE galerias SET titulo=?, descripcion=?, artista=? WHERE titulo=?");
        mysqli_stmt_bind_param($stmt, "ssss", $titulo_nuevo, $descripcion, $artista_sel, $titulo_original);
        mysqli_stmt_execute($stmt);
        header("Location: gestionar_galerias.php?exito=galeria_editada");
        exit();

    // Agregar foto a galería existente
    } elseif ($accion === 'agregar_foto') {
        $titulo_existente = $_POST['galeria_existente'];
        $res_gal  = mysqli_query($conn, "SELECT descripcion, artista FROM galerias WHERE titulo = '" . mysqli_real_escape_string($conn, $titulo_existente) . "' LIMIT 1");
        $gal_data = mysqli_fetch_assoc($res_gal);

        if (!empty($_FILES['imagen_extra']['name'])) {
            if (!is_dir('imagenes')) mkdir('imagenes', 0777, true);
            $ext          = pathinfo($_FILES['imagen_extra']['name'], PATHINFO_EXTENSION);
            $nuevo_nombre = 'galeria_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['imagen_extra']['tmp_name'], 'imagenes/' . $nuevo_nombre);
            $imagen_path = 'imagenes/' . $nuevo_nombre;
            $pie  = trim($_POST['pie_foto_extra'] ?? '');
            $desc = $gal_data['descripcion'] ?? '';
            $art  = $gal_data['artista'] ?? '';

            $stmt = mysqli_prepare($conn, "INSERT INTO galerias (titulo, descripcion, artista, imagen, pie_foto) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $titulo_existente, $desc, $art, $imagen_path, $pie);
            mysqli_stmt_execute($stmt);
            header("Location: gestionar_galerias.php?exito=foto_agregada");
            exit();
        }
    }
}

// ── Cargar galería a editar (si viene ?editar=titulo) ─────────
$galeria_editar = null;
if (isset($_GET['editar'])) {
    $titulo_editar = $_GET['editar'];
    $res = mysqli_query($conn, "SELECT titulo, descripcion, artista FROM galerias WHERE titulo = '" . mysqli_real_escape_string($conn, $titulo_editar) . "' LIMIT 1");
    $galeria_editar = mysqli_fetch_assoc($res);
}

// ── Obtener todas las galerías agrupadas ──────────────────────
$galerias_raw = mysqli_query($conn, "SELECT * FROM galerias ORDER BY titulo ASC, id ASC");
$galerias = [];
while ($row = mysqli_fetch_assoc($galerias_raw)) {
    $galerias[$row['titulo']][] = $row;
}

// ── Artistas aprobados para el select ─────────────────────────
$artistas_res = mysqli_query($conn, "SELECT nombre FROM artistas WHERE aprobado = 1 ORDER BY nombre ASC");
$artistas_lista = [];
while ($a = mysqli_fetch_assoc($artistas_res)) {
    $artistas_lista[] = $a['nombre'];
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Galerías - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .page-header h1 { font-size: 32px; color: var(--primary); }

        .layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }

        .panel-formularios { display: flex; flex-direction: column; gap: 20px; }

        .form-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
        }

        .form-card h2 {
            font-size: 18px;
            color: var(--primary);
            margin-bottom: 18px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }

        .form-group { margin-bottom: 14px; }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text);
            font-family: 'Cormorant Garamond', serif;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text);
            font-family: 'Jost', sans-serif;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .form-group textarea { height: 70px; resize: vertical; }

        .btn-guardar {
            width: 100%;
            padding: 11px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 17px;
            letter-spacing: 2px;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 8px;
        }

        .btn-guardar:hover { background-color: var(--primary-dark); }

        .btn-cancelar {
            display: block;
            width: 100%;
            padding: 9px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: border-color 0.2s;
        }

        .btn-cancelar:hover { border-color: var(--primary); color: var(--primary); }

        /* Lista de galerías */
        .galeria-bloque {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .galeria-bloque-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
            gap: 12px;
        }

        .galeria-bloque-header h3 { font-size: 20px; color: var(--primary); }

        .galeria-bloque-header p {
            color: var(--text-secondary);
            font-size: 13px;
            margin-top: 4px;
        }

        .header-acciones {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 110px;
        }

        .btn-editar-galeria {
            padding: 6px 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Jost', sans-serif;
            text-align: center;
            transition: background 0.2s;
        }

        .btn-editar-galeria:hover { background-color: var(--primary-dark); }

        .btn-eliminar-galeria {
            padding: 6px 14px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Jost', sans-serif;
            text-align: center;
            transition: border-color 0.2s;
        }

        .btn-eliminar-galeria:hover { border-color: #cc0000; color: #cc0000; }

        .fotos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
        }

        .foto-item {
            position: relative;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .foto-item img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            display: block;
        }

        .foto-item .pie {
            padding: 6px 10px;
            color: var(--text-secondary);
            font-size: 12px;
            font-style: italic;
            background-color: var(--card-bg);
        }

        .btn-eliminar-foto {
            position: absolute;
            top: 6px;
            right: 6px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-eliminar-foto:hover { background: #cc0000; }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid var(--primary);
            background-color: rgba(173, 102, 108, 0.15);
            color: var(--primary);
        }

        .sin-galerias {
            color: var(--text-secondary);
            font-size: 15px;
            text-align: center;
            padding: 40px 0;
        }

        .form-card.editando {
            border-color: var(--primary);
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
                <li><a href="gestionar_eventos.php">Eventos</a></li>
                <li><a href="gestionar_galerias.php" class="active">Galerías</a></li>
                <li><a href="artistas.php">Ver catálogo</a></li>
                <li><a href="galeria.php">Ver galería</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Gestionar Galerías</h1>
            <a href="logout.php" style="text-decoration:none; color: var(--text-secondary); font-size: 13px; border: 1px solid var(--border); padding: 8px 16px; border-radius: 4px;">Cerrar sesión</a>
        </div>

        <?php if (isset($_GET['exito'])): ?>
        <div class="alert">
            ✓ <?php
                $msgs = [
                    'galeria_creada'   => 'Galería creada correctamente.',
                    'galeria_editada'  => 'Galería actualizada correctamente.',
                    'foto_agregada'    => 'Foto agregada correctamente.',
                    'foto_eliminada'   => 'Foto eliminada correctamente.',
                    'galeria_eliminada'=> 'Galería eliminada correctamente.',
                ];
                echo $msgs[$_GET['exito']] ?? 'Operación realizada.';
            ?>
        </div>
        <?php endif; ?>

        <div class="layout">
            <!-- ── Panel izquierdo: formularios ── -->
            <div class="panel-formularios">

                <?php if ($galeria_editar): ?>
                <!-- ── Editar galería existente ── -->
                <div class="form-card editando">
                    <h2>Editar Galería</h2>
                    <form method="POST">
                        <input type="hidden" name="accion" value="editar_galeria">
                        <input type="hidden" name="titulo_original" value="<?= htmlspecialchars($galeria_editar['titulo']) ?>">

                        <div class="form-group">
                            <label>Título</label>
                            <input type="text" name="titulo_nuevo" value="<?= htmlspecialchars($galeria_editar['titulo']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Artista</label>
                            <select name="artista_edit">
                                <option value="">Sin artista específico</option>
                                <?php foreach ($artistas_lista as $nombre): ?>
                                <option value="<?= htmlspecialchars($nombre) ?>" <?= ($galeria_editar['artista'] == $nombre) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($nombre) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion_edit"><?= htmlspecialchars($galeria_editar['descripcion'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn-guardar">Guardar cambios</button>
                        <a href="gestionar_galerias.php" class="btn-cancelar">Cancelar</a>
                    </form>
                </div>

                <?php else: ?>
                <!-- ── Nueva galería ── -->
                <div class="form-card">
                    <h2>Nueva Galería</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="nueva_galeria">

                        <div class="form-group">
                            <label>Título de la galería</label>
                            <input type="text" name="titulo" required placeholder="Ej: Exposición de primavera">
                        </div>

                        <div class="form-group">
                            <label>Artista</label>
                            <select name="artista_galeria">
                                <option value="">Sin artista específico</option>
                                <?php foreach ($artistas_lista as $nombre): ?>
                                <option value="<?= htmlspecialchars($nombre) ?>"><?= htmlspecialchars($nombre) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" placeholder="Describe brevemente la galería..."></textarea>
                        </div>

                        <div class="form-group">
                            <label>Primera imagen</label>
                            <input type="file" name="imagen" accept="image/*" required>
                        </div>

                        <div class="form-group">
                            <label>Pie de foto</label>
                            <input type="text" name="pie_foto" placeholder="Descripción de la imagen (opcional)">
                        </div>

                        <button type="submit" class="btn-guardar">Crear galería</button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- ── Agregar foto a galería existente ── -->
                <?php if (!empty($galerias)): ?>
                <div class="form-card">
                    <h2>Agregar Foto</h2>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="agregar_foto">

                        <div class="form-group">
                            <label>Galería destino</label>
                            <select name="galeria_existente" required>
                                <?php foreach ($galerias as $titulo => $fotos): ?>
                                <option value="<?= htmlspecialchars($titulo) ?>"
                                    <?= (isset($galeria_editar) && $galeria_editar && $galeria_editar['titulo'] == $titulo) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($titulo) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Imagen</label>
                            <input type="file" name="imagen_extra" accept="image/*" required>
                        </div>

                        <div class="form-group">
                            <label>Pie de foto</label>
                            <input type="text" name="pie_foto_extra" placeholder="Descripción de la imagen (opcional)">
                        </div>

                        <button type="submit" class="btn-guardar">Agregar foto</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>

            <!-- ── Panel derecho: lista de galerías ── -->
            <div class="galerias-lista">
                <?php if (empty($galerias)): ?>
                    <p class="sin-galerias">No hay galerías creadas aún. ¡Crea la primera!</p>
                <?php else: ?>
                    <?php foreach ($galerias as $titulo => $fotos): ?>
                    <div class="galeria-bloque" id="galeria-<?= urlencode($titulo) ?>">
                        <div class="galeria-bloque-header">
                            <div>
                                <h3><?= htmlspecialchars($titulo) ?></h3>
                                <?php if (!empty($fotos[0]['artista'])): ?>
                                <p>Artista: <?= htmlspecialchars($fotos[0]['artista']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($fotos[0]['descripcion'])): ?>
                                <p><?= htmlspecialchars($fotos[0]['descripcion']) ?></p>
                                <?php endif; ?>
                                <p style="margin-top:4px;"><?= count($fotos) ?> foto(s)</p>
                            </div>
                            <div class="header-acciones">
                                <a href="?editar=<?= urlencode($titulo) ?>#panel-form"
                                   class="btn-editar-galeria">Editar</a>
                                <a href="?eliminar_galeria=<?= urlencode($titulo) ?>"
                                   class="btn-eliminar-galeria"
                                   onclick="return confirm('¿Eliminar la galería «<?= htmlspecialchars($titulo) ?>» y todas sus fotos?')">
                                   Eliminar
                                </a>
                            </div>
                        </div>

                        <div class="fotos-grid">
                            <?php foreach ($fotos as $foto): ?>
                            <div class="foto-item">
                                <img src="<?= htmlspecialchars($foto['imagen']) ?>" alt="<?= htmlspecialchars($foto['pie_foto'] ?? '') ?>">
                                <a href="?eliminar_foto=<?= $foto['id'] ?>"
                                   class="btn-eliminar-foto"
                                   onclick="return confirm('¿Eliminar esta foto?')">✕</a>
                                <?php if (!empty($foto['pie_foto'])): ?>
                                <p class="pie"><?= htmlspecialchars($foto['pie_foto']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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

        // Scroll al formulario al dar click en Editar
        <?php if ($galeria_editar): ?>
        window.scrollTo({ top: 0, behavior: 'smooth' });
        <?php endif; ?>
    </script>
</body>
</html>
