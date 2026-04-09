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
    <title>Eventos - La Escena</title>
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
        .evento-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .evento-card h2 {
            color: rgb(129, 52, 58);
            margin-top: 0;
        }
        .evento-card img {
            width: 648px;
            height: 432px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 15px;
        }
        .evento-meta {
            color: rgb(107, 91, 92);
            font-size: 14px;
            margin: 8px 0;
        }
        .no-eventos {
            text-align: center;
            color: #888;
            font-size: 18px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Eventos Próximos</h1>
    </header>
    <nav>
        <a href="index.html">Inicio</a>
        <a href="CC.html">Centro Cultural</a>
        <a href="artistas.php">Artistas</a>
        <a href="eventos.php">Eventos</a>
        <a href="gale.html">Galería</a>
        <a href="Reg.html">Registro</a>
        <a href="ing.html">Ingresar</a>
    </nav>
    <main>
        <?php
        $sql = "SELECT * FROM eventos ORDER BY fecha ASC";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($evento = mysqli_fetch_assoc($result)) {
                $fecha = date('d \d\e F \d\e\l Y', strtotime($evento['fecha']));
               echo '<div class="evento-card">';
                echo '<h2>' . htmlspecialchars($evento['titulo']) . '</h2>';
                echo '<p class="evento-meta"> ' . $fecha . ' | ' . $evento['hora'] . ' | ' . htmlspecialchars($evento['lugar']) . '</p>';
                echo '<p class="evento-meta"> ' . htmlspecialchars($evento['artista']) . '</p>';
                echo '<p>' . htmlspecialchars($evento['descripcion']) . '</p>';}
        }