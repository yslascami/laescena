<?php
session_start();

$host     = getenv('DB_HOST')     ?: 'localhost'; $user     = getenv('DB_USER')     ?: 'root'; $password = getenv('DB_PASSWORD') ?: ''; $database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// Crear tabla de códigos de recuperación si no existe
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    codigo VARCHAR(10) NOT NULL,
    expira_en DATETIME NOT NULL,
    usado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$paso  = isset($_GET['paso'])  ? intval($_GET['paso'])  : 1;
$error = '';
$info  = '';

// ── PASO 1: Recibir correo y enviar código ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'enviar_codigo') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    // Verificar que el correo existe en users
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) === 0) {
        $error = 'No existe ninguna cuenta registrada con ese correo.';
        $paso  = 1;
    } else {
        // Generar código de 6 dígitos
        $codigo  = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expira  = date('Y-m-d H:i:s', time() + 1800); // válido 30 minutos

        // Invalidar códigos anteriores del mismo email
        mysqli_query($conn, "UPDATE password_resets SET usado = 1 WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");

        // Guardar nuevo código
        $stmt2 = mysqli_prepare($conn, "INSERT INTO password_resets (email, codigo, expira_en) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt2, "sss", $email, $codigo, $expira);
        mysqli_stmt_execute($stmt2);

        // Enviar correo
        $asunto  = 'Código de recuperación - La Escena';
        $cuerpo  = "Hola,\n\n";
        $cuerpo .= "Recibimos una solicitud para restablecer la contraseña de tu cuenta en La Escena.\n\n";
        $cuerpo .= "Tu código de verificación es:\n\n";
        $cuerpo .= "    $codigo\n\n";
        $cuerpo .= "Este código es válido durante 30 minutos.\n\n";
        $cuerpo .= "Si no solicitaste este cambio, puedes ignorar este mensaje.\n\n";
        $cuerpo .= "— La Escena";

        $headers = "From: noreply@laescena.mx\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $enviado = mail($email, $asunto, $cuerpo, $headers);

        // Guardar email en sesión para los pasos siguientes
        $_SESSION['reset_email'] = $email;

        if ($enviado) {
            $info = "Código enviado a <strong>$email</strong>. Revisa tu bandeja de entrada (y spam).";
        } else {
            // En entornos locales (XAMPP sin SMTP) el mail() falla — mostrar código en pantalla solo para desarrollo
            $info = "⚠️ El servidor no pudo enviar el correo (entorno local sin SMTP configurado).<br>
                     <strong>Código de prueba: $codigo</strong> — úsalo para continuar.";
        }
        $paso = 2;
    }
}

// ── PASO 2: Validar código ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'verificar_codigo') {
    $email  = $_SESSION['reset_email'] ?? '';
    $codigo = trim($_POST['codigo'] ?? '');

    if (!$email) {
        $error = 'Sesión expirada. Vuelve a empezar.';
        $paso  = 1;
    } else {
        $em   = mysqli_real_escape_string($conn, $email);
        $cod  = mysqli_real_escape_string($conn, $codigo);
        $ahora = date('Y-m-d H:i:s');

        $res = mysqli_query($conn, "
            SELECT id FROM password_resets
            WHERE email = '$em'
              AND codigo = '$cod'
              AND usado = 0
              AND expira_en > '$ahora'
            ORDER BY id DESC
            LIMIT 1
        ");

        if (mysqli_num_rows($res) === 0) {
            $error = 'Código incorrecto o expirado. Inténtalo de nuevo.';
            $paso  = 2;
        } else {
            $_SESSION['reset_codigo_ok'] = true;
            $paso = 3;
        }
    }
}

// ── PASO 3: Guardar nueva contraseña ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'nueva_password') {
    $email  = $_SESSION['reset_email']      ?? '';
    $ok     = $_SESSION['reset_codigo_ok']  ?? false;
    $nueva  = $_POST['nueva_password']      ?? '';
    $conf   = $_POST['confirmar_password']  ?? '';

    if (!$email || !$ok) {
        $error = 'Sesión expirada. Vuelve a empezar.';
        $paso  = 1;
    } elseif (strlen($nueva) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
        $paso  = 3;
    } elseif ($nueva !== $conf) {
        $error = 'Las contraseñas no coinciden.';
        $paso  = 3;
    } else {
        // Actualizar contraseña
        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "ss", $nueva, $email);
        mysqli_stmt_execute($stmt);

        // Invalidar todos los códigos del email
        $em = mysqli_real_escape_string($conn, $email);
        mysqli_query($conn, "UPDATE password_resets SET usado = 1 WHERE email = '$em'");

        // Limpiar sesión de recuperación
        unset($_SESSION['reset_email'], $_SESSION['reset_codigo_ok']);

        $info = 'success';
        $paso = 4;
    }
}

// Si viene de GET con ?paso=2 pero no hay sesión, volver al paso 1
if ($paso === 2 && empty($_SESSION['reset_email']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $paso = 1;
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .login-wrapper {
            display: flex; align-items: center; justify-content: center;
            flex: 1; padding: 30px;
        }
        .login-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px; padding: 40px;
            width: 100%; max-width: 440px;
        }
        .login-card h1 { font-size: 26px; color: var(--primary); margin-bottom: 8px; }
        .login-card .subtitulo { color: var(--text-secondary); font-size: 14px; margin-bottom: 28px; line-height: 1.6; }

        .pasos {
            display: flex; gap: 0; margin-bottom: 28px;
            border: 1px solid var(--border); border-radius: 4px; overflow: hidden;
        }
        .paso-ind {
            flex: 1; padding: 8px 0; text-align: center;
            font-size: 12px; color: var(--text-secondary);
            background: var(--bg); border-right: 1px solid var(--border);
            transition: all 0.2s;
        }
        .paso-ind:last-child { border-right: none; }
        .paso-ind.activo {
            background: var(--primary); color: white; font-weight: 500;
        }
        .paso-ind.completado {
            background: rgba(173,102,108,0.15); color: var(--primary);
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 6px; color: var(--text);
            font-family: 'Cormorant Garamond', serif; font-size: 15px; letter-spacing: 1px;
        }
        .form-group input {
            width: 100%; padding: 12px;
            border: 1px solid var(--border); border-radius: 4px;
            background-color: var(--input-bg); color: var(--text);
            font-family: 'Jost', sans-serif; font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus { outline: none; border-color: var(--primary); }

        /* Input de código: grande y centrado */
        .input-codigo {
            text-align: center !important;
            font-size: 28px !important;
            letter-spacing: 10px !important;
            font-family: 'Cormorant Garamond', serif !important;
            padding: 16px !important;
        }

        .btn-primary {
            width: 100%; padding: 14px;
            background-color: var(--primary); color: white; border: none;
            border-radius: 4px; font-family: 'Cormorant Garamond', serif;
            font-size: 18px; letter-spacing: 2px; cursor: pointer;
            transition: background 0.2s; margin-bottom: 16px;
        }
        .btn-primary:hover { background-color: var(--primary-dark); }

        .alert-error {
            background: rgba(200,50,50,0.15); border: 1px solid #c83232;
            color: #e07070; padding: 12px 16px; border-radius: 4px;
            font-size: 13px; margin-bottom: 18px; line-height: 1.5;
        }
        .alert-info {
            background: rgba(173,102,108,0.15); border: 1px solid var(--primary);
            color: var(--primary); padding: 12px 16px; border-radius: 4px;
            font-size: 13px; margin-bottom: 18px; line-height: 1.6;
        }
        .alert-success {
            background: rgba(60,180,80,0.15); border: 1px solid #3cb450;
            color: #5ccc70; padding: 16px; border-radius: 4px;
            font-size: 14px; text-align: center; line-height: 1.7;
        }

        .link-sec {
            text-align: center; font-size: 13px; color: var(--text-secondary);
        }
        .link-sec a { color: var(--primary); text-decoration: none; }
        .link-sec a:hover { text-decoration: underline; }

        .hint-texto {
            font-size: 12px; color: var(--text-secondary);
            margin-top: 6px; display: block; line-height: 1.5;
        }

        .reenviar-link {
            display: block; text-align: center; margin-top: 10px;
            font-size: 12px; color: var(--text-secondary);
        }
        .reenviar-link a { color: var(--primary); text-decoration: none; }
        .reenviar-link a:hover { text-decoration: underline; }
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
                <li><a href="index.php">Inicio</a></li>
                <li><a href="artistas.php">Artistas</a></li>
                <li><a href="eventos.php">Eventos</a></li>
                <li><a href="galeria.php">Galería</a></li>
                <li><a href="ing.php">Ingresar</a></li>
            </ul>
        </nav>
        <div class="theme-toggle" onclick="toggleTheme()">
            <span id="theme-label">Modo oscuro</span>
            <div class="toggle-switch on" id="toggle"></div>
        </div>
    </div>

    <div class="main-content">
        <div class="login-wrapper">
            <div class="login-card">

                <h1>Recuperar Contraseña</h1>

                <!-- Indicador de pasos -->
                <?php if ($paso < 4): ?>
                <div class="pasos">
                    <div class="paso-ind <?= $paso == 1 ? 'activo' : ($paso > 1 ? 'completado' : '') ?>">1. Correo</div>
                    <div class="paso-ind <?= $paso == 2 ? 'activo' : ($paso > 2 ? 'completado' : '') ?>">2. Código</div>
                    <div class="paso-ind <?= $paso == 3 ? 'activo' : ($paso > 3 ? 'completado' : '') ?>">3. Nueva clave</div>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($info && $info !== 'success'): ?>
                <div class="alert-info"><?= $info ?></div>
                <?php endif; ?>

                <?php if ($paso === 1): ?>
                <!-- ── PASO 1: Ingresar correo ── -->
                <p class="subtitulo">Ingresa el correo con el que te registraste y te enviaremos un código de 6 dígitos para restablecer tu contraseña.</p>
                <form method="POST">
                    <input type="hidden" name="accion" value="enviar_codigo">
                    <div class="form-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="email" placeholder="tu@correo.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                    <button type="submit" class="btn-primary">Enviar código</button>
                </form>
                <div class="link-sec"><a href="ing.php">← Volver al inicio de sesión</a></div>

                <?php elseif ($paso === 2): ?>
                <!-- ── PASO 2: Ingresar código ── -->
                <p class="subtitulo">Ingresa el código de 6 dígitos que enviamos a <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '') ?></strong>.</p>
                <form method="POST">
                    <input type="hidden" name="accion" value="verificar_codigo">
                    <div class="form-group">
                        <label>Código de verificación</label>
                        <input type="text" name="codigo" class="input-codigo"
                               placeholder="000000" maxlength="6"
                               inputmode="numeric" pattern="[0-9]{6}" required autofocus>
                        <span class="hint-texto">El código expira en 30 minutos.</span>
                    </div>
                    <button type="submit" class="btn-primary">Verificar código</button>
                </form>
                <div class="reenviar-link">
                    ¿No recibiste el código? <a href="recuperar_password.php">Solicitar uno nuevo</a>
                </div>

                <?php elseif ($paso === 3): ?>
                <!-- ── PASO 3: Nueva contraseña ── -->
                <p class="subtitulo">Crea una nueva contraseña para tu cuenta.</p>
                <form method="POST">
                    <input type="hidden" name="accion" value="nueva_password">
                    <div class="form-group">
                        <label>Nueva contraseña</label>
                        <input type="password" name="nueva_password"
                               placeholder="Mínimo 6 caracteres" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Confirmar contraseña</label>
                        <input type="password" name="confirmar_password"
                               placeholder="Repite la contraseña" required>
                    </div>
                    <button type="submit" class="btn-primary">Guardar nueva contraseña</button>
                </form>

                <?php elseif ($paso === 4): ?>
                <!-- ── PASO 4: Éxito ── -->
                <div class="alert-success">
                    ✓ Tu contraseña se actualizó correctamente.<br><br>
                    <a href="ing.php" style="color:#5ccc70; font-weight:500;">Ir al inicio de sesión →</a>
                </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script>
        // Auto-avanzar al siguiente campo cuando se completen 6 dígitos
        const inputCodigo = document.querySelector('.input-codigo');
        if (inputCodigo) {
            inputCodigo.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 6);
                if (this.value.length === 6) {
                    this.closest('form').querySelector('button[type="submit"]').focus();
                }
            });
        }

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
</body>
</html>
