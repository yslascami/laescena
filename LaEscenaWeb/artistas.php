
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
    <title>Artistas - La Escena</title>
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
        .buscador-grid {
    display: grid;
    grid-template-columns: 1fr 200px;
    gap: 12px;
    margin-bottom: 16px;
}

.buscador-input input,
.buscador-select select {
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

.buscador-input input:focus,
.buscador-select select:focus {
    outline: none;
    border-color: var(--primary);
}

.contador {
    color: var(--text-secondary);
    font-size: 13px;
    margin-bottom: 20px;
}

.artista-card.oculto {
    display: none;
}

        .artistas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .artista-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            padding: 24px;
            transition: transform 0.2s, border-color 0.2s;
        }

        .artista-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
        }

        .artista-avatar {
            width: 60px;
            height: 60px;
            background-color: var(--primary);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 16px;
            color: white;
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
        }

        .artista-card h3 {
            font-size: 20px;
            color: var(--text);
            margin-bottom: 8px;
        }

        .artista-card .info {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 4px;
        }

        .artista-card .badge {
            margin-top: 16px;
        }

        .no-artistas {
            text-align: center;
            color: var(--text-secondary);
            font-size: 18px;
            margin-top: 40px;
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
                <li><a href="index.html">Inicio</a></li>
                <li><a href="CC.html">Centro Cultural</a></li>
                <li><a href="artistas.php" class="active">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
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
            <h1>Catálogo de Artistas</h1>
            <p>Conoce a los artistas que forman parte de La Escena</p>
        </div>
        <!-- Buscador -->
<div class="buscador-grid">
    <div class="buscador-input">
        <input type="text" id="buscarNombre" placeholder="Buscar por nombre..." oninput="filtrarArtistas()">
    </div>
    <div class="buscador-select">
        <select id="filtrarDisciplina" onchange="filtrarArtistas()">
            <option value="">Todas las disciplinas</option>
            <option value="Pintura">Pintura</option>
            <option value="Escultura">Escultura</option>
            <option value="Fotografía">Fotografía</option>
            <option value="Música">Música</option>
            <option value="Danza">Danza</option>
            <option value="Teatro">Teatro</option>
            <option value="Literatura">Literatura</option>
            <option value="Cine">Cine</option>
            <option value="Arte Digital">Arte Digital</option>
            <option value="Otra">Otra</option>
        </select>
    </div>
</div>
<p class="contador" id="contador"></p>

        <div class="artistas-grid">
            <?php
            $sql = "SELECT * FROM artistas";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                while ($artista = mysqli_fetch_assoc($result)) {
                    $inicial = strtoupper(mb_substr($artista['nombre'], 0, 1));
                    echo '
<div class="artista-card" data-nombre="' . strtolower($artista['nombre']) . '" data-disciplina="' . htmlspecialchars($artista['disciplina'] ?? '') . '">
    <div class="artista-avatar">' . $inicial . '</div>
    <h3>' . htmlspecialchars($artista['nombre']) . '</h3>
    <p class="info"> ' . htmlspecialchars($artista['correo']) . '</p>
    <p class="info"> ' . htmlspecialchars($artista['teléfono']) . '</p>
    <span class="badge">' . htmlspecialchars($artista['disciplina'] ?? 'Artista') . '</span>
</div>';
                }
            } else {
                echo '<p class="no-artistas">No hay artistas registrados aún.</p>';
            }
            mysqli_close($conn);
            ?>
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
    <script>
    function filtrarArtistas() {
        const nombre = document.getElementById('buscarNombre').value.toLowerCase();
        const disciplina = document.getElementById('filtrarDisciplina').value;
        const cards = document.querySelectorAll('.artista-card');
        let visibles = 0;

        cards.forEach(card => {
            const nombreCard = card.getAttribute('data-nombre');
            const disciplinaCard = card.getAttribute('data-disciplina');
            const coincideNombre = nombreCard.includes(nombre);
            const coincideDisciplina = disciplina === '' || disciplinaCard === disciplina;

            if (coincideNombre && coincideDisciplina) {
                card.classList.remove('oculto');
                visibles++;
            } else {
                card.classList.add('oculto');
            }
        });

        document.getElementById('contador').textContent = visibles + ' artista(s) encontrado(s)';
    }

    // Inicializar contador
    filtrarArtistas();
</script>
</body>
</html>