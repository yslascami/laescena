<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: ing.html");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost'; $user     = getenv('DB_USER')     ?: 'root'; $password = getenv('DB_PASSWORD') ?: ''; $database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// Crear tabla recintos si no existe
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

// Sembrar el Centro Cultural Ricardo Garibay si la tabla está vacía
$check_recintos = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM recintos"))['n'];
if ($check_recintos == 0) {
    mysqli_query($conn, "INSERT INTO recintos (nombre, descripcion, direccion, sitio_web)
        VALUES (
            'Centro Cultural Ricardo Garibay',
            'Espacio dedicado a la promoción y difusión de la cultura local, ofreciendo eventos artísticos, exposiciones y talleres para toda la comunidad.',
            'Tulancingo, Hidalgo',
            'https://sic.cultura.gob.mx/ficha.php?table=centro_cultural&table_id=442'
        )");
}

// Estadísticas generales
$total_artistas_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM users WHERE role = 'artista'"))['n'];
$total_cc             = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM users WHERE role = 'centrocultural'"))['n'];
// Usuarios reales = artistas registrados + cuentas CC (excluye superadmin)
$total_users     = $total_artistas_users + $total_cc;
$total_artistas  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM artistas"))['n'];
$total_aprobados = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM artistas WHERE aprobado = 1"))['n'];
$total_pendientes= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM artistas WHERE aprobado = 0"))['n'];
$total_eventos   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM eventos"))['n'];
$total_port      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT artista_id) as n FROM portafolio"))['n'];
$total_recintos  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM recintos"))['n'];

// Registros por mes (últimos 6 meses)
$registros_mes = [];
for ($i = 5; $i >= 0; $i--) {
    $mes   = date('Y-m', strtotime("-$i months"));
    $label = date('M', strtotime("-$i months"));
    $res   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as n FROM artistas WHERE DATE_FORMAT(created_at, '%Y-%m') = '$mes'"));
    $registros_mes[] = ['mes' => $label, 'n' => intval($res['n'])];
}

// Distribución por disciplina
$disciplinas_res = mysqli_query($conn, "SELECT disciplina, COUNT(*) as n FROM artistas WHERE aprobado=1 AND disciplina != '' GROUP BY disciplina ORDER BY n DESC LIMIT 6");
$disciplinas = [];
while ($d = mysqli_fetch_assoc($disciplinas_res)) $disciplinas[] = $d;

// Últimos 5 registros
$ultimos = mysqli_query($conn, "SELECT nombre, correo, disciplina, aprobado, created_at FROM artistas ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }
        .page-header h1 { font-size: 32px; color: var(--primary); }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px; margin-bottom: 30px;
        }
        .stat-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px; padding: 20px; text-align: center;
            transition: border-color 0.2s;
        }
        .stat-card:hover { border-color: var(--primary); }
        .stat-card .numero {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px; color: var(--primary);
            line-height: 1; margin-bottom: 6px;
        }
        .stat-card .label { color: var(--text-secondary); font-size: 12px; }
        .stat-card .sub { color: var(--primary); font-size: 11px; margin-top: 4px; }

        /* Sección */
        .seccion-titulo {
            font-size: 20px; color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px; margin: 28px 0 16px;
        }

        /* Acciones */
        .acciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 30px;
        }
        .accion-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border); border-radius: 4px;
            padding: 20px; text-decoration: none; color: var(--text);
            transition: transform 0.2s, border-color 0.2s; display: block;
        }
        .accion-card:hover { transform: translateY(-3px); border-color: var(--primary); }
        .accion-card .ac-icon { font-size: 28px; margin-bottom: 10px; }
        .accion-card h3 { font-size: 16px; color: var(--text); margin-bottom: 6px; }
        .accion-card p { color: var(--text-secondary); font-size: 12px; line-height: 1.4; }

        /* Gráfica de barras */
        .chart-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border); border-radius: 4px;
            padding: 24px; margin-bottom: 24px;
        }
        .chart-card h3 { font-size: 16px; color: var(--primary); margin-bottom: 20px; }

        .bar-chart {
            display: flex; align-items: flex-end; gap: 12px;
            height: 140px; padding-bottom: 24px; position: relative;
            border-bottom: 1px solid var(--border);
        }
        .bar-wrap {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; gap: 6px; height: 100%; justify-content: flex-end;
        }
        .bar-val { font-size: 12px; color: var(--primary); font-weight: 500; }
        .bar {
            width: 100%; border-radius: 3px 3px 0 0;
            background: linear-gradient(to top, var(--primary-dark), var(--primary));
            transition: height 0.6s ease; min-height: 2px;
        }
        .bar-label { font-size: 11px; color: var(--text-secondary); margin-top: 6px; }

        /* Disciplinas */
        .disciplinas-list { display: flex; flex-direction: column; gap: 10px; }
        .disciplina-item { display: flex; align-items: center; gap: 12px; }
        .disciplina-nombre { font-size: 13px; color: var(--text); width: 120px; flex-shrink: 0; }
        .disciplina-barra-wrap { flex: 1; background: var(--bg); border-radius: 4px; height: 8px; overflow: hidden; }
        .disciplina-barra { height: 100%; background: var(--primary); border-radius: 4px; transition: width 0.6s ease; }
        .disciplina-n { font-size: 12px; color: var(--text-secondary); width: 24px; text-align: right; }

        /* Charts grid */
        .charts-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;
        }

        /* Últimos registros */
        .registro-item {
            display: flex; align-items: center; gap: 14px;
            padding: 12px 0; border-bottom: 1px solid var(--border);
        }
        .registro-item:last-child { border-bottom: none; }
        .reg-avatar {
            width: 36px; height: 36px; border-radius: 4px;
            background: var(--primary); color: white;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif; font-size: 18px; flex-shrink: 0;
        }
        .reg-info { flex: 1; }
        .reg-nombre { font-size: 14px; color: var(--text); }
        .reg-detalle { font-size: 12px; color: var(--text-secondary); }
        .reg-badge {
            font-size: 11px; padding: 2px 8px; border-radius: 4px;
        }
        .reg-badge.aprobado { background: rgba(173,102,108,0.2); color: var(--primary); }
        .reg-badge.pendiente { background: rgba(170,170,170,0.15); color: var(--text-secondary); }
        .reg-fecha { font-size: 11px; color: var(--text-secondary); text-align: right; }
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
                <li><a href="panel_admin.php" class="active">Panel Admin</a></li>
                <li><a href="gestionar_artistas.php">Artistas</a></li>
                <li><a href="gestionar_usuarios.php">Usuarios</a></li>
                <li><a href="gestionar_portafolios.php">Portafolios</a></li>
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
            <h1>Panel Administrador</h1>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="numero"><?= $total_users ?></div>
                <div class="label">Usuarios totales</div>
                <div class="sub"><?= $total_artistas_users ?> artistas · <?= $total_cc ?> CC</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_artistas ?></div>
                <div class="label">Artistas</div>
                <div class="sub"><?= $total_pendientes ?> pendientes</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_aprobados ?></div>
                <div class="label">Artistas aprobados</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_eventos ?></div>
                <div class="label">Eventos</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_port ?></div>
                <div class="label">Portafolios activos</div>
                <div class="sub">artistas con contenido</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_cc ?></div>
                <div class="label">Cuentas CC</div>
            </div>
            <div class="stat-card">
                <div class="numero"><?= $total_recintos ?></div>
                <div class="label">Recintos registrados</div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <h2 class="seccion-titulo">Acciones</h2>
        <div class="acciones-grid">
            <a href="gestionar_artistas.php" class="accion-card">
                <div class="ac-icon"></div>
                <h3>Gestionar Artistas</h3>
                <p>Aprobar, editar y eliminar perfiles de artistas.</p>
            </a>
            <a href="gestionar_usuarios.php" class="accion-card">
                <div class="ac-icon"></div>
                <h3>Gestionar Usuarios</h3>
                <p>Crear cuentas de Centro Cultural y administrar roles.</p>
            </a>
            <a href="gestionar_portafolios.php" class="accion-card">
                <div class="ac-icon"></div>
                <h3>Portafolios</h3>
                <p>Ver todos los portafolios con ID, nombre e información del artista.</p>
            </a>
            <a href="gestionar_recintos.php" class="accion-card">
                <div class="ac-icon"></div>
                <h3>Recintos / CC</h3>
                <p>Agregar y gestionar recintos culturales.</p>
            </a>
            
        </div>

        <!-- Gráficas -->
        <h2 class="seccion-titulo">Estadísticas visuales</h2>
        <div class="charts-grid">
            <!-- Registros por mes -->
            <div class="chart-card">
                <h3>Nuevos registros (últimos 6 meses)</h3>
                <?php
                $max = max(array_column($registros_mes, 'n'));
                $max = $max > 0 ? $max : 1;
                ?>
                <div class="bar-chart">
                    <?php foreach ($registros_mes as $r): ?>
                    <div class="bar-wrap">
                        <span class="bar-val"><?= $r['n'] ?></span>
                        <div class="bar" style="height: <?= round(($r['n'] / $max) * 100) ?>%"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex; gap:12px; margin-top:8px;">
                    <?php foreach ($registros_mes as $r): ?>
                    <div style="flex:1; text-align:center;">
                        <span class="bar-label"><?= $r['mes'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Disciplinas -->
            <div class="chart-card">
                <h3>Artistas por disciplina</h3>
                <?php if (!empty($disciplinas)): ?>
                <?php $max_d = max(array_column($disciplinas, 'n')); ?>
                <div class="disciplinas-list">
                    <?php foreach ($disciplinas as $d): ?>
                    <div class="disciplina-item">
                        <span class="disciplina-nombre"><?= htmlspecialchars($d['disciplina']) ?></span>
                        <div class="disciplina-barra-wrap">
                            <div class="disciplina-barra" style="width: <?= round(($d['n'] / $max_d) * 100) ?>%"></div>
                        </div>
                        <span class="disciplina-n"><?= $d['n'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p style="color:var(--text-secondary);font-size:13px;">No hay datos aún.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimos registros -->
        <div class="chart-card">
            <h3>Últimos registros de artistas</h3>
            <?php
            $ultimos_arr = [];
            while ($u = mysqli_fetch_assoc($ultimos)) $ultimos_arr[] = $u;
            ?>
            <?php if (!empty($ultimos_arr)): ?>
            <?php foreach ($ultimos_arr as $u): ?>
            <div class="registro-item">
                <div class="reg-avatar"><?= strtoupper(mb_substr($u['nombre'], 0, 1)) ?></div>
                <div class="reg-info">
                    <div class="reg-nombre"><?= htmlspecialchars($u['nombre']) ?></div>
                    <div class="reg-detalle"><?= htmlspecialchars($u['correo']) ?> · <?= htmlspecialchars($u['disciplina'] ?? 'Sin disciplina') ?></div>
                </div>
                <span class="reg-badge <?= $u['aprobado'] ? 'aprobado' : 'pendiente' ?>">
                    <?= $u['aprobado'] ? 'Aprobado' : 'Pendiente' ?>
                </span>
                <div class="reg-fecha"><?= date('d/m/Y', strtotime($u['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="color:var(--text-secondary);font-size:13px;">No hay registros aún.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
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