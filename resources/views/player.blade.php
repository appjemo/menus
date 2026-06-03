<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#000000">
    <link rel="manifest" href="/manifest.webmanifest">
    <title>{{ $screen->name }} — JEMO Menus</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%; height: 100%; overflow: hidden;
            background: #000; cursor: none;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        #stage {
            position: absolute; top: 50%; left: 50%;
            transform-origin: center center;
        }
        #stage video, #stage .bg-fallback {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; object-fit: fill;
        }
        .bg-fallback {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            display: flex; align-items: center; justify-content: center;
            color: #334; font-size: 40px;
        }
        .slot {
            position: absolute;
            line-height: 1.05;
            text-shadow: 0 2px 8px rgba(0,0,0,.6);
            white-space: nowrap;
            font-weight: 800;
        }
        .slot .name { font-weight: 600; opacity: .95; }
        .slot .price::before { content: "$"; }
        /* Indicador de desconexión (discreto) */
        #status {
            position: fixed; bottom: 10px; right: 12px; z-index: 50;
            width: 10px; height: 10px; border-radius: 50%;
            background: #22c55e; opacity: .0; transition: opacity .3s;
        }
        #status.offline { background: #ef4444; opacity: .8; }
    </style>
</head>
<body>
    <div id="stage">
        @if (!empty($menu['template']['video_url']))
            <video id="bg" autoplay loop muted playsinline>
                <source src="{{ $menu['template']['video_url'] }}" type="video/mp4">
            </video>
        @else
            <div class="bg-fallback">No video assigned</div>
        @endif
        <div id="overlay"></div>
    </div>

    <div id="status" title="connection"></div>

    <script src="https://js.pusher.com/8.4/pusher.min.js"></script>
    <script>
        const TOKEN = @json($screen->token);
        const REVERB_KEY = @json($reverbKey);
        let menu = @json($menu);

        const stage = document.getElementById('stage');
        const overlay = document.getElementById('overlay');
        const statusDot = document.getElementById('status');

        // --- Escalado: el stage tiene las dimensiones base del video; lo ajustamos a la pantalla (contain) ---
        function fitStage() {
            const W = (menu.template && menu.template.width) || 1920;
            const H = (menu.template && menu.template.height) || 1080;
            stage.style.width = W + 'px';
            stage.style.height = H + 'px';
            const scale = Math.min(window.innerWidth / W, window.innerHeight / H);
            stage.style.transform = `translate(-50%, -50%) scale(${scale})`;
        }

        // --- Render del overlay de precios desde el JSON del menú ---
        function renderOverlay() {
            overlay.innerHTML = '';
            (menu.slots || []).forEach(slot => {
                const el = document.createElement('div');
                el.className = 'slot';
                el.style.left = slot.pos_x + 'px';
                el.style.top = slot.pos_y + 'px';
                el.style.fontSize = slot.font_size + 'px';
                el.style.color = slot.font_color || '#FFFFFF';
                el.style.textAlign = slot.align || 'left';
                if (slot.font_family) el.style.fontFamily = slot.font_family;

                if (slot.show_name && slot.name) {
                    const n = document.createElement('div');
                    n.className = 'name';
                    n.textContent = slot.name;
                    el.appendChild(n);
                }
                if (slot.price !== null && slot.price !== undefined) {
                    const p = document.createElement('span');
                    p.className = 'price';
                    p.textContent = slot.price;
                    el.appendChild(p);
                }
                overlay.appendChild(el);
            });
        }

        // --- Recargar el menú desde el servidor (al recibir evento o por fallback) ---
        async function refreshMenu() {
            try {
                const res = await fetch(`/play/${TOKEN}/menu`, { cache: 'no-store' });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                menu = await res.json();
                renderOverlay();
                fitStage();
                statusDot.classList.remove('offline');
            } catch (e) {
                // Sin conexión: conservamos el último menú mostrado (resiliencia básica)
                statusDot.classList.add('offline');
            }
        }

        // --- Inicial ---
        renderOverlay();
        fitStage();
        window.addEventListener('resize', fitStage);

        // --- Tiempo real con Reverb (protocolo Pusher) ---
        if (REVERB_KEY) {
            const pusher = new Pusher(REVERB_KEY, {
                wsHost: window.location.hostname,
                wsPort: 443,
                wssPort: 443,
                forceTLS: true,
                enabledTransports: ['ws', 'wss'],
                disableStats: true,
                cluster: 'mt1', // ignorado cuando wsHost está definido
            });

            pusher.connection.bind('connected', () => statusDot.classList.remove('offline'));
            pusher.connection.bind('unavailable', () => statusDot.classList.add('offline'));
            pusher.connection.bind('disconnected', () => statusDot.classList.add('offline'));

            const channel = pusher.subscribe('company.' + menu.company_id);
            channel.bind('menu.updated', () => refreshMenu());
        }

        // --- Fallback: refrescar cada 60s por si se perdió algún evento ---
        setInterval(refreshMenu, 60000);

        // --- Service Worker: cache de video + menú para modo offline ---
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }

        // Al volver la conexión, refrescar el menú de inmediato
        window.addEventListener('online', refreshMenu);
        window.addEventListener('offline', () => statusDot.classList.add('offline'));
    </script>
</body>
</html>
