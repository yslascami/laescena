<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "laescena";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

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

    // Guardar sesión
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $role;

    // Buscar datos del artista si es artista
    if ($role == 'artista') {
        $email = $user['email'];
        $sql2 = "SELECT * FROM artistas WHERE correo = ?";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "s", $email);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        if (mysqli_num_rows($result2) > 0) {
            $artista = mysqli_fetch_assoc($result2);
            $_SESSION['artista_id'] = $artista['id'];
            $_SESSION['artista_nombre'] = $artista['nombre'];
        }
    }

    // Redirige según el rol
    if ($role == 'superadmin') {
        header("Location: panel_admin.php");
    } elseif ($role == 'artista') {
        header("Location: perfil.php");
    } elseif ($role == 'centrocultural') {
        header("Location: panel_cc.php");
    } else {
        header("Location: index.html");
    }
    exit();
} else {
    echo "<script>alert('Credenciales inválidas'); window.history.back();</script>";
}

mysqli_close($conn);
?>