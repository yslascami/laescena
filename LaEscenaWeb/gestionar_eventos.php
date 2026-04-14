<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'centrocultural') {
    header("Location: ing.html");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// Eliminar evento
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    mysqli_query($conn, "DELETE FROM eventos WHERE id = $id");
    header("Location: gestionar_eventos.php");
    exit();
}

// Crear o editar evento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $lugar = $_POST['lugar'];
    $artista = $_POST['artista'];
    $categoria = $_POST['categoria'];

    // Subir imagen si se envió
    $imagen = $_POST['imagen_actual'] ?? '';
    if (!empty($_FILES['imagen']['name'])) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nuevo_nombre = 'evento_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], 'imagenes/' . $nuevo_nombre);
        $imagen = 'imagenes/' . $nuevo_nombre;
    }

    if (!empty($_POST['id'])) {
        // Editar
        $id = intval($_POST['id']);
        $sql = "UPDATE eventos SET titulo=?, descripcion=?, fecha=?, hora=?, lugar=?, artista=?, categoria=?, imagen=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $titulo, $descripcion, $fecha, $hora, $lugar, $artista, $categoria, $imagen, $id);
    } else {
        // Crear
        $sql = "INSERT INTO eventos (titulo, descripcion, fecha, hora, lugar, artista, categoria, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssss", $titulo, $descripcion, $fecha, $hora, $lugar, $artista, $categoria, $imagen);
    }
    mysqli_stmt_execute($stmt);
    header("Location: gestionar_eventos.php?exito=1");
    exit();
}

// Obtener evento a editar
$evento_editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $result = mysqli_query($conn, "SELECT * FROM eventos WHERE id = $id");
    $evento_editar = mysqli_fetch_assoc($result);
}

// Obtener todos los eventos
$eventos = mysqli_query($conn, "SELECT * FROM eventos ORDER BY fecha ASC");

// Obtener artistas para el select
$artistas = mysqli_query($conn, "SELECT nombre FROM artistas WHERE aprobado = 1 ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Eventos - La Escena</title>
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

        .layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .form-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            height: fit-content;
        }

        .form-card h2 {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
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

        .form-group textarea { height: 80px; resize: vertical; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn-guardar {
            width: 100%;
            padding: 12px;
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

        .btn-cancelar {
            width: 100%;
            padding: 10px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-family: 'Jost', sans-serif;
            font-size: 13px;
            cursor: pointer;
            margin-top: 8px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .eventos-lista h2 {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 16px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }

        .evento-item {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 12px;
        }

        .evento-item h3 {
            font-size: 16px;
            color: var(--text);
            margin-bottom: 6px;
        }

        .evento-item .meta {
            color: var(--text-secondary);
            font-size: 12px;
            margin-bottom: 12px;
        }

        .evento-acciones {
            display: flex;
            gap: 8px;
        }

        .btn-editar {
            padding: 6px 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Jost', sans-serif;
        }

        .btn-eliminar {
            padding: 6px 14px;
            background: none;
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Jost', sans-serif;
        }

        .btn-eliminar:hover { border-color: #cc0000; color: #cc0000; }

        .alert-exito {
            background-color: rgba(173, 102, 108, 0.2);
            border: 1px solid var(--primary);
            color: var(--primary);
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            grid-column: 1 / -1;
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
                <li><a href="gestionar_eventos.php" class="active">Eventos</a></li>
                <li><a href="gestionar_galerias.php">Galerías</a></li>
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
            <h1>Gestionar Eventos</h1>
            <a href="logout.php" style="text-decoration:none; color: var(--text-secondary); font-size: 13px; border: 1px solid var(--border); padding: 8px 16px; border-radius: 4px;">Cerrar sesión</a>
        </div>

        <div class="layout">
            <?php if (isset($_GET['exito'])): ?>
            <div class="alert-exito">✓ Evento guardado correctamente</div>
            <?php endif; ?>

            <!-- Formulario -->
            <div class="form-card">
                <h2><?= $evento_editar ? 'Editar Evento' : 'Nuevo Evento' ?></h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php if ($evento_editar): ?>
                    <input type="hidden" name="id" value="<?= $evento_editar['id'] ?>">
                    <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($evento_editar['imagen'] ?? '') ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Título del evento</label>
                        <input type="text" name="titulo" value="<?= htmlspecialchars($evento_editar['titulo'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Artista</label>
                        <select name="artista">
                            <option value="">Selecciona un artista</option>
                            <?php
                            mysqli_data_seek($artistas, 0);
                            while ($a = mysqli_fetch_assoc($artistas)) {
                                $selected = (($evento_editar['artista'] ?? '') == $a['nombre']) ? 'selected' : '';
                                echo "<option value='{$a['nombre']}' $selected>{$a['nombre']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="categoria">
                            <?php
                            $categorias = ['Pintura', 'Escultura', 'Fotografía', 'Música', 'Danza', 'Teatro', 'Literatura', 'Performance', 'Instalación', 'Otro'];
                            foreach ($categorias as $c) {
                                $selected = (($evento_editar['categoria'] ?? '') == $c) ? 'selected' : '';
                                echo "<option value='$c' $selected>$c</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" name="fecha" value="<?= $evento_editar['fecha'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Hora</label>
                            <input type="time" name="hora" value="<?= $evento_editar['hora'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Lugar</label>
                        <input type="text" name="lugar" value="<?= htmlspecialchars($evento_editar['lugar'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion"><?= htmlspecialchars($evento_editar['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Imagen del evento</label>
                        <input type="file" name="imagen" accept="image/*">
                    </div>

                    <button type="submit" class="btn-guardar">Guardar evento</button>
                    <?php if ($evento_editar): ?>
                    <a href="gestionar_eventos.php" class="btn-cancelar">Cancelar edición</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Lista de eventos -->
            <div class="eventos-lista">
                <h2>Eventos registrados</h2>
                <?php
                mysqli_data_seek($eventos, 0);
                while ($evento = mysqli_fetch_assoc($eventos)) {
                    $fecha = date('d/m/Y', strtotime($evento['fecha']));
                    echo '
                    <div class="evento-item">
                        <h3>' . htmlspecialchars($evento['titulo']) . '</h3>
                        <p class="meta"> ' . $fecha . ' |  ' . htmlspecialchars($evento['artista']) . ' | ' . htmlspecialchars($evento['categoria'] ?? '') . '</p>
                        <div class="evento-acciones">
                            <a href="?editar=' . $evento['id'] . '" class="btn-editar">Editar</a>
                            <a href="?eliminar=' . $evento['id'] . '" class="btn-eliminar" onclick="return confirm(\'¿Eliminar este evento?\')">Eliminar</a>
                        </div>
                    </div>';
                }
                ?>
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
    </script>
</body>
</html>