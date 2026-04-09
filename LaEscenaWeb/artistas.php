<?php require_once 'database.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Artistas - La Escena</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: rgb(173, 102, 108);
            color: black;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        nav {
            background-color: rgb(107, 91, 92);
            padding: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            font-family: verdana;
        }
        main {
            padding: 30px;
        }
        .artista-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .artista-info h2 {
            margin: 0 0 8px 0;
            color: rgb(129, 52, 58);
        }
        .artista-info p {
            margin: 4px 0;
            color: #555;
        }
        .badge {
            background-color: rgb(173, 102, 108);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-top: 8px;
        }
        .no-artistas {
            text-align: center;
            color: #888;
            font-size: 18px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Catálogo de Artistas</h1>
    </header>
    <nav>
        <a href="index.html">Inicio</a>
        <a href="CC.html">Centro Cultural</a>
        <a href="artistas.php">Artistas</a>
        <a href="eve.html">Eventos</a>
        <a href="gale.html">Galería</a>
        <a href="Reg.html">Registro</a>
        <a href="ing.html">Ingresar</a>
    </nav>
    <main>
        <?php
        $sql = "SELECT * FROM artistas";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($artista = mysqli_fetch_assoc($result)) {
                echo '
                <div class="artista-card">
                    <div class="artista-info">
                        <h2>' . htmlspecialchars($artista['nombre']) . '</h2>
                        <p>📧 ' . htmlspecialchars($artista['correo']) . '</p>
                        <p>📞 ' . htmlspecialchars($artista['teléfono']) . '</p>
                        <span class="badge">Artista</span>
                    </div>
                </div>';
            }
        } else {
            echo '<p class="no-artistas">No hay artistas registrados aún.</p>';
        }

        mysqli_close($conn);
        ?>
    </main>
</body>
</html>