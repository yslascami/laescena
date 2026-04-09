<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Galerías - La Escena</title>
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
        .galeria-titulo {
            color: rgb(129, 52, 58);
            border-bottom: 2px solid rgb(173, 102, 108);
            padding-bottom: 10px;
        }
        .foto-card {
            margin-bottom: 20px;
        }
        .foto-card img {
            width: 648px;
            height: 432px;
            object-fit: cover;
            border-radius: 8px;
        }
        .foto-card footer {
            color: rgb(107, 91, 92);
            font-style: italic;
            margin-top: 5px;
        }
        .descripcion {
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    <header>
        <h1>Galerías Actuales</h1>
    </header>
    <nav>
        <a href="index.html">Inicio</a>
        <a href="CC.html">Centro Cultural</a>
        <a href="artistas.php">Artistas</a>
        <a href="eventos.php">Eventos</a>
        <a href="galeria.php">Galería</a>
        <a href="Reg.html">Registro</a>
        <a href="ing.html">Ingresar</a>
    </nav>
    <main>
        <?php
        // Obtener galerías únicas
        $sql_galerias = "SELECT DISTINCT titulo, descripcion, artista FROM galerias";
        $result_galerias = mysqli_query($conn, $sql_galerias);

        while ($galeria = mysqli_fetch_assoc($result_galerias)) {
            echo '<h2 class="galeria-titulo">' . htmlspecialchars($galeria['titulo']) . '</h2>';
            
            if (!empty($galeria['descripcion'])) {
                echo '<p class="descripcion">' . htmlspecialchars($galeria['descripcion']) . '</p>';
            }

            // Obtener fotos de esta galería
            $titulo = mysqli_real_escape_string($conn, $galeria['titulo']);
            $sql_fotos = "SELECT * FROM galerias WHERE titulo = '$titulo'";
            $result_fotos = mysqli_query($conn, $sql_fotos);

            while ($foto = mysqli_fetch_assoc($result_fotos)) {
                echo '<div class="foto-card">';
                echo '<img src="' . htmlspecialchars($foto['imagen']) . '" alt="' . htmlspecialchars($foto['titulo']) . '">';
                if (!empty($foto['pie_foto'])) {
                    echo '<footer>' . htmlspecialchars($foto['pie_foto']) . '</footer>';
                }
                echo '</div>';
            }
        }

        mysqli_close($conn);
        ?>
    </main>
</body>
</html>