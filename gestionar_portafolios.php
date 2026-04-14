<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ing.html");
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// Eliminar ítem
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $res = mysqli_query($conn, "SELECT archivo FROM portafolio WHERE id = $id");
    $item = mysqli_fetch_assoc($res);
    if ($item) {
        if (!empty($item['archivo']) && file_exists($item['archivo'])) unlink($item['archivo']);
        mysqli_query($conn, "DELETE FROM portafolio WHERE id = $id");
    }
    header("Location: gestionar_portafolios.php?exito=eliminado");
    exit();
}

// Filtros
$filtro_artista    = isset($_GET['artista_id'])  ? intval($_GET['artista_id'])                    : 0;
$filtro_disciplina = isset($_GET['disciplina'])  ? trim($_GET['disciplina'])                      : '';
$filtro_tipo       = isset($_GET['tipo'])        ? trim($_GET['tipo'])                            : '';

// Artistas para el select (solo los que tienen portafolio)
$artistas_res = mysqli_query($conn, "
    SELECT DISTINCT a.id, a.nombre
    FROM artistas a
    INNER JOIN portafolio p ON p.artista_id = a.id
    ORDER BY a.nombre ASC
");
$artistas_lista = [];
while ($a = mysqli_fetch_assoc($artistas_res)) $artistas_lista[] = $a;

// Disciplinas para el select (solo las que tienen portafolio)
$disciplinas_res = mysqli_query($conn, "
    SELECT DISTINCT a.disciplina
    FROM artistas a
    INNER JOIN portafolio p ON p.artista_id = a.id
    WHERE a.disciplina != '' AND a.disciplina IS NOT NULL
    ORDER BY a.disciplina ASC
");
$disciplinas_lista = [];
while ($d = mysqli_fetch_assoc($disciplinas_res)) $disciplinas_lista[] = $d['disciplina'];

// Construir WHERE
$wheres = [];
if ($filtro_artista)    $wheres[] = "p.artista_id = $filtro_artista";
if ($filtro_disciplina) $wheres[] = "a.disciplina = '" . mysqli_real_escape_string($conn, $filtro_disciplina) . "'";
if ($filtro_tipo)       $wheres[] = "p.tipo = '"       . mysqli_real_escape_string($conn, $filtro_tipo) . "'";
$where = $wheres ? "WHERE " . implode(" AND ", $wheres) : "";

$items_res = mysqli_query($conn, "
    SELECT p.*, a.nombre AS artista_nombre, a.correo AS artista_correo,
           a.disciplina AS artista_disciplina, a.foto_perfil AS artista_foto
    FROM portafolio p
    JOIN artistas a ON p.artista_id = a.id
    $where
    ORDER BY p.created_at DESC
");
$items = [];
while ($row = mysqli_fetch_assoc($items_res)) $items[] = $row;

$tipos_labels = ['imagen'=>'Imagen','video'=>'Video','audio'=>'Audio','documento'=>'Documento','otro'=>'Otro'];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portafolios - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-header h1 { font-size:32px; color:var(--primary); }
        .btn-volver { text-decoration:none; color:var(--text-secondary); font-size:13px; border:1px solid var(--border); padding:8px 16px; border-radius:4px; transition:border-color 0.2s,color 0.2s; }
        .btn-volver:hover { border-color:var(--primary); color:var(--primary); }

        /* Filtro */
        .filtro-bar {
            display:flex; gap:12px; align-items:center; margin-bottom:24px;
            background:var(--card-bg); border:1px solid var(--border); border-radius:4px; padding:16px;
        }
        .filtro-bar label { color:var(--text-secondary); font-size:13px; flex-shrink:0; }
        .filtro-bar select {
            padding:8px 12px; border:1px solid var(--border); border-radius:4px;
            background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:13px;
        }
        .filtro-bar select:focus { outline:none; border-color:var(--primary); }
        .btn-filtrar {
            padding:8px 16px; background:var(--primary); color:white; border:none;
            border-radius:4px; cursor:pointer; font-family:'Jost',sans-serif; font-size:13px; transition:background 0.2s;
        }
        .btn-filtrar:hover { background:var(--primary-dark); }
        .btn-limpiar {
            padding:8px 16px; background:none; color:var(--text-secondary); border:1px solid var(--border);
            border-radius:4px; cursor:pointer; font-family:'Jost',sans-serif; font-size:13px; text-decoration:none; transition:all 0.2s;
        }
        .btn-limpiar:hover { border-color:var(--primary); color:var(--primary); }
        .total-badge { margin-left:auto; color:var(--text-secondary); font-size:13px; }

        /* Tabla */
        .tabla-port {
            width:100%; border-collapse:collapse;
            background:var(--card-bg); border:1px solid var(--border); border-radius:4px; overflow:hidden;
        }
        .tabla-port th {
            background:var(--bg); padding:12px 16px; text-align:left;
            font-family:'Cormorant Garamond',serif; font-size:14px; letter-spacing:1px;
            color:var(--primary); border-bottom:1px solid var(--border); font-weight:600;
        }
        .tabla-port td {
            padding:12px 16px; border-bottom:1px solid var(--border);
            font-size:13px; color:var(--text); vertical-align:middle;
        }
        .tabla-port tr:last-child td { border-bottom:none; }
        .tabla-port tr:hover td { background:rgba(173,102,108,0.05); }

        .td-id { color:var(--text-secondary); font-size:12px; width:50px; }

        .artista-cell { display:flex; align-items:center; gap:10px; }
        .art-avatar {
            width:32px; height:32px; border-radius:4px;
            background:var(--primary); color:white;
            display:flex; align-items:center; justify-content:center;
            font-family:'Cormorant Garamond',serif; font-size:16px; flex-shrink:0; overflow:hidden;
        }
        .art-avatar img { width:100%; height:100%; object-fit:cover; }
        .art-info { }
        .art-nombre { font-size:13px; color:var(--text); font-weight:500; }
        .art-detalle { font-size:11px; color:var(--text-secondary); }

        .tipo-badge {
            display:inline-block; padding:2px 8px; border-radius:4px; font-size:11px;
            background:rgba(173,102,108,0.15); color:var(--primary);
        }

        .preview-thumb {
            width:48px; height:48px; border-radius:4px; overflow:hidden;
            background:var(--bg); border:1px solid var(--border);
            display:flex; align-items:center; justify-content:center;
        }
        .preview-thumb img { width:100%; height:100%; object-fit:cover; }
        .preview-thumb .ph { font-size:22px; }

        .archivo-nombre { font-size:12px; color:var(--text-secondary); max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

        .td-acciones { display:flex; gap:6px; }
        .btn-ver-art { padding:4px 10px; background:none; border:1px solid var(--border); border-radius:4px; color:var(--text-secondary); font-size:11px; text-decoration:none; transition:all 0.2s; }
        .btn-ver-art:hover { border-color:var(--primary); color:var(--primary); }
        .btn-del-port { padding:4px 10px; background:none; border:1px solid var(--border); border-radius:4px; color:var(--text-secondary); font-size:11px; text-decoration:none; transition:all 0.2s; }
        .btn-del-port:hover { border-color:#cc0000; color:#cc0000; }

        .alert { padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:14px; border:1px solid var(--primary); background:rgba(173,102,108,0.15); color:var(--primary); }
        .sin-items { text-align:center; padding:40px; color:var(--text-secondary); }
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
                <li><a href="gestionar_artistas.php">Artistas</a></li>
                <li><a href="gestionar_usuarios.php">Usuarios</a></li>
                <li><a href="gestionar_portafolios.php" class="active">Portafolios</a></li>
                <li><a href="gestionar_recintos.php">Recintos</a></li>
                <li><a href="artistas.php">Ver catálogo</a></li>
                <li><a href="galeria.php">Galería</a></li>
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
            <h1>Portafolios</h1>
            <a href="panel_admin.php" class="btn-volver">← Panel Admin</a>
        </div>

        <?php if (isset($_GET['exito'])): ?>
        <div class="alert">✓ Ítem eliminado del portafolio.</div>
        <?php endif; ?>

        <!-- Filtros -->
        <form method="GET" class="filtro-bar" style="flex-wrap:wrap; gap:10px;">
            <label>Artista:</label>
            <select name="artista_id">
                <option value="0">Todos</option>
                <?php foreach ($artistas_lista as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $filtro_artista == $a['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Disciplina:</label>
            <select name="disciplina">
                <option value="">Todas</option>
                <?php foreach ($disciplinas_lista as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>" <?= $filtro_disciplina === $d ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <label>Tipo:</label>
            <select name="tipo">
                <option value="">Todos</option>
                <?php foreach (['imagen'=>'Imagen','video'=>'Video','audio'=>'Audio','documento'=>'Documento','otro'=>'Otro'] as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $filtro_tipo === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-filtrar">Filtrar</button>
            <?php if ($filtro_artista || $filtro_disciplina || $filtro_tipo): ?>
            <a href="gestionar_portafolios.php" class="btn-limpiar">Limpiar</a>
            <?php endif; ?>
            <span class="total-badge"><?= count($items) ?> archivo<?= count($items) !== 1 ? 's' : '' ?></span>
        </form>

        <!-- Tabla -->
        <?php if (!empty($items)): ?>
        <table class="tabla-port">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Artista</th>
                    <th>Archivo</th>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item):
                $ext = strtolower(pathinfo($item['archivo'], PATHINFO_EXTENSION));
                $nombre_mostrar = !empty($item['nombre_original']) ? $item['nombre_original'] : basename($item['archivo']);
                $iconos = ['imagen'=>'','video'=>'','audio'=>'','documento'=>'','otro'=>''];
            ?>
            <tr>
                <td class="td-id">#<?= $item['id'] ?></td>
                <td>
                    <div class="artista-cell">
                        <div class="art-avatar">
                            <?php if (!empty($item['artista_foto'])): ?>
                                <img src="<?= htmlspecialchars($item['artista_foto']) ?>" alt="">
                            <?php else: ?>
                                <?= strtoupper(mb_substr($item['artista_nombre'], 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="art-info">
                            <div class="art-nombre"><?= htmlspecialchars($item['artista_nombre']) ?></div>
                            <div class="art-detalle">ID <?= $item['artista_id'] ?> · <?= htmlspecialchars($item['artista_disciplina'] ?? '') ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div class="preview-thumb">
                            <?php if ($item['tipo'] === 'imagen'): ?>
                                <img src="<?= htmlspecialchars($item['archivo']) ?>" alt="">
                            <?php else: ?>
                                <span class="ph"><?= $iconos[$item['tipo']] ?? '' ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="archivo-nombre" title="<?= htmlspecialchars($nombre_mostrar) ?>"><?= htmlspecialchars($nombre_mostrar) ?></span>
                    </div>
                </td>
                <td><span class="tipo-badge"><?= $tipos_labels[$item['tipo']] ?? ucfirst($item['tipo']) ?></span></td>
                <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?= !empty($item['titulo']) ? htmlspecialchars($item['titulo']) : '<span style="color:var(--text-secondary);font-size:12px;">Sin título</span>' ?>
                </td>
                <td style="font-size:12px;color:var(--text-secondary);"><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                <td>
                    <div class="td-acciones">
                        <a href="ver_artista.php?id=<?= $item['artista_id'] ?>" class="btn-ver-art">Ver artista</a>
                        <a href="?eliminar=<?= $item['id'] ?>" class="btn-del-port"
                           onclick="return confirm('¿Eliminar este ítem del portafolio?')">Eliminar</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="sin-items">No hay archivos en el portafolio<?= $filtro_artista ? ' de este artista' : '' ?>.</div>
        <?php endif; ?>
    </div>

    <script>
        function toggleTheme() {
            const html=document.documentElement,toggle=document.getElementById('toggle'),label=document.getElementById('theme-label');
            if(html.getAttribute('data-theme')==='dark'){html.setAttribute('data-theme','light');toggle.classList.remove('on');label.textContent='Modo claro';}
            else{html.setAttribute('data-theme','dark');toggle.classList.add('on');label.textContent='Modo oscuro';}
            localStorage.setItem('theme',html.getAttribute('data-theme'));
        }
        const savedTheme=localStorage.getItem('theme')||'dark';
        document.documentElement.setAttribute('data-theme',savedTheme);
        if(savedTheme==='light'){document.getElementById('toggle').classList.remove('on');document.getElementById('theme-label').textContent='Modo claro';}
    </script>
</body>
</html>