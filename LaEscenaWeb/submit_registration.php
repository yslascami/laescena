<?php
require_once 'database.php';

$nombre = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = 'artista';

// Verifica si el correo ya existe
$check = "SELECT * FROM artistas WHERE correo = ?";
$stmt = mysqli_prepare($conn, $check);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "<script>alert('Este correo ya está registrado'); window.history.back();</script>";
} else {
    $sql = "INSERT INTO artistas (nombre, correo, contraseña) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $nombre, $email, $password);
    
    if (mysqli_stmt_execute($stmt)) {
        // También registra en la tabla users para poder hacer login
        $sql2 = "INSERT INTO users (email, password, role) VALUES (?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "sss", $email, $password, $role);
        mysqli_stmt_execute($stmt2);
        
        echo "<script>alert('Registro exitoso'); window.location.href='ing.html';</script>";
    } else {
        echo "<script>alert('Error al registrar'); window.history.back();</script>";
    }
}

mysqli_close($conn);
?>