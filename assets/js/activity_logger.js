// assets/js/activity_logger.js
(function () {
    const send = (payload) => {
        try {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            if ('sendBeacon' in navigator) {
                navigator.sendBeacon('/logger.php', blob);
            } else {
                fetch('/logger.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            }
        } catch (_) { }
    };

    document.addEventListener('click', (e) => {
        const el = e.target.closest('button, a, [data-audit]');
        if (!el) return;
        send({
            event: 'click',
            element: el.tagName.toLowerCase(),
            details: {
                id: el.id || null, name: el.name || null,
                text: (el.innerText || el.textContent || '').trim().slice(0, 200),
                href: el.tagName === 'A' ? el.getAttribute('href') : null,
                classes: el.className || null,
                dataset: el.dataset || {},
                path: location.pathname + location.search
            }
        });
    }, { capture: true });

    window.addEventListener('error', (ev) => {
        send({
            event: 'js_error', element: 'window',
            details: { message: ev.message, file: ev.filename, line: ev.lineno, col: ev.colno, path: location.pathname + location.search }
        });
    });
    window.addEventListener('unhandledrejection', (ev) => {
        send({
            event: 'js_unhandledrejection', element: 'window',
            details: { reason: (ev.reason && (ev.reason.message || ev.reason.toString())) || 'unhandledrejection', path: location.pathname + location.search }
        });
    });
})();
