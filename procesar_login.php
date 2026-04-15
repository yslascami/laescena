<?php
session_start();

$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'laescena';

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

    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $role;

    if ($role == 'artista') {
        $email_artista = $user['email'];
        $sql_check = "SELECT aprobado, id, nombre FROM artistas WHERE correo = ?";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "s", $email_artista);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $artista_check = mysqli_fetch_assoc($result_check);

        if ($artista_check) {
            $_SESSION['artista_id'] = $artista_check['id'];
            $_SESSION['artista_nombre'] = $artista_check['nombre'];
        }

        if ($artista_check && $artista_check['aprobado'] == 1) {
            header("Location: perfil.php");
        } else {
            header("Location: pendiente.php");
        }
    } elseif ($role == 'superadmin') {
        header("Location: panel_admin.php");
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