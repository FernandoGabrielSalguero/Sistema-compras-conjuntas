/*! SVE Offline bootstrap v2.1 - PWA + Login Offline (hashed + sesión local) */
(function () {
  'use strict';

  // ===== Helpers ============================================================
  const $ = (sel, root=document) => root.querySelector(sel);

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
    if (!window.crypto?.subtle) throw new Error('WebCrypto no soportado');
    const enc = new TextEncoder();
    const passKey = await crypto.subtle.importKey('raw', enc.encode(password), { name: 'PBKDF2' }, false, ['deriveBits','deriveKey']);
    const saltData = enc.encode('sve:' + username + ':' + saltB64);
    const bits = await crypto.subtle.deriveBits({ name:'PBKDF2', salt: saltData, iterations: 120000, hash:'SHA-256' }, passKey, 256);
    return toBase64(bits);
  }
  async function saveOfflineCredential(username, password) {
    const salt = genSalt();
    const hash = await hashCredential(username, password, salt);
    const payload = { username, salt, hash, createdAt: Date.now() };
    localStorage.setItem('sve_offline_cred', JSON.stringify(payload));
    console.log('[SVE] Credencial offline guardada para', username);
    return payload;
  }
  async function verifyOfflineCredential(username, password) {
    const raw = localStorage.getItem('sve_offline_cred');
    if (!raw) return false;
    try {
      const { username: storedUser, salt, hash } = JSON.parse(raw);
      if (storedUser !== username) return false;
      const candidate = await hashCredential(username, password, salt);
      return candidate === hash;
    } catch { return false; }
  }
  function createOfflineSession(username) {
    localStorage.setItem('sve_offline_session', JSON.stringify({ user: username, ts: Date.now() }));
  }
  const hasOfflineSession = () => !!localStorage.getItem('sve_offline_session');
  const hasOfflineCred    = () => !!localStorage.getItem('sve_offline_cred');

  // ===== Service Worker =====================================================
  async function registerSW() {
    if (!('serviceWorker' in navigator)) return;
    try {
      const reg = await navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
      if (reg.waiting) reg.waiting.postMessage({ type: 'SKIP_WAITING' });
      reg.addEventListener('updatefound', () => {
        const sw = reg.installing;
        if (!sw) return;
        sw.addEventListener('statechange', () => {
          if (sw.state === 'installed' && navigator.serviceWorker.controller) {
            console.log('[SVE] Cache actualizada');
          }
        });
      });
      console.log('[SVE] Service Worker registrado');
    } catch (e) {
      console.warn('[SVE] SW error', e);
    }
  }

  // ===== Banner offline simple =============================================
  function ensureOfflineBanner() {
    let banner = $('#sve-offline-banner');
    if (!banner) {
      banner = document.createElement('div');
      banner.id = 'sve-offline-banner';
      banner.style.cssText = 'position:fixed;z-index:99999;left:0;right:0;top:0;padding:8px 12px;text-align:center;font-weight:600;display:none;backdrop-filter:saturate(180%) blur(6px);background:#fee2e2;color:#991b1b';
      banner.textContent = '🔌 Estás sin conexión. Trabajando en modo offline.';
      document.body.appendChild(banner);
    }
    const update = () => { banner.style.display = navigator.onLine ? 'none' : 'block'; };
    window.addEventListener('online', update);
    window.addEventListener('offline', update);
    update();
  }

  // ===== Login (fallback offline + botón Activar) ===========================
  function findLoginForm() {
    const userSelectors = [
      'input[name*=user]','input[name*=correo]','input[name*=email]',
      'input[id*=user]','input[id*=correo]','input[name*=usuario]','input[id*=usuario]'
    ].join(',');
    const passSelectors = [
      'input[type=password]','input[name*=pass]','input[name*=clave]',
      'input[id*=pass]','input[name*=contrasena]','input[id*=contrasena]'
    ].join(',');
    for (const f of Array.from(document.getElementsByTagName('form'))) {
      const userEl = f.querySelector(userSelectors);
      const passEl = f.querySelector(passSelectors);
      if (userEl && passEl) return { form: f, userEl, passEl };
    }
    return null;
  }

  function enhanceLogin() {
    const found = findLoginForm();
    if (!found) return;
    const { form, userEl, passEl } = found;

    // 1) Fallback: submit sin red => validación local
    form.addEventListener('submit', async (e) => {
      if (navigator.onLine) return;
      e.preventDefault();
      const u = (userEl.value || '').trim();
      const p = (passEl.value || '');
      const ok = await verifyOfflineCredential(u, p);
      if (ok) {
        createOfflineSession(u);
        window.location.href = '/views/drone_pilot/drone_pilot_dashboard.php?offline=1';
      } else {
        alert('No se pudo validar offline. Activá el modo offline primero (⚡).');
      }
    });

    // 2) UI: Botones “⚡ activar offline” y “↺ reset”
    renderLoginButtons({ form, userEl, passEl });
  }

  function renderLoginButtons({ form, userEl, passEl }) {
    const isLogin =
      location.pathname === '/' ||
      location.pathname.endsWith('/index.php') ||
      location.pathname.endsWith('/views/sve/sve_registro_login.php');
    if (!isLogin) return;

    // a) buscar botón submit para anclar
    const submitBtn = form.querySelector('button[type=submit], input[type=submit]');
    // b) buscar si ya existe un botón reset previo
    const existingReset = $('#sve-cache-reset-btn');

    // Crear botón ⚡
    const btnOffline = document.createElement('button');
    btnOffline.type = 'button';
    btnOffline.id   = 'sve-offline-enable';
    btnOffline.textContent = '⚡';
    styleIconBtn(btnOffline);

    const setActive = (on) => {
      btnOffline.style.background = on ? '#16a34a' : '#6b7280'; // verde activo
      btnOffline.title = on ? 'Offline activado' : 'Activar acceso sin conexión';
      btnOffline.style.opacity = on ? '1' : '.9';
    };
    setActive(hasOfflineCred() && hasOfflineSession());

    btnOffline.addEventListener('click', async () => {
      const u = (userEl.value || '').trim();
      const p = (passEl.value || '');
      if (!u || !p) {
        alert('Ingresá usuario y contraseña para activar el modo offline.');
        return;
      }
      try {
        await saveOfflineCredential(u, p);
        createOfflineSession(u);
        localStorage.setItem('sve_offline_onboarded', JSON.stringify({ user: u, ts: Date.now() }));
        console.log('[SVE] Credencial offline activada para', u);
        setActive(true);
      } catch (e) {
        console.warn('[SVE] No se pudo activar el modo offline', e);
        alert('No se pudo activar el modo offline en este navegador.');
      }
    });

    // Colocación: junto al reset si existe, si no, al lado del submit
    if (existingReset && existingReset.parentElement) {
      existingReset.parentElement.insertBefore(btnOffline, existingReset);
    } else if (submitBtn && submitBtn.parentElement) {
      submitBtn.parentElement.style.display = 'flex';
      submitBtn.parentElement.style.gap = '8px';
      submitBtn.parentElement.appendChild(btnOffline);
      // si no hay reset, agrego uno pequeño también
      const btnReset = document.createElement('button');
      btnReset.type = 'button';
      btnReset.id   = 'sve-cache-reset-btn';
      btnReset.textContent = '↺';
      styleIconBtn(btnReset);
      btnReset.title = 'Restablecer versión offline';
      btnReset.addEventListener('click', async () => { await SVE_ClearAll(); location.reload(); });
      submitBtn.parentElement.appendChild(btnReset);
    } else {
      // fallback absoluto si el DOM es raro
      const wrap = document.createElement('div');
      wrap.style.cssText = 'position:fixed;right:12px;bottom:12px;display:flex;gap:8px;z-index:99999';
      wrap.appendChild(btnOffline);
      document.body.appendChild(wrap);
    }
  }

  function styleIconBtn(btn) {
    btn.style.cssText = [
      'width:32px;height:32px;border-radius:9999px;border:0',
      'background:#6b7280;color:#fff;font-size:16px',
      'cursor:pointer;display:inline-flex;align-items:center;justify-content:center',
      'box-shadow:0 2px 8px rgba(0,0,0,.2)'
    ].join(';');
  }

  // ===== Guard para dashboard offline ======================================
  function guardOfflineDashboard() {
    const isDashboard = location.pathname.endsWith('/views/drone_pilot/drone_pilot_dashboard.php');
    if (!isDashboard || navigator.onLine) return;
    if (!hasOfflineSession()) {
      location.replace('/views/sve/sve_registro_login.php?need_offline=1');
    }
  }

  // ===== Reset global expuesto (sin modal, sin dashboard) ===================
  window.SVE_ClearAll = async function () {
    try {
      const keys = await caches.keys(); await Promise.all(keys.map(k => caches.delete(k)));
      try { localStorage.removeItem('sve_offline_cred'); } catch {}
      try { localStorage.removeItem('sve_offline_session'); } catch {}
      try { sessionStorage.clear(); } catch {}
      try {
        if (indexedDB?.databases) {
          const dbs = await indexedDB.databases();
          await Promise.all(dbs.map(db => db.name && indexedDB.deleteDatabase(db.name)));
        }
      } catch {}
      if ('serviceWorker' in navigator) {
        const regs = await navigator.serviceWorker.getRegistrations();
        await Promise.all(regs.map(r => r.unregister()));
      }
      console.log('[SVE] Limpieza completa ejecutada');
    } catch (e) { console.warn('[SVE] Error limpiando', e); }
  };

  // ===== Boot ===============================================================
  window.addEventListener('load', registerSW);
  window.addEventListener('DOMContentLoaded', () => {
    ensureOfflineBanner();
    enhanceLogin();
    guardOfflineDashboard();
  });

  setTimeout(() => console.log('[SVE] offline.js listo'), 0);
})();
