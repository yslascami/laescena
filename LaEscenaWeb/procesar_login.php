<?php
require_once 'database.php';

$usuario = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = ? AND password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $usuario, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $role = $user['role'];

    // Redirige según el rol
    if ($role == 'superadmin') {
        header("Location: superadmin.html");
    } elseif ($role == 'artista') {
        header("Location: AT.html");
    } elseif ($role == 'centrocultural') {
        header("Location: CC.html");
    } else {
        header("Location: index.html");
    }
    exit();
} else {
    echo "<script>alert('Credenciales inválidas'); window.history.back();</script>";
}

mysqli_close($conn);
?>