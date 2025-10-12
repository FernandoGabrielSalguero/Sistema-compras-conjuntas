/*! SVE Offline bootstrap v1.0 - Minimal PWA + Login Offline (hashed) */
(function () {
    'use strict';

    const CDN = (window.SVE_CDN || {
        css: 'https://www.fernandosalguero.com/cdn/assets/css/framework.css',
        js: 'https://www.fernandosalguero.com/cdn/assets/javascript/framework.js',
    });

    // 1) Service Worker registration
    async function registerSW() {
        if (!('serviceWorker' in navigator)) return;
        try {
            const reg = await navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
            if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' });
            reg.addEventListener('updatefound', () => {
                const newSW = reg.installing;
                if (!newSW) return;
                newSW.addEventListener('statechange', () => {
                    if (newSW.state === 'installed' && navigator.serviceWorker.controller) {
                        console.log('[SVE] Nueva versión disponible (cache actualizada).');
                    }
                });
            });
            console.log('[SVE] Service Worker registrado');
        } catch (e) {
            console.warn('[SVE] SW error', e);
        }
    }

    // 2) Offline banner
    function ensureOfflineBanner() {
        let banner = document.getElementById('sve-offline-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'sve-offline-banner';
            banner.style.cssText = 'position:fixed;z-index:99999;left:0;right:0;top:0;padding:8px 12px;text-align:center;font-weight:600;display:none;backdrop-filter:saturate(180%) blur(6px);';
            banner.innerHTML = '<span>🔌 Estás sin conexión. Trabajando en modo offline.</span>';
            banner.style.background = '#fee2e2';
            banner.style.color = '#991b1b';
            document.body.appendChild(banner);
        }
        function update() {
            banner.style.display = navigator.onLine ? 'none' : 'block';
        }
        window.addEventListener('online', update);
        window.addEventListener('offline', update);
        update();
    }

    // 3) Credentials store (hashed) using WebCrypto
    function toBase64(arrayBuffer) {
        const bytes = new Uint8Array(arrayBuffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) binary += String.fromCharCode(bytes[i]);
        return btoa(binary);
    }

    function genSalt(len = 16) {
        const arr = new Uint8Array(len);
        crypto.getRandomValues(arr);
        return toBase64(arr.buffer);
    }

    async function hashCredential(username, password, saltB64) {
        if (!window.crypto || !window.crypto.subtle) throw new Error('WebCrypto no soportado');
        const enc = new TextEncoder();
        const passKey = await crypto.subtle.importKey(
            'raw',
            enc.encode(password),
            { name: 'PBKDF2' },
            false,
            ['deriveBits', 'deriveKey']
        );
        const saltData = enc.encode('sve:' + username + ':' + saltB64);
        const bits = await crypto.subtle.deriveBits(
            {
                name: 'PBKDF2',
                salt: saltData,
                iterations: 120000,
                hash: 'SHA-256'
            },
            passKey,
            256
        );
        return toBase64(bits);
    }

    async function saveOfflineCredential(username, password) {
        const salt = genSalt();
        const hash = await hashCredential(username, password, salt);
        const payload = {
            username,
            salt,
            hash,
            createdAt: Date.now()
        };
        localStorage.setItem('sve_offline_cred', JSON.stringify(payload));
        console.log('[SVE] Credencial offline guardada para', username);
    }

    async function verifyOfflineCredential(username, password) {
        const raw = localStorage.getItem('sve_offline_cred');
        if (!raw) return false;
        try {
            const { username: storedUser, salt, hash } = JSON.parse(raw);
            if (storedUser !== username) return false;
            const candidate = await hashCredential(username, password, salt);
            return candidate === hash;
        } catch (e) {
            console.warn('[SVE] Error verificando credencial offline', e);
            return false;
        }
    }

    // 4) Login enhancement: remember for offline + offline auth fallback
    function enhanceLogin() {
        const forms = Array.from(document.getElementsByTagName('form'));

        function findLoginForm() {
            const userSelectors = [
                'input[name*=user]',
                'input[name*=correo]',
                'input[name*=email]',
                'input[id*=user]',
                'input[id*=correo]',
                // compatibilidad SVE actual:
                'input[name*=usuario]',
                'input[id*=usuario]'
            ].join(',');

            const passSelectors = [
                'input[type=password]',
                'input[name*=pass]',
                'input[name*=clave]',
                'input[id*=pass]',
                // compatibilidad SVE actual:
                'input[name*=contrasena]',
                'input[id*=contrasena]'
            ].join(',');

            for (const f of forms) {
                const userEl = f.querySelector(userSelectors);
                const passEl = f.querySelector(passSelectors);
                if (userEl && passEl) return { form: f, userEl, passEl };
            }
            return null;
        }


        const found = findLoginForm();
        if (!found) return;

        const { form, userEl, passEl } = found;

        // Inject checkbox UI debajo del password
        const label = document.createElement('label');
        label.style.cssText = 'display:flex;align-items:center;gap:8px;margin-top:8px;';
        // label.innerHTML = '<input type="checkbox" id="sve-remember-offline" /> <span>Recordar</span>'; habilitar esta línea
        const pwdContainer = passEl.closest('.password-container') || passEl.parentElement || form;
        (pwdContainer.nextElementSibling)
            ? pwdContainer.parentElement.insertBefore(label, pwdContainer.nextElementSibling)
            : form.appendChild(label);


        form.addEventListener('submit', async (e) => {
            const username = (userEl.value || '').trim();
            const password = (passEl.value || '');
            const remember = /** @type {HTMLInputElement|null} */(document.getElementById('sve-remember-offline'))?.checked;

            if (!navigator.onLine) {
                e.preventDefault();
                try {
                    const ok = await verifyOfflineCredential(username, password);
                    if (ok) {
                        localStorage.setItem('sve_offline_session', JSON.stringify({ user: username, ts: Date.now() }));
                        window.location.href = '/views/drone_pilot/drone_pilot_dashboard.php?offline=1';
                    } else {
                        alert('No se pudo validar offline. Realiza al menos un inicio de sesión conectado para habilitar el acceso sin conexión.');
                    }
                } catch (err) {
                    alert('Tu navegador no soporta el cifrado necesario para el modo offline.');
                }
                return;
            }

            // Online: si pide recordar, guardamos hash antes de enviar (no interfiere con el submit)
            if (remember) {
                try { await saveOfflineCredential(username, password); } catch (e) { console.warn('[SVE] No se pudo guardar credencial offline', e); }
            }
        });
    }

    // 5) Guard for protected offline page (drone dashboard)
    function guardOfflineDashboard() {
        const isDashboard = window.location.pathname.endsWith('/views/drone_pilot/drone_pilot_dashboard.php');
        if (!isDashboard) return;
        if (navigator.onLine) return;

        const session = localStorage.getItem('sve_offline_session');
        if (!session) {
            window.location.replace('/views/sve/sve_registro_login.php?need_offline=1');
        }
    }

    // 6) Preload CDN
    function prefetchCDN() {
        try {
            const linkCss = document.createElement('link');
            linkCss.rel = 'preload';
            linkCss.as = 'style';
            linkCss.href = CDN.css;
            document.head.appendChild(linkCss);

            const linkJs = document.createElement('link');
            linkJs.rel = 'preload';
            linkJs.as = 'script';
            linkJs.href = CDN.js;
            document.head.appendChild(linkJs);
        } catch (e) { }
    }

    // --- 7) Botón oculto para limpiar caché + storage + SW ---
    function renderResetButton() {
        // Mostrar solo en login
        const isLogin =
            window.location.pathname === '/' ||
            window.location.pathname.endsWith('/index.php') ||
            window.location.pathname.endsWith('/views/sve/sve_registro_login.php');

        if (!isLogin) return;

        // Botón sutil (esquina inferior derecha)
        const btn = document.createElement('button');
        btn.id = 'sve-cache-reset-btn';
        btn.type = 'button';
        btn.title = 'Restablecer versión offline';
        btn.style.cssText = 'position:fixed;right:10px;bottom:10px;width:28px;height:28px;border-radius:9999px;border:0;background:#6b7280;color:#fff;font-size:16px;opacity:.35;cursor:pointer;z-index:99999;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.2)';
        btn.setAttribute('aria-label', 'Restablecer cache');
        btn.innerHTML = '↺';
        btn.addEventListener('mouseenter', () => (btn.style.opacity = '.85'));
        btn.addEventListener('mouseleave', () => (btn.style.opacity = '.35'));

        // Modal de confirmación
        const overlay = document.createElement('div');
        overlay.id = 'sve-cache-reset-overlay';
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;z-index:100000;';
        const modal = document.createElement('div');
        modal.style.cssText = 'position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);background:#fff;max-width:360px;width:92%;border-radius:12px;padding:16px;box-shadow:0 10px 30px rgba(0,0,0,.25);font-family:system-ui';
        modal.innerHTML = `
            <h3 style="margin:0 0 8px;font-size:16px;">¿Restablecer la versión offline?</h3>
            <p style="margin:0 0 12px;font-size:14px;line-height:1.4">
                Esto <strong>borra caches</strong>, <strong>storage</strong> y <strong>desregistra</strong> el Service Worker.
                Se recargará la página.
            </p>
            <div style="display:flex;gap:8px;justify-content:flex-end">
                <button id="sve-cancel" style="padding:6px 10px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;cursor:pointer">Cancelar</button>
                <button id="sve-confirm" style="padding:6px 10px;border-radius:8px;border:0;background:#7c3aed;color:#fff;cursor:pointer">Sí, borrar</button>
            </div>`;
        overlay.appendChild(modal);

        function openModal() { overlay.style.display = 'block'; }
        function closeModal() { overlay.style.display = 'none'; }

        btn.addEventListener('click', openModal);
        overlay.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
        setTimeout(() => {
            modal.querySelector('#sve-cancel').addEventListener('click', closeModal);
            modal.querySelector('#sve-confirm').addEventListener('click', async () => {
                closeModal();
                await SVE_ClearAll();
                location.reload();
            });
        });

        document.body.appendChild(btn);
        document.body.appendChild(overlay);
    }

    // Limpieza integral
    async function SVE_ClearAll() {
        try {
            // 1) caches
            const keys = await caches.keys();
            await Promise.all(keys.map(k => caches.delete(k)));

            // 2) local/session storage (solo claves SVE)
            try { localStorage.removeItem('sve_offline_cred'); } catch (e) { }
            try { localStorage.removeItem('sve_offline_session'); } catch (e) { }
            try { sessionStorage.clear(); } catch (e) { }

            // 3) IndexedDB (si el navegador lo permite)
            try {
                if (indexedDB && indexedDB.databases) {
                    const dbs = await indexedDB.databases();
                    await Promise.all(dbs.map(db => db.name && indexedDB.deleteDatabase(db.name)));
                }
            } catch (e) { /* opcional, no crítico */ }

            // 4) unregister service workers
            if ('serviceWorker' in navigator) {
                const regs = await navigator.serviceWorker.getRegistrations();
                await Promise.all(regs.map(r => r.unregister()));
            }
            console.log('[SVE] Limpieza completa ejecutada');
        } catch (e) {
            console.warn('[SVE] Error limpiando', e);
        }
    }

    // Boot
    window.addEventListener('load', registerSW);
    window.addEventListener('DOMContentLoaded', () => {
        ensureOfflineBanner();
        enhanceLogin();
        guardOfflineDashboard();
        prefetchCDN();
        renderResetButton(); // <— botón de reseteo
    });
    // ====== SVE Reset (expuesto global) ======
    window.SVE_ClearAll = async function () {
        try {
            // 1) borrar caches
            const keys = await caches.keys();
            await Promise.all(keys.map(k => caches.delete(k)));

            // 2) storages
            try { localStorage.removeItem('sve_offline_cred'); } catch (e) { }
            try { localStorage.removeItem('sve_offline_session'); } catch (e) { }
            try { sessionStorage.clear(); } catch (e) { }

            // 3) indexedDB (best effort)
            try {
                if (indexedDB && indexedDB.databases) {
                    const dbs = await indexedDB.databases();
                    await Promise.all(dbs.map(db => db.name && indexedDB.deleteDatabase(db.name)));
                }
            } catch (e) { }

            // 4) unregister SW
            if ('serviceWorker' in navigator) {
                const regs = await navigator.serviceWorker.getRegistrations();
                await Promise.all(regs.map(r => r.unregister()));
            }
            console.log('[SVE] Limpieza completa ejecutada');
        } catch (e) {
            console.warn('[SVE] Error limpiando', e);
        }
    };


})();

