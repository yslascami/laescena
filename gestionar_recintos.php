<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ing.html");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost'; $user     = getenv('DB_USER')     ?: 'root'; $password = getenv('DB_PASSWORD') ?: ''; $database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// Crear tabla si no existe
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS recintos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    direccion VARCHAR(500) DEFAULT '',
    telefono VARCHAR(50) DEFAULT '',
    correo VARCHAR(255) DEFAULT '',
    sitio_web VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Eliminar recinto
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    mysqli_query($conn, "DELETE FROM recintos WHERE id = $id");
    header("Location: gestionar_recintos.php?exito=eliminado");
    exit();
}

// Crear o editar recinto
if (isset($_POST['accion'])) {
    $nombre      = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $direccion   = trim($_POST['direccion']);
    $telefono    = trim($_POST['telefono']);
    $correo      = trim($_POST['correo']);
    $sitio_web   = trim($_POST['sitio_web']);

    if ($_POST['accion'] === 'crear') {
        $stmt = mysqli_prepare($conn, "INSERT INTO recintos (nombre, descripcion, direccion, telefono, correo, sitio_web) VALUES (?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssssss", $nombre, $descripcion, $direccion, $telefono, $correo, $sitio_web);
        mysqli_stmt_execute($stmt);
        header("Location: gestionar_recintos.php?exito=creado");
        exit();
    } elseif ($_POST['accion'] === 'editar') {
        $id = intval($_POST['recinto_id']);
        $stmt = mysqli_prepare($conn, "UPDATE recintos SET nombre=?, descripcion=?, direccion=?, telefono=?, correo=?, sitio_web=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssssi", $nombre, $descripcion, $direccion, $telefono, $correo, $sitio_web, $id);
        mysqli_stmt_execute($stmt);
        header("Location: gestionar_recintos.php?exito=editado");
        exit();
    }
}

// Cargar recinto a editar
$editando = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = mysqli_query($conn, "SELECT * FROM recintos WHERE id = $id");
    $editando = mysqli_fetch_assoc($res);
}

// Obtener todos los recintos
$recintos_res = mysqli_query($conn, "SELECT * FROM recintos ORDER BY created_at DESC");
$recintos = [];
while ($r = mysqli_fetch_assoc($recintos_res)) $recintos[] = $r;
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recintos - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-header h1 { font-size:32px; color:var(--primary); }
        .btn-volver { text-decoration:none; color:var(--text-secondary); font-size:13px; border:1px solid var(--border); padding:8px 16px; border-radius:4px; transition:border-color 0.2s,color 0.2s; }
        .btn-volver:hover { border-color:var(--primary); color:var(--primary); }

        .layout { display:grid; grid-template-columns:340px 1fr; gap:24px; align-items:start; }

        .form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:4px; padding:24px; position:sticky; top:20px; }
        .form-card h2 { font-size:18px; color:var(--primary); margin-bottom:18px; border-bottom:1px solid var(--border); padding-bottom:10px; }
        .form-group { margin-bottom:13px; }
        .form-group label { display:block; margin-bottom:5px; color:var(--text); font-family:'Cormorant Garamond',serif; font-size:14px; letter-spacing:1px; }
        .form-group input,
        .form-group textarea { width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:13px; }
        .form-group input:focus,
        .form-group textarea:focus { outline:none; border-color:var(--primary); }
        .form-group textarea { height:70px; resize:vertical; }
        .btn-guardar { width:100%; padding:12px; background:var(--primary); color:white; border:none; border-radius:4px; font-family:'Cormorant Garamond',serif; font-size:17px; letter-spacing:2px; cursor:pointer; transition:background 0.2s; }
        .btn-guardar:hover { background:var(--primary-dark); }
        .btn-cancelar { width:100%; padding:10px; background:none; border:1px solid var(--border); border-radius:4px; color:var(--text-secondary); font-family:'Jost',sans-serif; font-size:13px; cursor:pointer; margin-top:8px; text-decoration:none; text-align:center; display:block; transition:all 0.2s; }
        .btn-cancelar:hover { border-color:var(--primary); color:var(--primary); }

        .editando-banner { background:rgba(173,102,108,0.1); border:1px solid var(--primary); border-radius:4px; padding:10px 14px; margin-bottom:16px; font-size:13px; color:var(--primary); }

        /* Recintos lista */
        .recinto-card {
            background:var(--card-bg); border:1px solid var(--border); border-radius:4px;
            padding:20px; margin-bottom:14px; transition:border-color 0.2s;
        }
        .recinto-card:hover { border-color:var(--primary); }
        .recinto-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; }
        .recinto-header h3 { font-size:18px; color:var(--text); }
        .recinto-id { font-size:11px; color:var(--text-secondary); background:var(--bg); border:1px solid var(--border); padding:2px 8px; border-radius:4px; }
        .recinto-desc { color:var(--text-secondary); font-size:13px; line-height:1.6; margin-bottom:12px; }
        .recinto-datos { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:12px; }
        .recinto-dato { font-size:12px; color:var(--text-secondary); }
        .recinto-dato strong { color:var(--text); }
        .recinto-acciones { display:flex; gap:8px; }
        .btn-editar-rec { padding:6px 14px; background:var(--primary); color:white; border:none; border-radius:4px; font-size:12px; text-decoration:none; font-family:'Jost',sans-serif; transition:background 0.2s; }
        .btn-editar-rec:hover { background:var(--primary-dark); }
        .btn-del-rec { padding:6px 14px; background:none; border:1px solid var(--border); border-radius:4px; color:var(--text-secondary); font-size:12px; text-decoration:none; font-family:'Jost',sans-serif; transition:all 0.2s; }
        .btn-del-rec:hover { border-color:#cc0000; color:#cc0000; }

        .sin-recintos { text-align:center; padding:40px; color:var(--text-secondary); }
        .alert { padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:14px; border:1px solid var(--primary); background:rgba(173,102,108,0.15); color:var(--primary); }
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
                <li><a href="gestionar_portafolios.php">Portafolios</a></li>
                <li><a href="gestionar_recintos.php" class="active">Recintos</a></li>
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
            <h1>Recintos / Centros Culturales</h1>
            <a href="panel_admin.php" class="btn-volver">← Panel Admin</a>
        </div>

        <?php if (isset($_GET['exito'])): ?>
        <div class="alert">✓ <?= ['creado'=>'Recinto creado correctamente.','editado'=>'Recinto actualizado.','eliminado'=>'Recinto eliminado.'][$_GET['exito']] ?? 'Operación realizada.' ?></div>
        <?php endif; ?>

        <div class="layout">
            <!-- Formulario -->
            <div class="form-card">
                <?php if ($editando): ?>
                <div class="editando-banner"> Editando: <?= htmlspecialchars($editando['nombre']) ?></div>
                <?php endif; ?>
                <h2><?= $editando ? 'Editar recinto' : 'Agregar recinto' ?></h2>
                <form method="POST">
                    <input type="hidden" name="accion" value="<?= $editando ? 'editar' : 'crear' ?>">
                    <?php if ($editando): ?>
                    <input type="hidden" name="recinto_id" value="<?= $editando['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($editando['nombre'] ?? '') ?>" required placeholder="Nombre del recinto">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" placeholder="Descripción del recinto..."><?= htmlspecialchars($editando['descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" value="<?= htmlspecialchars($editando['direccion'] ?? '') ?>" placeholder="Calle, número, ciudad">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" value="<?= htmlspecialchars($editando['telefono'] ?? '') ?>" placeholder="771 000 0000">
                    </div>
                    <div class="form-group">
                        <label>Correo</label>
                        <input type="email" name="correo" value="<?= htmlspecialchars($editando['correo'] ?? '') ?>" placeholder="contacto@recinto.com">
                    </div>
                    <div class="form-group">
                        <label>Sitio web</label>
                        <input type="url" name="sitio_web" value="<?= htmlspecialchars($editando['sitio_web'] ?? '') ?>" placeholder="https://...">
                    </div>

                    <button type="submit" class="btn-guardar"><?= $editando ? 'Guardar cambios' : 'Agregar recinto' ?></button>
                    <?php if ($editando): ?>
                    <a href="gestionar_recintos.php" class="btn-cancelar">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Lista de recintos -->
            <div>
                <?php if (!empty($recintos)): ?>
                <?php foreach ($recintos as $r): ?>
                <div class="recinto-card">
                    <div class="recinto-header">
                        <h3><?= htmlspecialchars($r['nombre']) ?></h3>
                        <span class="recinto-id">ID #<?= $r['id'] ?></span>
                    </div>
                    <?php if (!empty($r['descripcion'])): ?>
                    <p class="recinto-desc"><?= htmlspecialchars($r['descripcion']) ?></p>
                    <?php endif; ?>
                    <div class="recinto-datos">
                        <?php if (!empty($r['direccion'])): ?>
                        <span class="recinto-dato"> <strong><?= htmlspecialchars($r['direccion']) ?></strong></span>
                        <?php endif; ?>
                        <?php if (!empty($r['telefono'])): ?>
                        <span class="recinto-dato"> <?= htmlspecialchars($r['telefono']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['correo'])): ?>
                        <span class="recinto-dato"> <?= htmlspecialchars($r['correo']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($r['sitio_web'])): ?>
                        <span class="recinto-dato"> <a href="<?= htmlspecialchars($r['sitio_web']) ?>" target="_blank" style="color:var(--primary);">Sitio web</a></span>
                        <?php endif; ?>
                    </div>
                    <div class="recinto-acciones">
                        <a href="?editar=<?= $r['id'] ?>" class="btn-editar-rec">Editar</a>
                        <a href="?eliminar=<?= $r['id'] ?>" class="btn-del-rec"
                           onclick="return confirm('¿Eliminar este recinto?')">Eliminar</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="sin-recintos">No hay recintos registrados aún.<br>Agrega el primero con el formulario.</div>
                <?php endif; ?>
            </div>
        </div>
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