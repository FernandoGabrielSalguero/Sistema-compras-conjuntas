(function () {
  const PING_EVERY_MS = 5 * 60 * 1000; // 5 minutos
  function ping() {
    if (document.hidden) return;
    fetch('/ping.php', { method: 'POST', credentials: 'same-origin' }).catch(() => {});
  }
  setInterval(ping, PING_EVERY_MS);
})();



{/*
    <script src="/views/partials/session-keepalive.js"></script>
*/}