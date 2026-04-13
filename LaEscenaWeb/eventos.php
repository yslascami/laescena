<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) die("Error de conexión: " . mysqli_connect_error());
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            color: var(--primary);
        }

        .page-header p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-top: 6px;
        }

        .evento-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            margin-bottom: 20px;
            transition: transform 0.2s, border-color 0.2s;
        }

        .evento-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .evento-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .evento-card h2 {
            font-size: 22px;
            color: var(--text);
            flex: 1;
            margin-right: 16px;
        }

        .evento-meta {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 6px;
            font-family: 'Jost', sans-serif;
        }

        .evento-descripcion {
            color: var(--text-secondary);
            font-size: 14px;
            line-height: 1.6;
            margin: 16px 0;
        }

        .evento-card img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 4px;
            margin-top: 16px;
        }

        .btn-mas-info {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            font-family: 'Cormorant Garamond', serif;
            font-size: 16px;
            letter-spacing: 1px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-mas-info:hover { background-color: var(--primary-dark); }

        .no-eventos {
            text-align: center;
            color: var(--text-secondary);
            font-size: 18px;
            margin-top: 40px;
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
                <li><a href="index.html">Inicio</a></li>
                <li><a href="CC.html">Centro Cultural</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php" class="active">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <li><a href="Reg.html">Registro</a></li>
                <li><a href="ing.html">Ingresar</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1>Agenda Cultural</h1>
            <p>Próximos eventos y actividades</p>
        </div>

        <?php
        $sql = "SELECT * FROM eventos ORDER BY fecha ASC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($evento = mysqli_fetch_assoc($result)) {
                $fecha = date('d \d\e F \d\e\l Y', strtotime($evento['fecha']));
                echo '
                <div class="evento-card">
                    <div class="evento-header">
                        <h2>' . htmlspecialchars($evento['titulo']) . '</h2>
                        <span class="badge">' . htmlspecialchars($evento['categoria'] ?? 'Evento') . '</span>
                    </div>
                    <p class="evento-meta"> ' . $fecha . ' &nbsp;|&nbsp;  ' . $evento['hora'] . ' &nbsp;|&nbsp;  ' . htmlspecialchars($evento['lugar']) . '</p>
                    <p class="evento-meta"> ' . htmlspecialchars($evento['artista']) . '</p>
                    <p class="evento-descripcion">' . htmlspecialchars($evento['descripcion']) . '</p>';

                if (!empty($evento['imagen'])) {
                    echo '<img src="' . htmlspecialchars($evento['imagen']) . '" alt="' . htmlspecialchars($evento['titulo']) . '">';
                }

                echo '<a class="btn-mas-info" href="#">Más información</a>
                </div>';
            }
        } else {
            echo '<p class="no-eventos">No hay eventos próximos.</p>';
        }
        mysqli_close($conn);
        ?>
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