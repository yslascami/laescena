<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'artista') {
    header("Location: ing.html");
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// Crear tabla portafolio si no existe (con tipo 'otro' añadido)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS portafolio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artista_id INT NOT NULL,
    tipo ENUM('imagen','video','audio','documento','otro') NOT NULL DEFAULT 'otro',
    archivo VARCHAR(500) NOT NULL,
    titulo VARCHAR(255) DEFAULT '',
    descripcion TEXT DEFAULT '',
    nombre_original VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");


$artista_id = $_SESSION['artista_id'];

// ── Función para detectar tipo según extensión ─────────────────
function detectarTipo($ext) {
    $ext = strtolower($ext);
    if (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','tiff','svg'])) return 'imagen';
    if (in_array($ext, ['mp4','webm','mov','avi','mkv','flv','wmv'])) return 'video';
    if (in_array($ext, ['mp3','wav','ogg','m4a','flac','aac','wma'])) return 'audio';
    if (in_array($ext, ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','rtf'])) return 'documento';
    return 'otro';
}

// ── Eliminar ítem ──────────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $res = mysqli_query($conn, "SELECT archivo FROM portafolio WHERE id = $id AND artista_id = $artista_id");
    $item = mysqli_fetch_assoc($res);
    if ($item) {
        if (!empty($item['archivo']) && file_exists($item['archivo'])) unlink($item['archivo']);
        mysqli_query($conn, "DELETE FROM portafolio WHERE id = $id AND artista_id = $artista_id");
    }
    header("Location: portafolio.php?exito=eliminado");
    exit();
}

// ── Editar ítem ────────────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'editar_item') {
    $id          = intval($_POST['item_id']);
    $titulo      = trim($_POST['titulo_item']);
    $descripcion = trim($_POST['descripcion_item']);

    $res_actual = mysqli_query($conn, "SELECT archivo, tipo, nombre_original FROM portafolio WHERE id = $id AND artista_id = $artista_id");
    $actual = mysqli_fetch_assoc($res_actual);

    if ($actual) {
        $ruta_archivo    = $actual['archivo'];
        $nombre_original = $actual['nombre_original'];

        if (!empty($_FILES['archivo_nuevo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['archivo_nuevo']['name'], PATHINFO_EXTENSION));
            $tipo_nuevo = detectarTipo($ext);
            if (!empty($ruta_archivo) && file_exists($ruta_archivo)) unlink($ruta_archivo);
            $carpeta = 'portafolio/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
            $nombre_nuevo = 'port_' . $artista_id . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['archivo_nuevo']['tmp_name'], $carpeta . $nombre_nuevo);
            $ruta_archivo    = $carpeta . $nombre_nuevo;
            $nombre_original = $_FILES['archivo_nuevo']['name'];
            $actual['tipo']  = $tipo_nuevo;
        }

        $stmt = mysqli_prepare($conn, "UPDATE portafolio SET titulo=?, descripcion=?, archivo=?, tipo=?, nombre_original=? WHERE id=? AND artista_id=?");
        mysqli_stmt_bind_param($stmt, "sssssii", $titulo, $descripcion, $ruta_archivo, $actual['tipo'], $nombre_original, $id, $artista_id);
        mysqli_stmt_execute($stmt);
    }
    header("Location: portafolio.php?exito=editado");
    exit();
}

// ── Actualizar disciplina ──────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_disciplina') {
    $disciplina = $_POST['disciplina'];
    $stmt = mysqli_prepare($conn, "UPDATE artistas SET disciplina=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $disciplina, $artista_id);
    mysqli_stmt_execute($stmt);
    header("Location: portafolio.php?exito=disciplina");
    exit();
}

// ── Actualizar foto de portada ─────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar_portada') {
    $foto_portada_actual = $_POST['foto_portada_actual'] ?? '';
    $nueva_ruta = $foto_portada_actual;

    if (!empty($_FILES['foto_portada']['name'])) {
        $ext = strtolower(pathinfo($_FILES['foto_portada']['name'], PATHINFO_EXTENSION));
        $carpeta = 'imagenes/';
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);
        $nombre = 'portada_' . $artista_id . '.' . $ext;
        if (!empty($foto_portada_actual) && file_exists($foto_portada_actual) && $foto_portada_actual !== $carpeta . $nombre) {
            unlink($foto_portada_actual);
        }
        move_uploaded_file($_FILES['foto_portada']['tmp_name'], $carpeta . $nombre);
        $nueva_ruta = $carpeta . $nombre;
    }

    $stmt = mysqli_prepare($conn, "UPDATE artistas SET foto_portada=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "si", $nueva_ruta, $artista_id);
    mysqli_stmt_execute($stmt);
    header("Location: portafolio.php?exito=portada");
    exit();
}

// ── Subir múltiples archivos ───────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'subir') {
    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    $carpeta = 'portafolio/';
    if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

    $archivos = $_FILES['archivos'];
    $total    = count($archivos['name']);
    $subidos  = 0;

    for ($i = 0; $i < $total; $i++) {
        if ($archivos['error'][$i] !== UPLOAD_ERR_OK || empty($archivos['name'][$i])) continue;

        $ext             = strtolower(pathinfo($archivos['name'][$i], PATHINFO_EXTENSION));
        $tipo            = detectarTipo($ext);
        $nombre_original = $archivos['name'][$i];
        $nombre_archivo  = 'port_' . $artista_id . '_' . time() . '_' . $i . '.' . $ext;

        move_uploaded_file($archivos['tmp_name'][$i], $carpeta . $nombre_archivo);
        $ruta = $carpeta . $nombre_archivo;

        // El título solo se aplica si se sube un archivo único
        $titulo_item = ($total === 1) ? $titulo : '';
        $desc_item   = ($total === 1) ? $descripcion : '';

        $stmt = mysqli_prepare($conn, "INSERT INTO portafolio (artista_id, tipo, archivo, titulo, descripcion, nombre_original) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "isssss", $artista_id, $tipo, $ruta, $titulo_item, $desc_item, $nombre_original);
        mysqli_stmt_execute($stmt);
        $subidos++;
    }

    if ($subidos > 0) {
        header("Location: portafolio.php?exito=subido&n=$subidos");
        exit();
    }
}

// ── Datos del artista y portafolio ────────────────────────────
$res_artista = mysqli_query($conn, "SELECT * FROM artistas WHERE id = $artista_id");
$artista     = mysqli_fetch_assoc($res_artista);

$res_items = mysqli_query($conn, "SELECT * FROM portafolio WHERE artista_id = $artista_id ORDER BY created_at DESC");
$items = [];
while ($row = mysqli_fetch_assoc($res_items)) $items[] = $row;

$disciplinas = ['Pintura','Escultura','Fotografía','Música','Danza','Teatro','Literatura','Cine','Arte Digital','Otra'];

// Iconos por tipo de archivo
function iconoTipo($tipo, $ext = '') {
    switch ($tipo) {
        case 'imagen':    return '';
        case 'video':     return '';
        case 'audio':     return '';
        case 'documento': return '';
        default:          return '';
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Portafolio - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-header h1 { font-size:32px; color:var(--primary); }

        .layout { display:grid; grid-template-columns:320px 1fr; gap:24px; align-items:start; }

        .panel-izq { display:flex; flex-direction:column; gap:20px; }

        .form-card {
            background-color:var(--card-bg);
            border:1px solid var(--border);
            border-radius:4px;
            padding:22px;
        }
        .form-card h2 {
            font-size:17px; color:var(--primary);
            margin-bottom:16px; border-bottom:1px solid var(--border); padding-bottom:10px;
        }
        .form-group { margin-bottom:13px; }
        .form-group label {
            display:block; margin-bottom:5px; color:var(--text);
            font-family:'Cormorant Garamond',serif; font-size:14px; letter-spacing:1px;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width:100%; padding:10px;
            border:1px solid var(--border); border-radius:4px;
            background-color:var(--input-bg); color:var(--text);
            font-family:'Jost',sans-serif; font-size:14px;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { outline:none; border-color:var(--primary); }
        .form-group textarea { height:70px; resize:vertical; }

        .btn-guardar {
            width:100%; padding:11px;
            background-color:var(--primary); color:white; border:none;
            border-radius:4px; font-family:'Cormorant Garamond',serif;
            font-size:17px; letter-spacing:2px; cursor:pointer; transition:background 0.2s;
        }
        .btn-guardar:hover { background-color:var(--primary-dark); }

        /* Portada preview */
        .portada-preview {
            width:100%; height:120px;
            background-color:var(--bg);
            border:1px dashed var(--border);
            border-radius:4px;
            display:flex; align-items:center; justify-content:center;
            overflow:hidden; margin-bottom:10px;
        }
        .portada-preview img { width:100%; height:100%; object-fit:cover; }
        .portada-preview .sin-portada { color:var(--text-secondary); font-size:13px; }

        /* Zona de subida múltiple */
        .dropzone {
            border:2px dashed var(--border);
            border-radius:4px;
            padding:24px 16px;
            text-align:center;
            cursor:pointer;
            transition:border-color 0.2s, background 0.2s;
            background:var(--bg);
            margin-bottom:10px;
        }
        .dropzone:hover, .dropzone.drag-over {
            border-color:var(--primary);
            background:rgba(173,102,108,0.05);
        }
        .dropzone .dz-icon { font-size:28px; margin-bottom:6px; }
        .dropzone .dz-text { color:var(--text-secondary); font-size:13px; line-height:1.5; }
        .dropzone .dz-text strong { color:var(--primary); }

        /* Lista de archivos seleccionados */
        .archivos-seleccionados {
            display:none;
            flex-direction:column;
            gap:6px;
            margin-bottom:10px;
            max-height:160px;
            overflow-y:auto;
        }
        .archivo-chip {
            display:flex; align-items:center; gap:8px;
            background:var(--bg); border:1px solid var(--border);
            border-radius:4px; padding:6px 10px; font-size:12px;
            color:var(--text-secondary);
        }
        .archivo-chip .chip-icono { font-size:16px; }
        .archivo-chip .chip-nombre { flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .archivo-chip .chip-size { color:var(--text-secondary); font-size:11px; flex-shrink:0; }

        .archivos-count {
            font-size:12px; color:var(--primary);
            margin-bottom:8px; display:none;
        }

        /* Grid de ítems */
        .items-header {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:16px;
        }
        .items-header h2 { font-size:20px; color:var(--primary); }
        .items-count { font-size:13px; color:var(--text-secondary); }

        .items-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill, minmax(220px,1fr));
            gap:16px;
        }

        .item-card {
            background-color:var(--card-bg);
            border:1px solid var(--border);
            border-radius:4px;
            overflow:hidden;
            position:relative;
            transition:transform 0.2s, border-color 0.2s;
        }
        .item-card:hover { transform:translateY(-3px); border-color:var(--primary); }

        .item-card img { width:100%; height:160px; object-fit:cover; display:block; }
        .item-card video { width:100%; max-height:160px; display:block; background:#000; }
        .item-card audio { width:100%; display:block; background:var(--bg); padding:10px; }

        /* Placeholder para docs/otros */
        .item-placeholder {
            width:100%; height:160px;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center;
            background:var(--bg); gap:8px;
        }
        .item-placeholder .ph-icon { font-size:40px; }
        .item-placeholder .ph-ext {
            font-size:11px; color:var(--text-secondary);
            text-transform:uppercase; letter-spacing:1px;
        }

        .item-meta { padding:10px 12px; }
        .item-tipo {
            display:inline-block;
            background:rgba(173,102,108,0.2); color:var(--primary);
            padding:2px 8px; border-radius:4px; font-size:11px; margin-bottom:5px;
        }
        .item-titulo { font-size:14px; color:var(--text); margin-bottom:3px; font-weight:500; }
        .item-nombre { font-size:11px; color:var(--text-secondary); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .item-desc   { font-size:12px; color:var(--text-secondary); line-height:1.4; margin-top:3px; }

        .item-acciones {
            display:flex; gap:6px; padding:0 12px 10px;
        }
        .btn-edit-item, .btn-del-item {
            flex:1; padding:5px 8px; background:none;
            border:1px solid var(--border); border-radius:4px;
            color:var(--text-secondary); font-size:12px; cursor:pointer;
            font-family:'Jost',sans-serif; text-align:center; text-decoration:none;
            transition:border-color 0.2s, color 0.2s;
        }
        .btn-edit-item:hover { border-color:var(--primary); color:var(--primary); }
        .btn-del-item:hover  { border-color:#cc0000; color:#cc0000; }

        .alert {
            padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:14px;
            border:1px solid var(--primary); background-color:rgba(173,102,108,0.15);
            color:var(--primary);
        }

        .sin-items {
            color:var(--text-secondary); font-size:15px;
            text-align:center; padding:60px 0;
            grid-column:1/-1;
        }

        /* Modal edición */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(0,0,0,0.7); z-index:1000;
            align-items:center; justify-content:center;
        }
        .modal-overlay.open { display:flex; }
        .modal {
            background:var(--card-bg); border:1px solid var(--border);
            border-radius:4px; padding:28px; width:480px; max-width:94vw;
            max-height:90vh; overflow-y:auto;
        }
        .modal h3 { font-size:18px; color:var(--primary); margin-bottom:18px; }
        .modal-acciones { display:flex; gap:10px; margin-top:16px; }
        .btn-cancelar-modal {
            flex:1; padding:10px; background:none; border:1px solid var(--border);
            border-radius:4px; color:var(--text-secondary); cursor:pointer;
            font-family:'Jost',sans-serif; font-size:13px;
        }
        .btn-cancelar-modal:hover { border-color:var(--primary); color:var(--primary); }

        .preview-actual {
            width:100%; border-radius:4px; overflow:hidden;
            background:var(--bg); border:1px solid var(--border); margin-bottom:10px;
        }
        .preview-actual img   { width:100%; max-height:160px; object-fit:cover; display:block; }
        .preview-actual video { width:100%; max-height:160px; display:block; }
        .preview-actual audio { width:100%; padding:8px; display:block; }
        .preview-actual .prev-doc {
            padding:20px; text-align:center;
            color:var(--text-secondary); font-size:28px;
        }
        .hint { color:var(--text-secondary); font-size:11px; margin-top:4px; display:block; }

        /* Filtro de tipo */
        .filtros {
            display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;
        }
        .filtro-btn {
            padding:5px 14px; border:1px solid var(--border);
            border-radius:20px; background:none; cursor:pointer;
            font-family:'Jost',sans-serif; font-size:12px; color:var(--text-secondary);
            transition:all 0.2s;
        }
        .filtro-btn:hover, .filtro-btn.activo {
            border-color:var(--primary); color:var(--primary);
            background:rgba(173,102,108,0.1);
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
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="portafolio.php" class="active">Mi Portafolio</a></li>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Mi Portafolio</h1>
        </div>

        <?php if (isset($_GET['exito'])): ?>
        <div class="alert">
            ✓ <?php
                $n = intval($_GET['n'] ?? 1);
                $msgs = [
                    'subido'     => ($n > 1 ? "$n archivos subidos correctamente." : 'Archivo subido correctamente.'),
                    'editado'    => 'Ítem actualizado correctamente.',
                    'eliminado'  => 'Ítem eliminado.',
                    'disciplina' => 'Disciplina actualizada.',
                    'portada'    => 'Foto de portada actualizada correctamente.',
                ];
                echo $msgs[$_GET['exito']] ?? 'Operación realizada.';
            ?>
        </div>
        <?php endif; ?>

        <div class="layout">
            <!-- Panel izquierdo -->
            <div class="panel-izq">

                <!-- Foto de portada -->
                <div class="form-card">
                    <h2> Foto de Portada</h2>
                    <div class="portada-preview">
                        <?php if (!empty($artista['foto_portada'])): ?>
                            <img src="<?= htmlspecialchars($artista['foto_portada']) ?>" alt="Portada">
                        <?php else: ?>
                            <span class="sin-portada">Sin foto de portada</span>
                        <?php endif; ?>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="actualizar_portada">
                        <input type="hidden" name="foto_portada_actual" value="<?= htmlspecialchars($artista['foto_portada'] ?? '') ?>">
                        <div class="form-group">
                            <label>Nueva imagen de portada</label>
                            <input type="file" name="foto_portada" accept="image/*">
                            <span class="hint">Se mostrará como banner en tu perfil público.</span>
                        </div>
                        <button type="submit" class="btn-guardar">Actualizar portada</button>
                    </form>
                </div>

                <!-- Disciplina -->
                <div class="form-card">
                    <h2>Mi Disciplina</h2>
                    <form method="POST">
                        <input type="hidden" name="accion" value="actualizar_disciplina">
                        <div class="form-group">
                            <label>Disciplina artística</label>
                            <select name="disciplina">
                                <option value="">Sin especificar</option>
                                <?php foreach ($disciplinas as $d): ?>
                                <option value="<?= $d ?>" <?= ($artista['disciplina'] == $d) ? 'selected' : '' ?>><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-guardar">Guardar disciplina</button>
                    </form>
                </div>

                <!-- Subir archivos (múltiple) -->
                <div class="form-card">
                    <h2>Subir al Portafolio</h2>
                    <form method="POST" enctype="multipart/form-data" id="form-subir">
                        <input type="hidden" name="accion" value="subir">

                        <!-- Dropzone -->
                        <div class="dropzone" id="dropzone" onclick="document.getElementById('archivos-input').click()">
                            <div class="dz-icon"></div>
                            <div class="dz-text">
                                <strong>Haz clic o arrastra archivos aquí</strong><br>
                                Imágenes, videos, audios, PDFs y más.<br>
                                Puedes seleccionar varios a la vez.
                            </div>
                        </div>
                        <input type="file" name="archivos[]" id="archivos-input"
                               multiple style="display:none" onchange="mostrarArchivos(this.files)">

                        <div class="archivos-count" id="archivos-count"></div>
                        <div class="archivos-seleccionados" id="archivos-lista"></div>

                        <div class="form-group" id="campo-titulo-wrap">
                            <label>Título <span style="color:var(--text-secondary);font-size:11px;">(solo si subes 1 archivo)</span></label>
                            <input type="text" name="titulo" id="campo-titulo" placeholder="Nombre de la obra">
                        </div>

                        <div class="form-group" id="campo-desc-wrap">
                            <label>Descripción <span style="color:var(--text-secondary);font-size:11px;">(solo si subes 1 archivo)</span></label>
                            <textarea name="descripcion" placeholder="Técnica, año, notas..."></textarea>
                        </div>

                        <button type="submit" class="btn-guardar" id="btn-subir" disabled
                                style="opacity:0.5;cursor:not-allowed;">
                            Subir archivos
                        </button>
                    </form>
                </div>

            </div>

            <!-- Panel derecho: ítems -->
            <div>
                <div class="items-header">
                    <h2>Mis archivos</h2>
                    <span class="items-count"><?= count($items) ?> elemento<?= count($items) !== 1 ? 's' : '' ?></span>
                </div>

                <!-- Filtros -->
                <?php if (!empty($items)): ?>
                <div class="filtros">
                    <button class="filtro-btn activo" onclick="filtrar('todos', this)">Todos</button>
                    <?php
                    $tipos_presentes = array_unique(array_column($items, 'tipo'));
                    $labels = ['imagen'=>'Imágenes','video'=>'Videos','audio'=>'Audios','documento'=>'Documentos','otro'=>'Otros'];
                    foreach ($tipos_presentes as $t): ?>
                    <button class="filtro-btn" onclick="filtrar('<?= $t ?>', this)"><?= $labels[$t] ?? ucfirst($t) ?></button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="items-grid" id="items-grid">
                    <?php if (empty($items)): ?>
                        <p class="sin-items">Aún no has subido nada a tu portafolio.</p>
                    <?php else: ?>
                        <?php foreach ($items as $item):
                            $ext = strtolower(pathinfo($item['archivo'], PATHINFO_EXTENSION));
                            $nombre_mostrar = !empty($item['nombre_original']) ? $item['nombre_original'] : basename($item['archivo']);
                        ?>
                        <div class="item-card" data-tipo="<?= $item['tipo'] ?>">
                            <?php if ($item['tipo'] === 'imagen'): ?>
                                <img src="<?= htmlspecialchars($item['archivo']) ?>"
                                     alt="<?= htmlspecialchars($item['titulo']) ?>">
                            <?php elseif ($item['tipo'] === 'video'): ?>
                                <video controls>
                                    <source src="<?= htmlspecialchars($item['archivo']) ?>">
                                </video>
                            <?php elseif ($item['tipo'] === 'audio'): ?>
                                <div class="item-placeholder" style="height:80px;">
                                    <div class="ph-icon"></div>
                                </div>
                                <audio controls>
                                    <source src="<?= htmlspecialchars($item['archivo']) ?>">
                                </audio>
                            <?php else: ?>
                                <div class="item-placeholder">
                                    <div class="ph-icon"><?= iconoTipo($item['tipo'], $ext) ?></div>
                                    <div class="ph-ext">.<?= $ext ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="item-meta">
                                <span class="item-tipo"><?= $labels[$item['tipo']] ?? ucfirst($item['tipo']) ?></span>
                                <?php if (!empty($item['titulo'])): ?>
                                <p class="item-titulo"><?= htmlspecialchars($item['titulo']) ?></p>
                                <?php endif; ?>
                                <p class="item-nombre" title="<?= htmlspecialchars($nombre_mostrar) ?>">
                                    <?= htmlspecialchars($nombre_mostrar) ?>
                                </p>
                                <?php if (!empty($item['descripcion'])): ?>
                                <p class="item-desc"><?= htmlspecialchars($item['descripcion']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="item-acciones">
                                <button class="btn-edit-item"
                                    onclick="abrirEditor(
                                        <?= $item['id'] ?>,
                                        '<?= addslashes(htmlspecialchars($item['titulo'])) ?>',
                                        '<?= addslashes(htmlspecialchars($item['descripcion'])) ?>',
                                        '<?= $item['tipo'] ?>',
                                        '<?= addslashes(htmlspecialchars($item['archivo'])) ?>'
                                    )">
                                     Editar
                                </button>
                                <a href="?eliminar=<?= $item['id'] ?>" class="btn-del-item"
                                   onclick="return confirm('¿Eliminar este ítem del portafolio?')">
                                    Eliminar
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal edición -->
    <div class="modal-overlay" id="modal-editor">
        <div class="modal">
            <h3>Editar ítem</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion"    value="editar_item">
                <input type="hidden" name="item_id"   id="modal-item-id">
                <input type="hidden" name="tipo_item" id="modal-tipo">

                <div class="form-group">
                    <label>Archivo actual</label>
                    <div class="preview-actual" id="modal-preview">
                        <div class="prev-doc"></div>
                    </div>
                    <label style="margin-top:8px;">
                        Reemplazar archivo
                        <span style="color:var(--text-secondary);font-size:11px;">(opcional)</span>
                    </label>
                    <input type="file" name="archivo_nuevo" id="modal-archivo">
                    <span class="hint" id="modal-hint">Deja vacío para conservar el archivo actual.</span>
                </div>

                <div class="form-group">
                    <label>Título</label>
                    <input type="text" name="titulo_item" id="modal-titulo" placeholder="Nombre de la obra">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion_item" id="modal-descripcion" placeholder="Técnica, año, notas..."></textarea>
                </div>
                <div class="modal-acciones">
                    <button type="button" class="btn-cancelar-modal" onclick="cerrarEditor()">Cancelar</button>
                    <button type="submit" class="btn-guardar" style="flex:2">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ── Dropzone drag & drop ───────────────────────────────
        const dropzone = document.getElementById('dropzone');
        dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            const input = document.getElementById('archivos-input');
            // Transferir archivos al input
            const dt = new DataTransfer();
            for (const f of e.dataTransfer.files) dt.items.add(f);
            input.files = dt.files;
            mostrarArchivos(input.files);
        });

        function formatBytes(b) {
            if (b < 1024) return b + ' B';
            if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
            return (b/1048576).toFixed(1) + ' MB';
        }

        function iconoParaArchivo(nombre) {
            const ext = nombre.split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) return '';
            if (['mp4','webm','mov','avi','mkv'].includes(ext))             return '';
            if (['mp3','wav','ogg','m4a','flac'].includes(ext))             return '';
            if (['pdf'].includes(ext))                                       return '';
            if (['doc','docx'].includes(ext))                               return '';
            if (['xls','xlsx'].includes(ext))                               return '';
            if (['ppt','pptx'].includes(ext))                               return '';
            return '';
        }

        function mostrarArchivos(files) {
            const lista  = document.getElementById('archivos-lista');
            const count  = document.getElementById('archivos-count');
            const btn    = document.getElementById('btn-subir');
            const tituloWrap = document.getElementById('campo-titulo-wrap');
            const descWrap   = document.getElementById('campo-desc-wrap');

            lista.innerHTML = '';
            if (files.length === 0) {
                lista.style.display = 'none';
                count.style.display = 'none';
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
                return;
            }

            count.style.display = 'block';
            count.textContent = files.length + ' archivo' + (files.length > 1 ? 's' : '') + ' seleccionado' + (files.length > 1 ? 's' : '');
            lista.style.display = 'flex';

            for (const f of files) {
                const chip = document.createElement('div');
                chip.className = 'archivo-chip';
                chip.innerHTML = `
                    <span class="chip-icono">${iconoParaArchivo(f.name)}</span>
                    <span class="chip-nombre">${f.name}</span>
                    <span class="chip-size">${formatBytes(f.size)}</span>
                `;
                lista.appendChild(chip);
            }

            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.textContent = files.length > 1 ? `Subir ${files.length} archivos` : 'Subir archivo';

            // Título/desc solo útil para 1 archivo
            const opacity = files.length > 1 ? '0.4' : '1';
            tituloWrap.style.opacity = opacity;
            descWrap.style.opacity   = opacity;
        }

        // ── Filtro de tipo ─────────────────────────────────────
        function filtrar(tipo, btn) {
            document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
            document.querySelectorAll('#items-grid .item-card').forEach(card => {
                card.style.display = (tipo === 'todos' || card.dataset.tipo === tipo) ? '' : 'none';
            });
        }

        // ── Modal edición ──────────────────────────────────────
        function abrirEditor(id, titulo, descripcion, tipo, archivo) {
            document.getElementById('modal-item-id').value     = id;
            document.getElementById('modal-tipo').value        = tipo;
            document.getElementById('modal-titulo').value      = titulo;
            document.getElementById('modal-descripcion').value = descripcion;
            document.getElementById('modal-archivo').value     = '';

            const preview = document.getElementById('modal-preview');
            if (tipo === 'imagen') {
                preview.innerHTML = `<img src="${archivo}" alt="Vista previa">`;
            } else if (tipo === 'video') {
                preview.innerHTML = `<video controls><source src="${archivo}"></video>`;
            } else if (tipo === 'audio') {
                preview.innerHTML = `<audio controls style="width:100%;padding:8px;"><source src="${archivo}"></audio>`;
            } else {
                const ext = archivo.split('.').pop().toUpperCase();
                preview.innerHTML = `<div class="prev-doc"> .${ext}</div>`;
            }

            document.getElementById('modal-editor').classList.add('open');
        }

        function cerrarEditor() {
            document.getElementById('modal-editor').classList.remove('open');
            document.getElementById('modal-preview').innerHTML = '<div class="prev-doc"></div>';
        }
        document.getElementById('modal-editor').addEventListener('click', function(e) {
            if (e.target === this) cerrarEditor();
        });

        // ── Tema ───────────────────────────────────────────────
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
            document.getElementById('toggle').classList.remove('on');
            document.getElementById('theme-label').textContent = 'Modo claro';
        }
    </script>
</body>
</html>