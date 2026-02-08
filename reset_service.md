  1. Ver caches:
  await caches.keys()

  2. Ver Service Worker:
  (await navigator.serviceWorker.getRegistrations())[0]?.active?.scriptURL

  3. Ver si offlineSync existe:
  window.offlineSync

  4. Ver sesión offline:
  window.offlineSync?.getOfflineSession()

  5. Ver object stores disponibles
  Array.from(window.offlineSync.db.objectStoreNames)