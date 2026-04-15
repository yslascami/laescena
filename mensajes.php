<?php
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'artista' && $_SESSION['role'] != 'centrocultural')) {
    header("Location: ing.php");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost'; $user     = getenv('DB_USER')     ?: 'root'; $password = getenv('DB_PASSWORD') ?: ''; $database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

if ($_SESSION['role'] == 'artista') {
    $artista_id = $_SESSION['artista_id'];
    $res = mysqli_query($conn, "SELECT nombre, aprobado FROM artistas WHERE id = $artista_id");
    $artista = mysqli_fetch_assoc($res);
    if (!$artista || $artista['aprobado'] != 1) {
        header("Location: pendiente.php");
        exit();
    }
}

$es_cc = $_SESSION['role'] === 'centrocultural';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        /* ── Estructura principal ─────────────────────────────── */
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .page-header h1 { font-size:32px; color:var(--primary); }

        .mensajes-wrap {
            display: grid;
            grid-template-columns: <?= $es_cc ? '280px 1fr' : '1fr' ?>;
            gap: 0;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            height: calc(100vh - 150px);
            max-height: 720px;
            overflow: hidden;
        }

        /* ── Panel izquierdo: lista de conversaciones (solo CC) ── */
        .conv-panel {
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .conv-panel-header {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .conv-buscar {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }
        .conv-buscar input {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: var(--input-bg);
            color: var(--text);
            font-family: 'Jost', sans-serif;
            font-size: 13px;
        }
        .conv-buscar input:focus { outline: none; border-color: var(--primary); }

        .conv-lista {
            flex: 1;
            overflow-y: auto;
        }

        .conv-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        .conv-item:hover { background: rgba(173,102,108,0.08); }
        .conv-item.activo { background: rgba(173,102,108,0.15); border-left: 3px solid var(--primary); }

        .conv-avatar {
            width: 40px; height: 40px; flex-shrink: 0;
            border-radius: 4px;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px; color: white;
            overflow: hidden;
        }
        .conv-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .conv-info { flex: 1; min-width: 0; }
        .conv-nombre {
            font-size: 14px; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            margin-bottom: 2px;
        }
        .conv-preview {
            font-size: 11px; color: var(--text-secondary);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .conv-meta { flex-shrink: 0; text-align: right; }
        .conv-hora { font-size: 10px; color: var(--text-secondary); margin-bottom: 4px; }
        .conv-badge {
            display: inline-block;
            background: var(--primary); color: white;
            border-radius: 10px;
            font-size: 10px;
            min-width: 18px; height: 18px;
            line-height: 18px; text-align: center;
            padding: 0 5px;
        }

        .conv-nueva {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
            color: var(--primary);
            font-size: 13px;
        }
        .conv-nueva:hover { background: rgba(173,102,108,0.08); }

        .conv-vacia {
            padding: 30px 16px;
            text-align: center;
            color: var(--text-secondary);
            font-size: 13px;
        }

        /* ── Panel derecho: chat ──────────────────────────────── */
        .chat-panel {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px;
            flex-shrink: 0;
        }
        .chat-header .ch-avatar {
            width: 38px; height: 38px; border-radius: 4px;
            background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0; overflow: hidden;
        }
        .chat-header .ch-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .chat-header h2 { font-size: 16px; color: var(--text); margin-bottom: 2px; }
        .chat-header p  { font-size: 11px; color: var(--text-secondary); }

        /* ── Mensajes ─────────────────────────────────────────── */
        .chat-messages {
            flex: 1; overflow-y: auto;
            padding: 18px 20px;
            display: flex; flex-direction: column; gap: 10px;
        }

        .msg-wrap {
            display: flex; flex-direction: column;
            max-width: 68%;
        }
        .msg-wrap.propio  { align-self: flex-end;   align-items: flex-end; }
        .msg-wrap.ajeno   { align-self: flex-start; align-items: flex-start; }

        .msg-asunto {
            font-size: 10px; color: var(--text-secondary);
            margin-bottom: 2px; font-style: italic;
        }
        .msg-burbuja {
            padding: 9px 14px; border-radius: 4px;
            font-size: 14px; line-height: 1.5; word-break: break-word;
        }
        .msg-wrap.propio .msg-burbuja {
            background: var(--primary); color: white;
            border-radius: 12px 12px 2px 12px;
        }
        .msg-wrap.ajeno .msg-burbuja {
            background: var(--bg); color: var(--text);
            border: 1px solid var(--border);
            border-radius: 12px 12px 12px 2px;
        }
        .msg-hora {
            font-size: 10px; color: var(--text-secondary);
            margin-top: 3px; padding: 0 4px;
        }
        .msg-fecha-sep {
            text-align: center; color: var(--text-secondary);
            font-size: 10px; margin: 6px 0; position: relative;
        }
        .msg-fecha-sep::before, .msg-fecha-sep::after {
            content: ''; position: absolute; top: 50%;
            width: 28%; height: 1px; background: var(--border);
        }
        .msg-fecha-sep::before { left: 0; }
        .msg-fecha-sep::after  { right: 0; }

        /* ── Input ────────────────────────────────────────────── */
        .chat-input-area {
            border-top: 1px solid var(--border);
            padding: 14px 18px;
            flex-shrink: 0;
        }
        .asunto-row {
            display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
        }
        .asunto-row label { font-size: 11px; color: var(--text-secondary); flex-shrink: 0; }
        .asunto-row input {
            flex: 1; padding: 5px 10px;
            border: 1px solid var(--border); border-radius: 4px;
            background: var(--input-bg); color: var(--text);
            font-family: 'Jost', sans-serif; font-size: 12px;
        }
        .asunto-row input:focus { outline: none; border-color: var(--primary); }

        .input-row { display: flex; gap: 10px; align-items: flex-end; }
        .input-row textarea {
            flex: 1; padding: 9px 12px;
            border: 1px solid var(--border); border-radius: 4px;
            background: var(--input-bg); color: var(--text);
            font-family: 'Jost', sans-serif; font-size: 14px;
            resize: none; height: 42px; max-height: 120px;
            transition: border-color 0.2s; line-height: 1.4;
        }
        .input-row textarea:focus { outline: none; border-color: var(--primary); }
        .btn-enviar {
            padding: 9px 18px; background: var(--primary); color: white;
            border: none; border-radius: 4px; cursor: pointer;
            font-family: 'Cormorant Garamond', serif; font-size: 16px;
            letter-spacing: 1px; transition: background 0.2s;
            flex-shrink: 0; height: 42px;
        }
        .btn-enviar:hover { background: var(--primary-dark); }
        .btn-enviar:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ── Seleccionar artista nuevo (CC) ───────────────────── */
        .nueva-conv-panel {
            display: none;
            flex-direction: column;
            gap: 0;
            overflow: hidden;
        }
        .nueva-conv-panel.visible { display: flex; }

        /* ── Pantalla vacía ───────────────────────────────────── */
        .sin-seleccion {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: var(--text-secondary); gap: 10px;
        }
        .sin-seleccion .ss-icon { font-size: 48px; }
        .sin-seleccion p { font-size: 14px; text-align: center; max-width: 200px; }

        .enviando { display: none; font-size: 11px; color: var(--text-secondary); margin-top: 5px; }
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

        <div class="mensajes-wrap">

            <?php if ($es_cc): ?>
            <!-- ══ PANEL IZQUIERDO: conversaciones (solo CC) ══ -->
            <div class="conv-panel">
                <div class="conv-panel-header">
                    <span>Conversaciones</span>
                </div>

                <div class="conv-buscar">
                    <input type="text" id="buscar-conv" placeholder="Buscar artista..."
                           oninput="filtrarConvs(this.value)">
                </div>

                <div class="conv-lista" id="conv-lista">
                    <div class="conv-vacia">Cargando conversaciones...</div>
                </div>

                <!-- Botón nueva conversación -->
                <div style="border-top:1px solid var(--border); padding:12px 14px; flex-shrink:0;">
                    <button onclick="mostrarNuevaConv()" style="width:100%; padding:8px; background:var(--primary); color:white; border:none; border-radius:4px; cursor:pointer; font-family:'Jost',sans-serif; font-size:13px;">
                        + Nueva conversación
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- ══ PANEL DERECHO: chat ══ -->
            <div class="chat-panel" id="chat-panel">

                <?php if ($es_cc): ?>
                <!-- Panel "nueva conversación": selector de artista -->
                <div id="nueva-conv-panel" style="display:none; flex-direction:column; height:100%;">
                    <div class="chat-header">
                        <div class="ch-avatar">💬</div>
                        <div><h2>Nueva conversación</h2><p>Elige el artista con quien quieres hablar</p></div>
                    </div>
                    <div style="padding:24px; display:flex; flex-direction:column; gap:12px; flex:1;">
                        <label style="color:var(--text); font-family:'Cormorant Garamond',serif; font-size:15px; letter-spacing:1px;">Artista</label>
                        <select id="artista-nuevo-select"
                            style="padding:10px; border:1px solid var(--border); border-radius:4px; background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:14px;">
                            <option value="">-- Selecciona un artista --</option>
                            <?php
                            $arts = mysqli_query($conn, "SELECT id, nombre FROM artistas WHERE aprobado = 1 ORDER BY nombre ASC");
                            while ($a = mysqli_fetch_assoc($arts)):
                            ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <button onclick="abrirConvDesdeSelector()"
                            style="padding:10px; background:var(--primary); color:white; border:none; border-radius:4px; cursor:pointer; font-family:'Cormorant Garamond',serif; font-size:17px; letter-spacing:2px;">
                            Abrir chat
                        </button>
                        <button onclick="cancelarNuevaConv()"
                            style="padding:8px; background:none; border:1px solid var(--border); color:var(--text-secondary); border-radius:4px; cursor:pointer; font-family:'Jost',sans-serif; font-size:13px;">
                            Cancelar
                        </button>
                    </div>
                </div>

                <!-- Pantalla sin selección -->
                <div id="sin-seleccion" class="sin-seleccion">
                    <div class="ss-icon">💬</div>
                    <p>Selecciona una conversación o inicia una nueva</p>
                </div>
                <?php endif; ?>

                <!-- Chat activo -->
                <div id="chat-activo" style="display:<?= $es_cc ? 'none' : 'flex' ?>; flex-direction:column; height:100%;">
                    <div class="chat-header" id="chat-header">
                        <?php if (!$es_cc): ?>
                        <div class="ch-avatar">🏛️</div>
                        <div>
                            <h2>Centro Cultural Ricardo Garibay</h2>
                            <p>Puedes escribirnos cualquier consulta</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="chat-messages" id="chat-messages">
                        <div style="text-align:center; color:var(--text-secondary); font-size:14px; margin:auto; padding:40px;" id="sin-mensajes">
                            <div style="font-size:40px; margin-bottom:12px;">💬</div>
                            <p>Aún no hay mensajes.</p>
                        </div>
                    </div>

                    <div class="chat-input-area">
                        <div class="asunto-row">
                            <label>Asunto:</label>
                            <input type="text" id="asunto-input" placeholder="Opcional">
                        </div>
                        <div class="input-row">
                            <textarea id="msg-input" placeholder="Escribe tu mensaje..." rows="1"></textarea>
                            <button class="btn-enviar" id="btn-enviar" onclick="enviarMensaje()">Enviar</button>
                        </div>
                        <div class="enviando" id="enviando">Enviando...</div>
                    </div>
                </div>

            </div><!-- /chat-panel -->
        </div><!-- /mensajes-wrap -->
    </div>

    <script>
    const MI_ROL = '<?= $_SESSION['role'] ?>';
    const ES_CC  = <?= $es_cc ? 'true' : 'false' ?>;
    <?php if (!$es_cc): ?>
    const ARTISTA_ID_PROPIO = <?= (int)$_SESSION['artista_id'] ?>;
    <?php endif; ?>

    let artistaSeleccionado    = ES_CC ? null : <?= $es_cc ? 'null' : (int)$_SESSION['artista_id'] ?>;
    let artistaSelecNombre     = '';
    let artistaSelecFoto       = '';
    let ultimoId               = 0;
    let intervalo              = null;

    // ══ LISTA DE CONVERSACIONES (CC) ══════════════════════════════
    async function cargarConversaciones() {
        if (!ES_CC) return;
        const res  = await fetch('mensajes_api.php?accion=conversaciones');
        const data = await res.json();
        const lista = document.getElementById('conv-lista');

        if (!data.conversaciones || data.conversaciones.length === 0) {
            lista.innerHTML = '<div class="conv-vacia">Aún no tienes conversaciones.<br>Inicia una nueva.</div>';
            return;
        }

        lista.innerHTML = '';
        data.conversaciones.forEach(c => {
            const item = crearItemConv(c);
            lista.appendChild(item);
        });
    }

    function crearItemConv(c) {
        const item = document.createElement('div');
        item.className = 'conv-item';
        item.dataset.id     = c.id;
        item.dataset.nombre = c.nombre.toLowerCase();

        const inicial = c.nombre.charAt(0).toUpperCase();
        const avatarHtml = c.foto_perfil
            ? `<img src="${c.foto_perfil}" alt="">`
            : inicial;

        const hora = c.ultimo_at ? formatHoraCorta(c.ultimo_at) : '';
        const preview = c.ultimo_msg ? escHtml(c.ultimo_msg).substring(0, 40) + (c.ultimo_msg.length > 40 ? '…' : '') : 'Sin mensajes aún';
        const badge   = c.no_leidos > 0 ? `<span class="conv-badge">${c.no_leidos}</span>` : '';

        item.innerHTML = `
            <div class="conv-avatar">${avatarHtml}</div>
            <div class="conv-info">
                <div class="conv-nombre">${escHtml(c.nombre)}</div>
                <div class="conv-preview">${preview}</div>
            </div>
            <div class="conv-meta">
                <div class="conv-hora">${hora}</div>
                ${badge}
            </div>`;

        item.addEventListener('click', () => abrirConversacion(c.id, c.nombre, c.foto_perfil || ''));
        return item;
    }

    function filtrarConvs(texto) {
        const q = texto.toLowerCase();
        document.querySelectorAll('.conv-item').forEach(item => {
            item.style.display = item.dataset.nombre.includes(q) ? '' : 'none';
        });
    }

    // ══ ABRIR CONVERSACIÓN ═════════════════════════════════════════
    function abrirConversacion(id, nombre, foto) {
        artistaSeleccionado = id;
        artistaSelecNombre  = nombre;
        artistaSelecFoto    = foto;

        // Resaltar en lista
        document.querySelectorAll('.conv-item').forEach(i => i.classList.remove('activo'));
        const activo = document.querySelector(`.conv-item[data-id="${id}"]`);
        if (activo) activo.classList.add('activo');

        // Actualizar header del chat
        const inicial = nombre.charAt(0).toUpperCase();
        const avatarHtml = foto
            ? `<img src="${foto}" alt="">`
            : inicial;

        document.getElementById('chat-header').innerHTML = `
            <div class="ch-avatar">${avatarHtml}</div>
            <div><h2>${escHtml(nombre)}</h2><p>Conversación directa</p></div>`;

        // Mostrar panel de chat
        document.getElementById('sin-seleccion').style.display  = 'none';
        document.getElementById('nueva-conv-panel').style.display = 'none';
        document.getElementById('chat-activo').style.display    = 'flex';

        // Reiniciar mensajes
        document.getElementById('chat-messages').innerHTML = `
            <div style="text-align:center;color:var(--text-secondary);font-size:14px;margin:auto;padding:40px;" id="sin-mensajes">
                <div style="font-size:40px;margin-bottom:12px;">💬</div>
                <p>Cargando mensajes...</p>
            </div>`;

        ultimoId = 0;
        clearInterval(intervalo);
        cargarMensajes(false);
        intervalo = setInterval(() => cargarMensajes(true), 3000);
    }

    // ══ NUEVA CONVERSACIÓN (CC) ════════════════════════════════════
    function mostrarNuevaConv() {
        document.getElementById('sin-seleccion').style.display    = 'none';
        document.getElementById('chat-activo').style.display      = 'none';
        document.getElementById('nueva-conv-panel').style.display = 'flex';
    }
    function cancelarNuevaConv() {
        document.getElementById('nueva-conv-panel').style.display = 'none';
        if (artistaSeleccionado) {
            document.getElementById('chat-activo').style.display = 'flex';
        } else {
            document.getElementById('sin-seleccion').style.display = 'flex';
        }
    }
    function abrirConvDesdeSelector() {
        const sel = document.getElementById('artista-nuevo-select');
        const id  = parseInt(sel.value);
        if (!id) return;
        const nombre = sel.options[sel.selectedIndex].text;
        // Agregar a la lista si no existe
        if (!document.querySelector(`.conv-item[data-id="${id}"]`)) {
            const lista = document.getElementById('conv-lista');
            const vacia = lista.querySelector('.conv-vacia');
            if (vacia) vacia.remove();
            const item = crearItemConv({ id, nombre, foto_perfil: '', no_leidos: 0, ultimo_msg: '', ultimo_at: '' });
            lista.prepend(item);
        }
        abrirConversacion(id, nombre, '');
    }

    // ══ CARGAR MENSAJES ════════════════════════════════════════════
    async function cargarMensajes(soloNuevos = false) {
        if (!artistaSeleccionado) return;
        const desde = soloNuevos ? ultimoId : 0;
        const res   = await fetch(`mensajes_api.php?accion=obtener&artista_id=${artistaSeleccionado}&desde_id=${desde}`);
        const data  = await res.json();
        if (!data.mensajes || data.mensajes.length === 0) return;

        const cont = document.getElementById('chat-messages');
        const sinMsg = document.getElementById('sin-mensajes');
        if (sinMsg) sinMsg.remove();

        let ultimaFecha = '';
        data.mensajes.forEach(m => {
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

        cont.scrollTop = cont.scrollHeight;

        // Actualizar preview en lista de convs si es CC
        if (ES_CC) {
            const ultimo = data.mensajes[data.mensajes.length - 1];
            const item = document.querySelector(`.conv-item[data-id="${artistaSeleccionado}"]`);
            if (item) {
                const prev = item.querySelector('.conv-preview');
                const hora = item.querySelector('.conv-hora');
                const badge = item.querySelector('.conv-badge');
                if (prev)  prev.textContent  = ultimo.mensaje.substring(0, 40) + (ultimo.mensaje.length > 40 ? '…' : '');
                if (hora)  hora.textContent  = formatHoraCorta(ultimo.created_at);
                if (badge) badge.remove(); // Limpiamos badge al abrir
            }
        }
    }

    // ══ ENVIAR MENSAJE ══════════════════════════════════════════════
    async function enviarMensaje() {
        const texto  = document.getElementById('msg-input').value.trim();
        const asunto = document.getElementById('asunto-input').value.trim();
        if (!texto || !artistaSeleccionado) return;

        const btn = document.getElementById('btn-enviar');
        btn.disabled = true;
        document.getElementById('enviando').style.display = 'block';

        const form = new FormData();
        form.append('accion',     'enviar');
        form.append('artista_id', artistaSeleccionado);
        form.append('mensaje',    texto);
        form.append('asunto',     asunto);

        const res  = await fetch('mensajes_api.php', { method: 'POST', body: form });
        const data = await res.json();

        btn.disabled = false;
        document.getElementById('enviando').style.display = 'none';

        if (data.ok) {
            document.getElementById('msg-input').value   = '';
            document.getElementById('asunto-input').value = '';
            await cargarMensajes(true);
        }
    }

    // ══ ENTER para enviar ══════════════════════════════════════════
    document.getElementById('msg-input').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje();
        }
    });

    // ══ UTILIDADES ══════════════════════════════════════════════════
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function formatHora(dt) {
        const d = new Date(dt.replace(' ','T'));
        return d.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
    }
    function formatHoraCorta(dt) {
        const d   = new Date(dt.replace(' ','T'));
        const hoy = new Date();
        if (d.toDateString() === hoy.toDateString())
            return d.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' });
        return d.toLocaleDateString('es-MX', { day:'numeric', month:'short' });
    }
    function formatFecha(f) {
        const d = new Date(f + 'T12:00:00');
        return d.toLocaleDateString('es-MX', { weekday:'long', day:'numeric', month:'long' });
    }

    // ══ TEMA ════════════════════════════════════════════════════════
    function toggleTheme() {
        const html=document.documentElement, toggle=document.getElementById('toggle'), label=document.getElementById('theme-label');
        if (html.getAttribute('data-theme')==='dark') { html.setAttribute('data-theme','light'); toggle.classList.remove('on'); label.textContent='Modo claro'; }
        else { html.setAttribute('data-theme','dark'); toggle.classList.add('on'); label.textContent='Modo oscuro'; }
        localStorage.setItem('theme', html.getAttribute('data-theme'));
    }
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
    if (savedTheme === 'light') { document.getElementById('toggle').classList.remove('on'); document.getElementById('theme-label').textContent = 'Modo claro'; }

    // ══ INICIO ══════════════════════════════════════════════════════
    if (ES_CC) {
        cargarConversaciones();
        // Refrescar lista de convs cada 10 s para badges de no leídos
        setInterval(cargarConversaciones, 10000);
    } else {
        // Artista: iniciar chat directo con CC
        cargarMensajes(false);
        intervalo = setInterval(() => cargarMensajes(true), 3000);
    }
    </script>
</body>
</html>
