<?php
session_start();

$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) die("Error de conexión: " . mysqli_connect_error());

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id === 0) { header("Location: artistas.php"); exit(); }

$stmt = mysqli_prepare($conn, "SELECT * FROM artistas WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$artista = mysqli_fetch_assoc($result);
if (!$artista) { header("Location: artistas.php"); exit(); }

$stmt2 = mysqli_prepare($conn, "SELECT * FROM galerias WHERE artista = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt2, "s", $artista['nombre']);
mysqli_stmt_execute($stmt2);
$fotos = mysqli_stmt_get_result($stmt2);

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

// Eliminar ítem portafolio (solo superadmin)
if (isset($_GET['del_port']) && isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin') {
    $del_id = intval($_GET['del_port']);
    $res_del = mysqli_query($conn, "SELECT archivo FROM portafolio WHERE id = $del_id AND artista_id = $id");
    $del_item = mysqli_fetch_assoc($res_del);
    if ($del_item) {
        if (!empty($del_item['archivo']) && file_exists($del_item['archivo'])) unlink($del_item['archivo']);
        mysqli_query($conn, "DELETE FROM portafolio WHERE id = $del_id");
    }
    header("Location: ver_artista.php?id=$id&exito=port_eliminado");
    exit();
}

$stmt3 = mysqli_prepare($conn, "SELECT * FROM portafolio WHERE artista_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt3, "i", $id);
mysqli_stmt_execute($stmt3);
$port_items = mysqli_stmt_get_result($stmt3);
$portafolio = [];
while ($p = mysqli_fetch_assoc($port_items)) $portafolio[] = $p;

$es_cc_o_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['centrocultural', 'superadmin']);
$es_superadmin = isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';

function iconoTipoVer($tipo) {
    switch ($tipo) {
        case 'imagen':    return '🖼️';
        case 'video':     return '🎬';
        case 'audio':     return '🎵';
        case 'documento': return '📄';
        default:          return '📁';
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artista['nombre']) ?> - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* ── Banner de portada ────────────────────────────────── */
        .portada-banner {
            width: 100%;
            height: 260px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: -60px;
            position: relative;
            background-color: var(--card-bg);
            border: 1px solid var(--border);
        }
        .portada-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .portada-banner .portada-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg,
                rgba(173,102,108,0.3) 0%,
                rgba(129,52,58,0.15) 50%,
                rgba(28,28,28,0.8) 100%);
            display: flex;
            align-items: flex-end;
            padding: 20px;
        }
        .portada-banner .portada-placeholder span {
            color: rgba(255,255,255,0.3);
            font-family: 'Cormorant Garamond', serif;
            font-size: 13px;
            letter-spacing: 2px;
        }
        .portada-banner::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 80px;
            background: linear-gradient(to top, var(--bg), transparent);
        }

        /* ── Cabecera del perfil ──────────────────────────────── */
        .perfil-header {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            padding: 0 4px;
        }
        .avatar-wrap {
            flex-shrink: 0;
        }
        .avatar {
            width: 110px;
            height: 110px;
            border-radius: 4px;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 44px;
            color: white;
            overflow: hidden;
            border: 3px solid var(--bg);
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        }
        .avatar img { width:100%; height:100%; object-fit:cover; }

        .perfil-info {
            flex: 1;
            padding-bottom: 6px;
        }
        .perfil-info h1 {
            font-size: 28px;
            color: var(--text);
            margin-bottom: 6px;
        }
        .disciplina-badge {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 3px 12px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .contacto-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .contacto-item {
            color: var(--text-secondary);
            font-size: 13px;
        }
        .contacto-privado-msg {
            color: var(--text-secondary);
            font-size: 12px;
            font-style: italic;
        }

        .perfil-acciones {
            display: flex;
            gap: 8px;
            padding-bottom: 6px;
            flex-shrink: 0;
        }

        .btn-volver {
            background: none;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            text-decoration: none;
            transition: border-color 0.2s, color 0.2s;
        }
        .btn-volver:hover { border-color: var(--primary); color: var(--primary); }

        .btn-editar-admin {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            font-family: 'Jost', sans-serif;
            transition: background 0.2s;
        }
        .btn-editar-admin:hover { background-color: var(--primary-dark); }

        /* ── Layout contenido ─────────────────────────────────── */
        .contenido-grid {
            display: grid;
            grid-template-columns: 260px 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-lateral {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .info-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
        }
        .info-card h2 {
            font-size: 16px;
            color: var(--primary);
            margin-bottom: 14px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }
        .bio-text {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.8;
        }
        .sin-bio {
            color: var(--text-secondary);
            font-size: 13px;
            font-style: italic;
        }

        /* ── Portafolio ───────────────────────────────────────── */
        .seccion-titulo {
            font-size: 22px;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin: 0 0 18px;
        }

        .port-filtros {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .port-filtro {
            padding: 4px 14px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: none;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 12px;
            color: var(--text-secondary);
            transition: all 0.2s;
        }
        .port-filtro:hover, .port-filtro.activo {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(173,102,108,0.1);
        }

        .port-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .port-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
            position: relative;
        }
        .port-card:hover { transform: translateY(-4px); border-color: var(--primary); }

        .port-card img   { width:100%; height:190px; object-fit:cover; display:block; }
        .port-card video { width:100%; max-height:190px; display:block; background:#000; }
        .port-card audio { width:100%; display:block; padding:10px; background:var(--bg); }

        .port-placeholder {
            width:100%; height:150px;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center;
            background:var(--bg); gap:8px;
        }
        .port-placeholder .ph-icon { font-size:38px; }
        .port-placeholder .ph-nombre {
            font-size:11px; color:var(--text-secondary);
            max-width:90%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
            text-align:center;
        }

        .port-meta { padding:10px 12px; }
        .port-tipo {
            display:inline-block;
            background:rgba(173,102,108,0.2); color:var(--primary);
            padding:2px 8px; border-radius:4px; font-size:11px; margin-bottom:5px;
        }
        .port-titulo  { font-size:14px; color:var(--text); margin-bottom:3px; font-weight:500; }
        .port-desc    { font-size:12px; color:var(--text-secondary); line-height:1.4; }

        .btn-del-port {
            display:block; width:calc(100% - 24px);
            margin:0 12px 10px;
            padding:5px;
            background:none; border:1px solid var(--border); border-radius:4px;
            color:var(--text-secondary); font-size:12px; cursor:pointer;
            font-family:'Jost',sans-serif; text-align:center; text-decoration:none;
            transition:border-color 0.2s, color 0.2s;
        }
        .btn-del-port:hover { border-color:#cc0000; color:#cc0000; }

        /* ── Galería ──────────────────────────────────────────── */
        .fotos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
        }
        .foto-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            overflow: hidden;
            transition: transform 0.2s, border-color 0.2s;
        }
        .foto-card:hover { transform: translateY(-4px); border-color: var(--primary); }
        .foto-card img { width:100%; height:200px; object-fit:cover; display:block; }
        .foto-card .pie-foto {
            padding: 10px 14px;
            color: var(--text-secondary);
            font-size: 13px;
            font-style: italic;
            font-family: 'Cormorant Garamond', serif;
        }

        .alert-port {
            padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:14px;
            border:1px solid var(--primary); background-color:rgba(173,102,108,0.15);
            color:var(--primary);
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
                <li><a href="artistas.php" class="active">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <?php if (isset($_SESSION['role'])): ?>
                    <?php if ($_SESSION['role'] == 'artista'): ?>
                        <li><a href="perfil.php">Mi Perfil</a></li>
                    <?php elseif ($_SESSION['role'] == 'centrocultural'): ?>
                        <li><a href="panel_cc.php">Mi Panel</a></li>
                    <?php elseif ($_SESSION['role'] == 'superadmin'): ?>
                        <li><a href="panel_admin.php">Panel Admin</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Cerrar sesión</a></li>
                <?php else: ?>
                    <li><a href="Reg.html">Registro</a></li>
                    <li><a href="ing.html">Ingresar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">

        <!-- Banner de portada -->
        <div class="portada-banner">
            <?php if (!empty($artista['foto_portada'])): ?>
                <img src="<?= htmlspecialchars($artista['foto_portada']) ?>" alt="Portada de <?= htmlspecialchars($artista['nombre']) ?>">
            <?php else: ?>
                <div class="portada-placeholder">
                    <span>LA ESCENA</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cabecera del perfil (sobre la portada) -->
        <div class="perfil-header">
            <div class="avatar-wrap">
                <div class="avatar">
                    <?php if (!empty($artista['foto_perfil'])): ?>
                        <img src="<?= htmlspecialchars($artista['foto_perfil']) ?>" alt="Foto de <?= htmlspecialchars($artista['nombre']) ?>">
                    <?php else: ?>
                        <?= strtoupper(mb_substr($artista['nombre'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="perfil-info">
                <h1><?= htmlspecialchars($artista['nombre']) ?></h1>
                <?php if (!empty($artista['disciplina'])): ?>
                    <span class="disciplina-badge"><?= htmlspecialchars($artista['disciplina']) ?></span>
                <?php endif; ?>
                <div class="contacto-row">
                    <?php if ($artista['correo_publico'] || $es_cc_o_admin): ?>
                        <span class="contacto-item">✉ <?= htmlspecialchars($artista['correo']) ?></span>
                    <?php endif; ?>
                    <?php if (($artista['telefono_publico'] || $es_cc_o_admin) && !empty($artista['teléfono'])): ?>
                        <span class="contacto-item">☎ <?= htmlspecialchars($artista['teléfono']) ?></span>
                    <?php endif; ?>
                    <?php if (!$artista['correo_publico'] && !$artista['telefono_publico'] && !$es_cc_o_admin): ?>
                        <p class="contacto-privado-msg">Información de contacto privada.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="perfil-acciones">
                <a href="artistas.php" class="btn-volver">← Volver</a>
                <?php if ($es_cc_o_admin): ?>
                    <a href="editar_artista.php?id=<?= $artista['id'] ?>" class="btn-editar-admin">Editar perfil</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['exito']) && $_GET['exito'] === 'port_eliminado'): ?>
        <div class="alert-port">✓ Ítem eliminado del portafolio.</div>
        <?php endif; ?>

        <!-- Contenido principal -->
        <div class="contenido-grid">
            <!-- Columna izquierda: bio -->
            <div class="info-lateral">
                <div class="info-card">
                    <h2>Biografía</h2>
                    <?php if (!empty($artista['descripcion'])): ?>
                        <p class="bio-text"><?= nl2br(htmlspecialchars($artista['descripcion'])) ?></p>
                    <?php else: ?>
                        <p class="sin-bio">Este artista aún no ha agregado una descripción.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna derecha: portafolio -->
            <div>
                <?php if (!empty($portafolio)): ?>
                <h2 class="seccion-titulo">Portafolio</h2>

                <!-- Filtros por tipo -->
                <?php
                $tipos_labels = ['imagen'=>'Imágenes','video'=>'Videos','audio'=>'Audios','documento'=>'Documentos','otro'=>'Otros'];
                $tipos_presentes = array_unique(array_column($portafolio, 'tipo'));
                if (count($tipos_presentes) > 1):
                ?>
                <div class="port-filtros">
                    <button class="port-filtro activo" onclick="filtrarPort('todos', this)">Todos (<?= count($portafolio) ?>)</button>
                    <?php foreach ($tipos_presentes as $t):
                        $cnt = count(array_filter($portafolio, fn($p) => $p['tipo'] === $t));
                    ?>
                    <button class="port-filtro" onclick="filtrarPort('<?= $t ?>', this)"><?= $tipos_labels[$t] ?? ucfirst($t) ?> (<?= $cnt ?>)</button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="port-grid" id="port-grid">
                    <?php foreach ($portafolio as $item):
                        $ext = strtolower(pathinfo($item['archivo'], PATHINFO_EXTENSION));
                        $nombre_mostrar = !empty($item['nombre_original']) ? $item['nombre_original'] : basename($item['archivo']);
                    ?>
                    <div class="port-card" data-tipo="<?= $item['tipo'] ?>">
                        <?php if ($item['tipo'] === 'imagen'): ?>
                            <img src="<?= htmlspecialchars($item['archivo']) ?>"
                                 alt="<?= htmlspecialchars($item['titulo']) ?>">
                        <?php elseif ($item['tipo'] === 'video'): ?>
                            <video controls>
                                <source src="<?= htmlspecialchars($item['archivo']) ?>">
                            </video>
                        <?php elseif ($item['tipo'] === 'audio'): ?>
                            <div class="port-placeholder" style="height:70px;">
                                <div class="ph-icon">🎵</div>
                            </div>
                            <audio controls>
                                <source src="<?= htmlspecialchars($item['archivo']) ?>">
                            </audio>
                        <?php else: ?>
                            <div class="port-placeholder">
                                <div class="ph-icon"><?= iconoTipoVer($item['tipo']) ?></div>
                                <div class="ph-nombre"><?= htmlspecialchars($nombre_mostrar) ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="port-meta">
                            <span class="port-tipo"><?= $tipos_labels[$item['tipo']] ?? ucfirst($item['tipo']) ?></span>
                            <?php if (!empty($item['titulo'])): ?>
                            <p class="port-titulo"><?= htmlspecialchars($item['titulo']) ?></p>
                            <?php endif; ?>
                            <?php if ($item['tipo'] !== 'imagen' && $item['tipo'] !== 'video'): ?>
                            <p style="font-size:11px;color:var(--text-secondary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                               title="<?= htmlspecialchars($nombre_mostrar) ?>">
                               <?= htmlspecialchars($nombre_mostrar) ?>
                            </p>
                            <?php endif; ?>
                            <?php if (!empty($item['descripcion'])): ?>
                            <p class="port-desc"><?= htmlspecialchars($item['descripcion']) ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if ($es_superadmin): ?>
                        <a href="?id=<?= $id ?>&del_port=<?= $item['id'] ?>" class="btn-del-port"
                           onclick="return confirm('¿Eliminar este ítem del portafolio?')">
                           🗑️ Eliminar (Admin)
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Galería del artista -->
                <?php if (mysqli_num_rows($fotos) > 0): ?>
                <h2 class="seccion-titulo" style="margin-top:<?= !empty($portafolio) ? '30px' : '0' ?>;">Obra</h2>
                <div class="fotos-grid">
                    <?php while ($foto = mysqli_fetch_assoc($fotos)): ?>
                    <div class="foto-card">
                        <img src="<?= htmlspecialchars($foto['imagen']) ?>" alt="<?= htmlspecialchars($foto['titulo']) ?>">
                        <?php if (!empty($foto['pie_foto'])): ?>
                        <p class="pie-foto"><?= htmlspecialchars($foto['pie_foto']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function filtrarPort(tipo, btn) {
            document.querySelectorAll('.port-filtro').forEach(b => b.classList.remove('activo'));
            btn.classList.add('activo');
            document.querySelectorAll('#port-grid .port-card').forEach(card => {
                card.style.display = (tipo === 'todos' || card.dataset.tipo === tipo) ? '' : 'none';
            });
        }

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