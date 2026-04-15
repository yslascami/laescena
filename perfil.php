<?php
session_start();

if (!isset($_SESSION['artista_id'])) {
    header("Location: ing.html");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// Verificar si el artista está aprobado
$id_check = $_SESSION['artista_id'];
$sql_aprobado = "SELECT aprobado FROM artistas WHERE id = $id_check";
$result_aprobado = mysqli_query($conn, $sql_aprobado);
$check = mysqli_fetch_assoc($result_aprobado);

if (!$check || $check['aprobado'] != 1) {
    header("Location: pendiente.php");
    exit();
}


// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_SESSION['artista_id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $telefono = trim($_POST['telefono'] ?? '');
    $descripcion = $_POST['descripcion'];
    $disciplina = $_POST['disciplina'];
    $telefono_publico = isset($_POST['telefono_publico']) ? 1 : 0;
    $correo_publico = isset($_POST['correo_publico']) ? 1 : 0;

    // Subir foto si se envió
    $foto_perfil = $_POST['foto_actual'];
    if (!empty($_FILES['foto_perfil']['name'])) {
        $ext = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $nuevo_nombre = 'perfil_' . $id . '.' . $ext;
        move_uploaded_file($_FILES['foto_perfil']['tmp_name'], 'imagenes/' . $nuevo_nombre);
        $foto_perfil = 'imagenes/' . $nuevo_nombre;
    }

    $sql = "UPDATE artistas SET nombre=?, correo=?, `teléfono`=?, descripcion=?, disciplina=?, foto_perfil=?, telefono_publico=?, correo_publico=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssii", $nombre, $correo, $telefono, $descripcion, $disciplina, $foto_perfil, $telefono_publico, $correo_publico, $id);
    mysqli_stmt_execute($stmt);

    $_SESSION['artista_nombre'] = $nombre;
    $exito = true;
}

// Obtener datos actuales
$id = $_SESSION['artista_id'];
$sql = "SELECT * FROM artistas WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$artista = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 32px;
            color: var(--primary);
        }

        .btn-logout {
            background: none;
            border: 1px solid var(--border);
            color: var(--text-secondary);
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            transition: border-color 0.2s, color 0.2s;
        }

        .btn-logout:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .perfil-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
        }

        .perfil-avatar-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            text-align: center;
            height: fit-content;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 4px;
            background-color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 48px;
            color: white;
            margin: 0 auto 16px;
            overflow: hidden;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .perfil-avatar-card h2 {
            font-size: 20px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .perfil-avatar-card .disciplina-badge {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 16px;
        }

        .foto-input {
            width: 100%;
            padding: 8px;
            border: 1px dashed var(--border);
            border-radius: 4px;
            background: none;
            color: var(--text-secondary);
            font-size: 12px;
            cursor: pointer;
            margin-top: 8px;
        }

        .perfil-form-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 30px;
        }

        .perfil-form-card h2 {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text);
            font-family: 'Cormorant Garamond', serif;
            font-size: 15px;
            letter-spacing: 1px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text);
            font-family: 'Jost', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .privacidad-section {
            background-color: var(--bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .privacidad-section h3 {
            font-size: 16px;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .checkbox-group label {
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
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
                <li><a href="index.php">Inicio</a></li>
                <li><a href="CC.php">Centro Cultural</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <li><a href="perfil.php" class="active">Mi Perfil</a></li>
                <li><a href="portafolio.php">Mi Portafolio</a></li>
                <li><a href="mensajes.php">Mensajes</a></li>
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
            <h1>Mi Perfil</h1>
            <a href="logout.php"><button class="btn-logout">Cerrar sesión</button></a>
        </div>

        <?php if (isset($exito)): ?>
        <div class="alert-exito">✓ Perfil actualizado correctamente</div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="foto_actual" value="<?= htmlspecialchars($artista['foto_perfil'] ?? '') ?>">
        <div class="perfil-grid">
            <!-- Avatar -->
            <div class="perfil-avatar-card">
                <div class="avatar">
                    <?php if (!empty($artista['foto_perfil'])): ?>
                        <img src="<?= htmlspecialchars($artista['foto_perfil']) ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <?= strtoupper(mb_substr($artista['nombre'], 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <h2><?= htmlspecialchars($artista['nombre']) ?></h2>
                <?php if (!empty($artista['disciplina'])): ?>
                <span class="disciplina-badge"><?= htmlspecialchars($artista['disciplina']) ?></span>
                <?php endif; ?>
                <input type="file" name="foto_perfil" class="foto-input" accept="image/*">
            </div>

            <!-- Formulario -->
            <div class="perfil-form-card">
                <h2>Editar información</h2>

                <div class="form-group">
                    <label>Nombre completo</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($artista['nombre']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <input type="email" name="correo" value="<?= htmlspecialchars($artista['correo']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" value="<?= htmlspecialchars($artista['teléfono'] ?? '') ?>" placeholder="Tu número de teléfono">
                </div>

                <div class="form-group">
                    <label>Disciplina artística</label>
                    <select name="disciplina">
                        <option value="">Selecciona una disciplina</option>
                        <?php
                        $disciplinas = ['Pintura', 'Escultura', 'Fotografía', 'Música', 'Danza', 'Teatro', 'Literatura', 'Cine', 'Arte Digital', 'Otra'];
                        foreach ($disciplinas as $d) {
                            $selected = ($artista['disciplina'] == $d) ? 'selected' : '';
                            echo "<option value='$d' $selected>$d</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Descripción / Biografía</label>
                    <textarea name="descripcion"><?= htmlspecialchars($artista['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="privacidad-section">
                    <h3>Privacidad de contacto</h3>
                    <div class="checkbox-group">
                        <input type="checkbox" id="telefono_publico" name="telefono_publico" 
                            <?= ($artista['telefono_publico'] ?? 0) ? 'checked' : '' ?>>
                        <label for="telefono_publico">Mostrar teléfono al público general</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="correo_publico" name="correo_publico"
                            <?= ($artista['correo_publico'] ?? 0) ? 'checked' : '' ?>>
                        <label for="correo_publico">Mostrar correo al público general</label>
                    </div>
                    <p style="color: var(--text-secondary); font-size: 12px; margin-top: 8px;">
                        Si no están marcados, solo el Centro Cultural podrá ver tu información de contacto.
                    </p>
                </div>

                <button type="submit" class="btn-guardar">Guardar cambios</button>
            </div>
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
        const savedTheme = loc