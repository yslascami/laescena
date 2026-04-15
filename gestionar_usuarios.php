<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ing.html");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost'; $user     = getenv('DB_USER')     ?: 'root'; $password = getenv('DB_PASSWORD') ?: ''; $database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// ── Crear cuenta CC ────────────────────────────────────────────
if (isset($_POST['accion']) && $_POST['accion'] === 'crear_cc') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role     = 'centrocultural';

    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        $error = "Este correo ya está registrado.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $email, $password, $role);
        mysqli_stmt_execute($stmt);
        header("Location: gestionar_usuarios.php?exito=creado");
        exit();
    }
}

// ── Eliminar usuario ───────────────────────────────────────────
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    // No permitir eliminar superadmin
    $res = mysqli_query($conn, "SELECT role FROM users WHERE id = $id");
    $u   = mysqli_fetch_assoc($res);
    if ($u && $u['role'] !== 'superadmin') {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    }
    header("Location: gestionar_usuarios.php?exito=eliminado");
    exit();
}

// ── Obtener usuarios ───────────────────────────────────────────
$usuarios = mysqli_query($conn, "SELECT * FROM users ORDER BY role ASC, email ASC");
$lista = [];
while ($u = mysqli_fetch_assoc($usuarios)) $lista[] = $u;

$labels = [
    'superadmin'    => 'Super Admin',
    'centrocultural'=> 'Centro Cultural',
    'artista'       => 'Artista',
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-header h1 { font-size:32px; color:var(--primary); }
        .btn-volver { text-decoration:none; color:var(--text-secondary); font-size:13px; border:1px solid var(--border); padding:8px 16px; border-radius:4px; transition:border-color 0.2s,color 0.2s; }
        .btn-volver:hover { border-color:var(--primary); color:var(--primary); }

        .layout { display:grid; grid-template-columns:300px 1fr; gap:24px; align-items:start; }

        .form-card { background:var(--card-bg); border:1px solid var(--border); border-radius:4px; padding:24px; }
        .form-card h2 { font-size:18px; color:var(--primary); margin-bottom:18px; border-bottom:1px solid var(--border); padding-bottom:10px; }
        .form-group { margin-bottom:14px; }
        .form-group label { display:block; margin-bottom:5px; color:var(--text); font-family:'Cormorant Garamond',serif; font-size:14px; letter-spacing:1px; }
        .form-group input { width:100%; padding:10px; border:1px solid var(--border); border-radius:4px; background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:14px; }
        .form-group input:focus { outline:none; border-color:var(--primary); }
        .btn-crear { width:100%; padding:12px; background:var(--primary); color:white; border:none; border-radius:4px; font-family:'Cormorant Garamond',serif; font-size:17px; letter-spacing:2px; cursor:pointer; transition:background 0.2s; }
        .btn-crear:hover { background:var(--primary-dark); }

        .seccion-titulo { font-size:18px; color:var(--primary); border-bottom:1px solid var(--border); padding-bottom:8px; margin-bottom:16px; }

        .usuario-item {
            background:var(--card-bg); border:1px solid var(--border); border-radius:4px;
            padding:14px 18px; margin-bottom:10px;
            display:flex; justify-content:space-between; align-items:center; gap:16px;
        }
        .usuario-info h3 { font-size:14px; color:var(--text); margin-bottom:4px; }
        .rol-badge { display:inline-block; padding:2px 10px; border-radius:4px; font-size:11px; }
        .rol-superadmin     { background:rgba(173,102,108,0.3); color:var(--primary); }
        .rol-centrocultural { background:rgba(100,150,200,0.2); color:#7ab0d4; }
        .rol-artista        { background:rgba(173,102,108,0.15); color:var(--primary); }

        .usuario-acciones { display:flex; gap:6px; }
        .btn-ver { padding:5px 12px; background:none; border:1px solid var(--border); border-radius:4px; color:var(--text-secondary); font-size:12px; text-decoration:none; font-family:'Jost',sans-serif; transition:all 0.2s; }
        .btn-ver:hover { border-color:var(--primary); color:var(--primary); }
        .btn-del { padding:5px 12px; background:none; border:1px solid var(--border); border-radius:4px; color:var(--text-secondary); font-size:12px; text-decoration:none; font-family:'Jost',sans-serif; transition:all 0.2s; }
        .btn-del:hover { border-color:#cc0000; color:#cc0000; }

        .alert { padding:12px 16px; border-radius:4px; margin-bottom:20px; font-size:14px; border:1px solid var(--primary); background:rgba(173,102,108,0.15); color:var(--primary); }
        .alert-error { border-color:#cc0000; background:rgba(200,0,0,0.1); color:#cc6666; }

        .hint { color:var(--text-secondary); font-size:11px; margin-top:4px; display:block; }
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
                <li><a href="gestionar_usuarios.php" class="active">Usuarios</a></li>
                <li><a href="gestionar_portafolios.php">Portafolios</a></li>
                <li><a href="gestionar_recintos.php">Recintos</a></li>
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
            <h1>Gestionar Usuarios</h1>
            <a href="panel_admin.php" class="btn-volver">← Panel Admin</a>
        </div>

        <?php if (isset($_GET['exito'])): ?>
        <div class="alert">✓ <?= ['creado'=>'Cuenta creada correctamente.','eliminado'=>'Usuario eliminado.'][$_GET['exito']] ?? 'Operación realizada.' ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
        <div class="alert alert-error">✗ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="layout">
            <!-- Crear cuenta CC -->
            <div class="form-card">
                <h2>Nueva cuenta Centro Cultural</h2>
                <form method="POST">
                    <input type="hidden" name="accion" value="crear_cc">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña</label>
                        <input type="text" name="password" placeholder="Contraseña temporal" required>
                        <span class="hint">El usuario podrá cambiarla después.</span>
                    </div>
                    <button type="submit" class="btn-crear">Crear cuenta</button>
                </form>
            </div>

            <!-- Lista de usuarios -->
            <div>
                <?php
                $roles_orden = ['superadmin','centrocultural','artista'];
                foreach ($roles_orden as $rol):
                    $filtrados = array_filter($lista, fn($u) => $u['role'] === $rol);
                    if (empty($filtrados)) continue;
                ?>
                <h2 class="seccion-titulo"><?= $labels[$rol] ?? $rol ?> (<?= count($filtrados) ?>)</h2>
                <?php foreach ($filtrados as $u): ?>
                <div class="usuario-item">
                    <div class="usuario-info">
                        <h3><?= htmlspecialchars($u['email']) ?></h3>
                        <span class="rol-badge rol-<?= $u['role'] ?>"><?= $labels[$u['role']] ?? $u['role'] ?></span>
                    </div>
                    <div class="usuario-acciones">
                        <?php if ($u['role'] === 'artista'): ?>
                            <?php
                            $email_art = $u['email'];
                            $res_art = mysqli_query($conn, "SELECT id FROM artistas WHERE correo = '" . mysqli_real_escape_string($conn, $email_art) . "'");
                            $art = mysqli_fetch_assoc($res_art);
                            if ($art):
                            ?>
                            <a href="ver_artista.php?id=<?= $art['id'] ?>" class="btn-ver">Ver perfil</a>
                            <a href="editar_artista.php?id=<?= $art['id'] ?>" class="btn-ver">Editar</a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($u['role'] !== 'superadmin'): ?>
                        <a href="?eliminar=<?= $u['id'] ?>" class="btn-del"
                           onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endforeach; ?>
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