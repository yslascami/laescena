<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'artista' && $_SESSION['role'] != 'centrocultural')) {
    header("Location: ing.php");
    exit();
}

$host = "localhost"; $user = "root"; $password = ""; $database = "laescena";
$conn = mysqli_connect($host, $user, $password, $database);

if ($_SESSION['role'] == 'artista') {
    $artista_id = $_SESSION['artista_id'];
    // Verificar aprobación
    $res = mysqli_query($conn, "SELECT nombre, aprobado FROM artistas WHERE id = $artista_id");
    $artista = mysqli_fetch_assoc($res);
    if (!$artista || $artista['aprobado'] != 1) {
        header("Location: pendiente.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-header h1 { font-size:32px; color:var(--primary); }

        /* Chat container */
        .chat-wrap {
            background:var(--card-bg);
            border:1px solid var(--border);
            border-radius:4px;
            display:flex;
            flex-direction:column;
            height: calc(100vh - 160px);
            max-height: 700px;
        }

        .chat-header {
            padding:16px 20px;
            border-bottom:1px solid var(--border);
            display:flex; align-items:center; gap:12px;
        }
        .chat-header .cc-avatar {
            width:40px; height:40px; border-radius:4px;
            background:var(--primary); display:flex; align-items:center; justify-content:center;
            font-size:20px; flex-shrink:0;
        }
        .chat-header h2 { font-size:17px; color:var(--text); margin-bottom:2px; }
        .chat-header p  { font-size:12px; color:var(--text-secondary); }
        .online-dot { width:8px; height:8px; border-radius:50%; background:#4caf50; display:inline-block; margin-left:6px; }

        /* Mensajes */
        .chat-messages {
            flex:1; overflow-y:auto; padding:20px;
            display:flex; flex-direction:column; gap:12px;
        }

        .msg-wrap {
            display:flex; flex-direction:column;
            max-width:70%;
        }
        .msg-wrap.propio  { align-self:flex-end; align-items:flex-end; }
        .msg-wrap.ajeno   { align-self:flex-start; align-items:flex-start; }

        .msg-asunto {
            font-size:11px; color:var(--text-secondary);
            margin-bottom:3px; font-style:italic;
        }

        .msg-burbuja {
            padding:10px 14px; border-radius:4px;
            font-size:14px; line-height:1.5; word-break:break-word;
        }
        .msg-wrap.propio .msg-burbuja {
            background:var(--primary);
            color:white;
            border-radius:12px 12px 2px 12px;
        }
        .msg-wrap.ajeno .msg-burbuja {
            background:var(--bg);
            color:var(--text);
            border:1px solid var(--border);
            border-radius:12px 12px 12px 2px;
        }
        .msg-hora {
            font-size:10px; color:var(--text-secondary);
            margin-top:4px; padding: 0 4px;
        }

        .msg-fecha-sep {
            text-align:center; color:var(--text-secondary);
            font-size:11px; margin:8px 0;
            position:relative;
        }
        .msg-fecha-sep::before, .msg-fecha-sep::after {
            content:''; position:absolute; top:50%;
            width:30%; height:1px; background:var(--border);
        }
        .msg-fecha-sep::before { left:0; }
        .msg-fecha-sep::after  { right:0; }

        /* Input */
        .chat-input-area {
            border-top:1px solid var(--border);
            padding:16px 20px;
        }
        .asunto-row {
            display:flex; align-items:center; gap:8px; margin-bottom:10px;
        }
        .asunto-row label { font-size:12px; color:var(--text-secondary); flex-shrink:0; }
        .asunto-row input {
            flex:1; padding:6px 10px;
            border:1px solid var(--border); border-radius:4px;
            background:var(--input-bg); color:var(--text);
            font-family:'Jost',sans-serif; font-size:13px;
        }
        .asunto-row input:focus { outline:none; border-color:var(--primary); }

        .input-row {
            display:flex; gap:10px; align-items:flex-end;
        }
        .input-row textarea {
            flex:1; padding:10px 14px;
            border:1px solid var(--border); border-radius:4px;
            background:var(--input-bg); color:var(--text);
            font-family:'Jost',sans-serif; font-size:14px;
            resize:none; height:44px; max-height:120px;
            transition:border-color 0.2s;
            line-height:1.4;
        }
        .input-row textarea:focus { outline:none; border-color:var(--primary); }
        .btn-enviar {
            padding:10px 20px; background:var(--primary); color:white;
            border:none; border-radius:4px; cursor:pointer;
            font-family:'Cormorant Garamond',serif; font-size:16px;
            letter-spacing:1px; transition:background 0.2s; flex-shrink:0;
            height:44px;
        }
        .btn-enviar:hover { background:var(--primary-dark); }
        .btn-enviar:disabled { opacity:0.5; cursor:not-allowed; }

        .sin-mensajes {
            text-align:center; color:var(--text-secondary);
            font-size:14px; margin:auto; padding:40px;
        }
        .sin-mensajes .sm-icon { font-size:40px; margin-bottom:12px; }

        /* Typing indicator */
        .enviando { display:none; font-size:12px; color:var(--text-secondary); margin-top:6px; }
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
            <?php if ($_SESSION['role'] == 'artista'): ?>
                <li><a href="perfil.php">Mi Perfil</a></li>
                <li><a href="portafolio.php">Mi Portafolio</a></li>
            <?php elseif ($_SESSION['role'] == 'centrocultural'): ?>
                <li><a href="panel_cc.php">Panel</a></li>
            <?php endif; ?>
            <li><a href="mensajes.php" class="active">Mensajes</a></li>
            <li><a href="logout.php">Cerrar sesión</a></li>
        </ul>
    </nav>
    <div class="theme-toggle" onclick="toggleTheme()">
        <span id="theme-label">Modo oscuro</span>
        <div class="toggle-switch on" id="toggle"></div>
    </div>
</div>

    <div class="main-content">
        <div class="page-header">
            <h1>Mensajes</h1>
        </div>

        <div class="chat-wrap">
            <div class="chat-header">
    <?php if ($_SESSION['role'] == 'centrocultural'): ?>
    <div class="cc-avatar"></div>
    <div style="flex:1">
        <h2>Selecciona un artista</h2>
        <select id="artista-select" onchange="cambiarArtista(this.value)" style="margin-top:6px; padding:6px 10px; border:1px solid var(--border); border-radius:4px; background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:13px; width:100%;">
            <option value="">-- Selecciona un artista --</option>
            <?php
            $arts = mysqli_query($conn, "SELECT id, nombre FROM artistas WHERE aprobado = 1 ORDER BY nombre ASC");
            while ($a = mysqli_fetch_assoc($arts)) {
                echo "<option value='{$a['id']}'>" . htmlspecialchars($a['nombre']) . "</option>";
            }
            ?>
        </select>
    </div>
    <?php else: ?>
    <div class="cc-avatar"></div>
    <div>
        <h2>Centro Cultural Ricardo Garibay</h2>
        <p>Puedes escribirnos cualquier consulta<span class="online-dot"></span></p>
    </div>
    <?php endif; ?>
</div>

            <div class="chat-messages" id="chat-messages">
                <div class="sin-mensajes" id="sin-mensajes">
                    <div class="sm-icon"></div>
                    <p>Aún no hay mensajes.<br></p>
                </div>
            </div>

            <div class="chat-input-area">
                <div class="asunto-row">
                    <label>Asunto:</label>
                    <input type="text" id="asunto-input" placeholder="Opcional — ¿De qué trata tu mensaje?">
                </div>
                <div class="input-row">
                    <textarea id="msg-input" placeholder="Escribe tu mensaje..." rows="1"></textarea>
                    <button class="btn-enviar" id="btn-enviar" onclick="enviarMensaje()">Enviar</button>
                </div>
                <div class="enviando" id="enviando">Enviando...</div>
            </div>
        </div>
    </div>

    <script>
        const MI_ROL = '<?= $_SESSION['role'] ?>';
<?php if ($_SESSION['role'] == 'centrocultural'): ?>
const ARTISTA_ID = null;
<?php else: ?>
const ARTISTA_ID = <?= $_SESSION['artista_id'] ?>;
<?php endif; ?>

        // ── Cargar mensajes ────────────────────────────────────
        async function cargarMensajes(soloNuevos = false) {
            const desde = soloNuevos ? ultimoId : 0;
            const res = await fetch(`mensajes_api.php?accion=obtener&artista_id=${artistaSeleccionado}&desde_id=${desde}`);
            const data  = await res.json();
            if (!data.mensajes) return;

            const msgs = data.mensajes;
            if (msgs.length === 0) return;

            const cont = document.getElementById('chat-messages');
            document.getElementById('sin-mensajes').style.display = 'none';

            let ultimaFecha = '';
            msgs.forEach(m => {
                const fecha = m.created_at.split(' ')[0];
                if (fecha !== ultimaFecha) {
                    ultimaFecha = fecha;
                    const sep = document.createElement('div');
                    sep.className = 'msg-fecha-sep';
                    sep.textContent = formatFecha(fecha);
                    cont.appendChild(sep);
                }

                const esPropio = m.remitente === MI_ROL;
                const wrap = document.createElement('div');
                wrap.className = 'msg-wrap ' + (esPropio ? 'propio' : 'ajeno');
                wrap.dataset.id = m.id;

                let html = '';
                if (m.asunto) html += `<div class="msg-asunto">📌 ${escHtml(m.asunto)}</div>`;
                html += `<div class="msg-burbuja">${escHtml(m.mensaje).replace(/\n/g,'<br>')}</div>`;
                html += `<div class="msg-hora">${formatHora(m.created_at)}</div>`;
                wrap.innerHTML = html;
                cont.appendChild(wrap);

                if (parseInt(m.id) > ultimoId) ultimoId = parseInt(m.id);
            });

            // Scroll al fondo
            cont.scrollTop = cont.scrollHeight;
        }

        // ── Enviar mensaje ─────────────────────────────────────
        async function enviarMensaje() {
            const texto  = document.getElementById('msg-input').value.trim();
            const asunto = document.getElementById('asunto-input').value.trim();
           if (!texto || !artistaSeleccionado) return;
form.append('artista_id', artistaSeleccionado);

            const btn = document.getElementById('btn-enviar');
            btn.disabled = true;
            document.getElementById('enviando').style.display = 'block';

            const form = new FormData();
            form.append('accion', 'enviar');
            form.append('artista_id', ARTISTA_ID);
            form.append('mensaje', texto);
            form.append('asunto', asunto);

            const res  = await fetch('mensajes_api.php', { method:'POST', body: form });
            const data = await res.json();

            btn.disabled = false;
            document.getElementById('enviando').style.display = 'none';

            if (data.ok) {
                document.getElementById('msg-input').value  = '';
                document.getElementById('asunto-input').value = '';
                await cargarMensajes(true);
            }
        }

        // ── Polling cada 3 segundos ────────────────────────────
        function iniciarPolling() {
            cargarMensajes(false);
            intervalo = setInterval(() => cargarMensajes(true), 3000);
        }

        // ── Enter para enviar ──────────────────────────────────
        document.getElementById('msg-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                enviarMensaje();
            }
        });

        // ── Utilidades ─────────────────────────────────────────
        function escHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
        function formatHora(dt) {
            const d = new Date(dt.replace(' ','T'));
            return d.toLocaleTimeString('es-MX', {hour:'2-digit', minute:'2-digit'});
        }
        function formatFecha(f) {
            const d = new Date(f + 'T12:00:00');
            return d.toLocaleDateString('es-MX', {weekday:'long', day:'numeric', month:'long'});
        }

        // ── Tema ───────────────────────────────────────────────
        function toggleTheme() {
            const html=document.documentElement,toggle=document.getElementById('toggle'),label=document.getElementById('theme-label');
            if(html.getAttribute('data-theme')==='dark'){html.setAttribute('data-theme','light');toggle.classList.remove('on');label.textContent='Modo claro';}
            else{html.setAttribute('data-theme','dark');toggle.classList.add('on');label.textContent='Modo oscuro';}
            localStorage.setItem('theme',html.getAttribute('data-theme'));
        }
        const savedTheme=localStorage.getItem('theme')||'dark';
        document.documentElement.setAttribute('data-theme',savedTheme);
        if(savedTheme==='light'){document.getElementById('toggle').classList.remove('on');document.getElementById('theme-label').textContent='Modo claro';}

        // ── Iniciar ────────────────────────────────────────────
        iniciarPolling();
    </script>
</body>
</html>