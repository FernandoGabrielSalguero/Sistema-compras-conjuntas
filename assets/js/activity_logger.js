// assets/js/activity_logger.js
(function () {
    const send = (payload) => {
        try {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            // /logger.php es manejado por request_logger.php (204 No Content)
            navigator.sendBeacon('/logger.php', blob);
        } catch (_) { }
    };

    // Delegación global de clics en botones, links y elementos con data-audit
    document.addEventListener('click', (e) => {
        const el = e.target.closest('button, a, [data-audit]');
        if (!el) return;
        const details = {
            id: el.id || null,
            name: el.name || null,
            role: el.getAttribute('role') || null,
            text: (el.innerText || el.textContent || '').trim().slice(0, 200),
            href: el.tagName === 'A' ? el.getAttribute('href') : null,
            classes: el.className || null,
            dataset: el.dataset || {},
            path: location.pathname + location.search
        };
        send({ event: 'click', element: el.tagName.toLowerCase(), details });
    }, { capture: true });

    // Errores JS de frontend
    window.addEventListener('error', (ev) => {
        const d = {
            message: ev.message,
            file: ev.filename,
            line: ev.lineno,
            col: ev.colno,
            path: location.pathname + location.search
        };
        send({ event: 'js_error', element: 'window', details: d });
    });

    window.addEventListener('unhandledrejection', (ev) => {
        const d = {
            reason: (ev.reason && (ev.reason.message || ev.reason.toString())) || 'unhandledrejection',
            path: location.pathname + location.search
        };
        send({ event: 'js_unhandledrejection', element: 'window', details: d });
    });
})();
