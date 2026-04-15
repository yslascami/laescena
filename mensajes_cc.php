<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['centrocultural', 'superadmin'])) {
    header("Location: ing.php");
    exit();
}

$host     = getenv('DB_HOST')     ?: 'localhost'; $user     = getenv('DB_USER')     ?: 'root'; $password = getenv('DB_PASSWORD') ?: ''; $database = getenv('DB_NAME')     ?: 'laescena';
$conn = mysqli_connect($host, $user, $password, $database);

// Artista seleccionado
$artista_sel_id = isset($_GET['artista']) ? intval($_GET['artista']) : 0;

// Obtener artista seleccionado
$artista_sel = null;
if ($artista_sel_id) {
    $res = mysqli_query($conn, "SELECT * FROM artistas WHERE id = $artista_sel_id");
    $artista_sel = mysqli_fetch_assoc($res);
}

// Lista de artistas con mensajes + todos los artistas aprobados
$artistas_res = mysqli_query($conn, "
    SELECT a.id, a.nombre, a.foto_perfil, a.disciplina,
        (SELECT COUNT(*) FROM mensajes WHERE artista_id=a.id AND remitente='artista' AND leido=0) as no_leidos,
        (SELECT created_at FROM mensajes WHERE artista_id=a.id ORDER BY created_at DESC LIMIT 1) as ultimo_at
    FROM artistas a
    WHERE a.aprobado = 1
    ORDER BY no_leidos DESC, ultimo_at DESC, a.nombre ASC
");
$artistas_lista = [];
while ($a = mysqli_fetch_assoc($artistas_res)) $artistas_lista[] = $a;
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - La Escena</title>
    <link rel="stylesheet" href="estilos.css">
    <style>
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .page-header h1 { font-size:32px; color:var(--primary); }
        .btn-volver { text-decoration:none; color:var(--text-secondary); font-size:13px; border:1px solid var(--border); padding:8px 16px; border-radius:4px; transition:border-color 0.2s,color 0.2s; }
        .btn-volver:hover { border-color:var(--primary); color:var(--primary); }

        /* Layout */
        .mensajes-layout {
            display:grid;
            grid-template-columns:280px 1fr;
            gap:0;
            height: calc(100vh - 140px);
            max-height: 700px;
            background:var(--card-bg);
            border:1px solid var(--border);
            border-radius:4px;
            overflow:hidden;
        }

        /* Lista artistas */
        .artistas-lista {
            border-right:1px solid var(--border);
            overflow-y:auto;
            display:flex; flex-direction:column;
        }
        .artistas-lista-header {
            padding:14px 16px;
            border-bottom:1px solid var(--border);
            font-size:13px; color:var(--text-secondary);
            font-family:'Cormorant Garamond',serif; letter-spacing:1px;
            background:var(--bg);
        }
        .artista-item {
            display:flex; align-items:center; gap:12px;
            padding:12px 16px; cursor:pointer;
            border-bottom:1px solid var(--border);
            transition:background 0.15s;
            text-decoration:none; color:var(--text);
        }
        .artista-item:hover { background:rgba(173,102,108,0.08); }
        .artista-item.activo { background:rgba(173,102,108,0.15); border-left:3px solid var(--primary); }
        .art-av {
            width:38px; height:38px; border-radius:4px;
            background:var(--primary); color:white; flex-shrink:0;
            display:flex; align-items:center; justify-content:center;
            font-family:'Cormorant Garamond',serif; font-size:18px; overflow:hidden;
        }
        .art-av img { width:100%; height:100%; object-fit:cover; }
        .art-datos { flex:1; min-width:0; }
        .art-nombre { font-size:13px; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .art-ultimo { font-size:11px; color:var(--text-secondary); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:2px; }
        .art-badge {
            background:var(--primary); color:white;
            border-radius:10px; padding:1px 7px; font-size:10px; flex-shrink:0;
        }

        .sin-conv { padding:30px 16px; text-align:center; color:var(--text-secondary); font-size:13px; }

        /* Chat */
        .chat-panel {
            display:flex; flex-direction:column;
        }
        .chat-panel-header {
            padding:14px 20px; border-bottom:1px solid var(--border);
            display:flex; align-items:center; gap:12px; background:var(--bg);
        }
        .chat-panel-header .cp-av {
            width:38px; height:38px; border-radius:4px;
            background:var(--primary); color:white;
            display:flex; align-items:center; justify-content:center;
            font-family:'Cormorant Garamond',serif; font-size:18px; overflow:hidden;
        }
        .chat-panel-header .cp-av img { width:100%; height:100%; object-fit:cover; }
        .chat-panel-header h3 { font-size:16px; color:var(--text); margin-bottom:2px; }
        .chat-panel-header p  { font-size:11px; color:var(--text-secondary); }
        .chat-panel-header .ver-perfil {
            margin-left:auto; padding:5px 12px;
            border:1px solid var(--border); border-radius:4px;
            color:var(--text-secondary); font-size:12px; text-decoration:none;
            transition:all 0.2s;
        }
        .chat-panel-header .ver-perfil:hover { border-color:var(--primary); color:var(--primary); }

        .chat-messages {
            flex:1; overflow-y:auto; padding:20px;
            display:flex; flex-direction:column; gap:12px;
        }
        .msg-wrap { display:flex; flex-direction:column; max-width:70%; }
        .msg-wrap.propio  { align-self:flex-end; align-items:flex-end; }
        .msg-wrap.ajeno   { align-self:flex-start; align-items:flex-start; }
        .msg-asunto { font-size:11px; color:var(--text-secondary); margin-bottom:3px; font-style:italic; }
        .msg-burbuja { padding:10px 14px; border-radius:4px; font-size:14px; line-height:1.5; word-break:break-word; }
        .msg-wrap.propio .msg-burbuja { background:var(--primary); color:white; border-radius:12px 12px 2px 12px; }
        .msg-wrap.ajeno  .msg-burbuja { background:var(--bg); color:var(--text); border:1px solid var(--border); border-radius:12px 12px 12px 2px; }
        .msg-hora { font-size:10px; color:var(--text-secondary); margin-top:4px; padding:0 4px; }
        .msg-fecha-sep { text-align:center; color:var(--text-secondary); font-size:11px; margin:8px 0; position:relative; }
        .msg-fecha-sep::before, .msg-fecha-sep::after { content:''; position:absolute; top:50%; width:30%; height:1px; background:var(--border); }
        .msg-fecha-sep::before { left:0; } .msg-fecha-sep::after { right:0; }

        .chat-input-area { border-top:1px solid var(--border); padding:14px 20px; }
        .asunto-row { display:flex; align-items:center; gap:8px; margin-bottom:10px; }
        .asunto-row label { font-size:12px; color:var(--text-secondary); flex-shrink:0; }
        .asunto-row input { flex:1; padding:6px 10px; border:1px solid var(--border); border-radius:4px; background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:13px; }
        .asunto-row input:focus { outline:none; border-color:var(--primary); }
        .input-row { display:flex; gap:10px; align-items:flex-end; }
        .input-row textarea { flex:1; padding:10px 14px; border:1px solid var(--border); border-radius:4px; background:var(--input-bg); color:var(--text); font-family:'Jost',sans-serif; font-size:14px; resize:none; height:44px; max-height:120px; transition:border-color 0.2s; }
        .input-row textarea:focus { outline:none; border-color:var(--primary); }
        .btn-enviar { padding:10px 20px; background:var(--primary); color:white; border:none; border-radius:4px; cursor:pointer; font-family:'Cormorant Garamond',serif; font-size:16px; letter-spacing:1px; transition:background 0.2s; flex-shrink:0; height:44px; }
        .btn-enviar:hover { background:var(--primary-dark); }
        .btn-enviar:disabled { opacity:0.5; cursor:not-allowed; }

        .sin-chat { display:flex; flex-direction:column; align-items:center; justify-content:center; flex:1; color:var(--text-secondary); font-size:14px; gap:12px; }
        .sin-chat .sc-icon { font-size:48px; }

        .sin-mensajes { text-align:center; color:var(--text-secondary); font-size:13px; margin:auto; padding:30px; }
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
                <?php if ($_SESSION['role'] === 'centrocultural'): ?>
                <li><a href="panel_cc.php">Panel</a></li>
                <li><a href="gestionar_eventos.php">Eventos</a></li>
                <li><a href="gestionar_galerias.php">Galerías</a></li>
                <li><a href="artistas.php">Ver catálogo</a></li>
                <li><a href="mensajes_cc.php" class="active">Mensajes <span id="badge-nav" style="display:none;background:var(--primary);color:white;border-radius:10px;padding:1px 6px;font-size:10px;margin-left:4px;"></span></a></li>
                <?php else: ?>
                <li><a href="panel_admin.php">Panel Admin</a></li>
                <li><a href="gestionar_artistas.php">Artistas</a></li>
                <li><a href="mensajes_cc.php" class="active">Mensajes</a></li>
                <?php endif; ?>
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
            <a href="<?= $_SESSION['role'] === 'centrocultural' ? 'panel_cc.php' : 'panel_admin.php' ?>" class="btn-volver">← Panel</a>
        </div>

        <div class="mensajes-layout">
            <!-- Lista de artistas -->
            <div class="artistas-lista">
                <div class="artistas-lista-header">Artistas (<?= count($artistas_lista) ?>)</div>
                <?php if (empty($artistas_lista)): ?>
                <div class="sin-conv">No hay artistas aprobados aún.</div>
                <?php else: ?>
                <?php foreach ($artistas_lista as $a): ?>
                <a href="mensajes_cc.php?artista=<?= $a['id'] ?>"
                   class="artista-item <?= $artista_sel_id == $a['id'] ? 'activo' : '' ?>">
                    <div class="art-av">
                        <?php if (!empty($a['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($a['foto_perfil']) ?>" alt="">
                        <?php else: ?>
                            <?= strtoupper(mb_substr($a['nombre'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="art-datos">
                        <div class="art-nombre"><?= htmlspecialchars($a['nombre']) ?></div>
                        <div class="art-ultimo"><?= htmlspecialchars($a['disciplina'] ?? 'Sin disciplina') ?></div>
                    </div>
                    <?php if ($a['no_leidos'] > 0): ?>
                    <span class="art-badge"><?= $a['no_leidos'] ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Panel de chat -->
            <div class="chat-panel">
                <?php if ($artista_sel): ?>
                <div class="chat-panel-header">
                    <div class="cp-av">
                        <?php if (!empty($artista_sel['foto_perfil'])): ?>
                            <img src="<?= htmlspecialchars($artista_sel['foto_perfil']) ?>" alt="">
                        <?php else: ?>
                            <?= strtoupper(mb_substr($artista_sel['nombre'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3><?= htmlspecialchars($artista_sel['nombre']) ?></h3>
                        <p><?= htmlspecialchars($artista_sel['disciplina'] ?? 'Sin disciplina') ?> · <?= htmlspecialchars($artista_sel['correo']) ?></p>
                    </div>
                    <a href="ver_artista.php?id=<?= $artista_sel['id'] ?>" class="ver-perfil" target="_blank">Ver perfil ↗</a>
                </div>

                <div class="chat-messages" id="chat-messages">
                    <div class="sin-mensajes" id="sin-mensajes">Aún no hay mensajes con este artista.</div>
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
                </div>

                <?php else: ?>
                <div class="sin-chat">
                    <div class="sc-icon">💬</div>
                    <p>Selecciona un artista para ver la conversación</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($artista_sel): ?>
    <script>
        const ARTISTA_ID = <?= $artista_sel['id'] ?>;
        const MI_ROL     = 'centrocultural';
        let ultimoId     = 0;

        async function cargarMensajes(soloNuevos = false) {
            const desde = soloNuevos ? ultimoId : 0;
            const res   = await fetch(`mensajes_api.php?accion=obtener&artista_id=${ARTISTA_ID}&desde_id=${desde}`);
            const data  = await res.json();
            if (!data.mensajes || data.mensajes.length === 0) return;

            const cont = document.getElementById('chat-messages');
            document.getElementById('sin-mensajes').style.display = 'none';

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
        }

        async function enviarMensaje() {
            const texto  = document.getElementById('msg-input').value.trim();
            const asunto = document.getElementById('asunto-input').value.trim();
            if (!texto) return;
            const btn = document.getElementById('btn-enviar');
            btn.disabled = true;
            const form = new FormData();
            form.append('accion', 'enviar');
            form.append('artista_id', ARTISTA_ID);
            form.append('mensaje', texto);
            form.append('asunto', asunto);
            const res  = await fetch('mensajes_api.php', { method:'POST', body: form });
            const data = await res.json();
            btn.disabled = false;
            if (data.ok) {
                document.getElementById('msg-input').value = '';
                document.getElementById('asunto-input').value = '';
                await cargarMensajes(true);
            }
        }

        document.getElementById('msg-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
        });

        // Badge de no leídos en sidebar
        async function actualizarBadge() {
            const res  = await fetch('mensajes_api.php?accion=no_leidos');
            const data = await res.json();
            const badge = document.getElementById('badge-nav');
            if (data.no_leidos > 0) {
                badge.textContent = data.no_leidos;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        }

        function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
        function formatHora(dt) { const d=new Date(dt.replace(' ','T')); return d.toLocaleTimeString('es-MX',{hour:'2-digit',minute:'2-digit'}); }
        function formatFecha(f) { const d=new Date(f+'T12:00:00'); return d.toLocaleDateString('es-MX',{weekday:'long',day:'numeric',month:'long'}); }

        cargarMensajes(false);
        setInterval(() => cargarMensajes(true), 3000);
        setInterval(actualizarBadge, 5000);
    </script>
    <?php endif; ?>

    <script>
        function toggleTheme() {
            const html=document.documentElement,toggle=document.getElementById('toggle'),label=document.getElementById('theme-label');
            if(html.getAttribute('data-theme')==='dark'){html.setAttribute('data-theme','light');toggle.classList.remove('on');label.textContent='Modo claro';}
            else{html.setAttribute('data-theme','dark');toggle.classList.add('on');label.textContent='Modo oscuro';}
            localStorage.setItem('theme',html.getAttribute('data-theme'));
        }
        const savedTheme=localStorage.getItem('theme')||'dark';
        document.documentElement.setAttribute('data-theme',savedTheme);
        if(savedTheme==='light'){document.getElementById('toggle').classList.remove('on');document.getElementById('theme-label').textContent='Modo claro';}
    </script>
</body>
</html>