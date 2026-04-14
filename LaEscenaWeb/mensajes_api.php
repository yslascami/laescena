<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role'])) {
    echo json_encode(['error' => 'no_auth']);
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

// Crear tabla si no existe
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    artista_id INT NOT NULL,
    remitente ENUM('artista','centrocultural') NOT NULL,
    asunto VARCHAR(255) DEFAULT '',
    mensaje TEXT NOT NULL,
    leido TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// ── Obtener mensajes de una conversación ──────────────────────
if ($accion === 'obtener') {
    $artista_id = intval($_GET['artista_id'] ?? 0);
    $desde_id   = intval($_GET['desde_id'] ?? 0);

    // Validar acceso
    if ($_SESSION['role'] === 'artista' && $_SESSION['artista_id'] != $artista_id) {
        echo json_encode(['error' => 'sin_permiso']); exit();
    }

    // Marcar como leídos los mensajes del otro
    if ($_SESSION['role'] === 'artista') {
        mysqli_query($conn, "UPDATE mensajes SET leido=1 WHERE artista_id=$artista_id AND remitente='centrocultural' AND leido=0");
    } else {
        mysqli_query($conn, "UPDATE mensajes SET leido=1 WHERE artista_id=$artista_id AND remitente='artista' AND leido=0");
    }

    $where_desde = $desde_id > 0 ? "AND id > $desde_id" : "";
    $res = mysqli_query($conn, "SELECT * FROM mensajes WHERE artista_id = $artista_id $where_desde ORDER BY created_at ASC");
    $msgs = [];
    while ($m = mysqli_fetch_assoc($res)) $msgs[] = $m;
    echo json_encode(['mensajes' => $msgs]);
    exit();
}

// ── Enviar mensaje ─────────────────────────────────────────────
if ($accion === 'enviar') {
    $artista_id = intval($_POST['artista_id'] ?? 0);
    $mensaje    = trim($_POST['mensaje'] ?? '');
    $asunto     = trim($_POST['asunto'] ?? '');
    $remitente  = $_SESSION['role'] === 'artista' ? 'artista' : 'centrocultural';

    if (!$artista_id || !$mensaje) {
        echo json_encode(['error' => 'datos_incompletos']); exit();
    }
    if ($_SESSION['role'] === 'artista' && $_SESSION['artista_id'] != $artista_id) {
        echo json_encode(['error' => 'sin_permiso']); exit();
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO mensajes (artista_id, remitente, asunto, mensaje) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "isss", $artista_id, $remitente, $asunto, $mensaje);
    mysqli_stmt_execute($stmt);
    $nuevo_id = mysqli_insert_id($conn);

    $res = mysqli_query($conn, "SELECT * FROM mensajes WHERE id = $nuevo_id");
    $msg = mysqli_fetch_assoc($res);
    echo json_encode(['ok' => true, 'mensaje' => $msg]);
    exit();
}

// ── Contar no leídos (para badge) ─────────────────────────────
if ($accion === 'no_leidos') {
    if ($_SESSION['role'] === 'artista') {
        $artista_id = intval($_SESSION['artista_id']);
        $res = mysqli_query($conn, "SELECT COUNT(*) as n FROM mensajes WHERE artista_id=$artista_id AND remitente='centrocultural' AND leido=0");
    } else {
        $res = mysqli_query($conn, "SELECT COUNT(*) as n FROM mensajes WHERE remitente='artista' AND leido=0");
    }
    $row = mysqli_fetch_assoc($res);
    echo json_encode(['no_leidos' => intval($row['n'])]);
    exit();
}

// ── Lista de conversaciones para CC ───────────────────────────
if ($accion === 'conversaciones') {
    if ($_SESSION['role'] !== 'centrocultural' && $_SESSION['role'] !== 'superadmin') {
        echo json_encode(['error' => 'sin_permiso']); exit();
    }
    $res = mysqli_query($conn, "
        SELECT a.id, a.nombre, a.foto_perfil, a.disciplina,
               (SELECT COUNT(*) FROM mensajes WHERE artista_id=a.id AND remitente='artista' AND leido=0) as no_leidos,
               (SELECT mensaje FROM mensajes WHERE artista_id=a.id ORDER BY created_at DESC LIMIT 1) as ultimo_msg,
               (SELECT created_at FROM mensajes WHERE artista_id=a.id ORDER BY created_at DESC LIMIT 1) as ultimo_at
        FROM artistas a
        WHERE EXISTS (SELECT 1 FROM mensajes WHERE artista_id = a.id)
        ORDER BY ultimo_at DESC
    ");
    $convs = [];
    while ($c = mysqli_fetch_assoc($res)) $convs[] = $c;
    echo json_encode(['conversaciones' => $convs]);
    exit();
}

echo json_encode(['error' => 'accion_desconocida']);
?>