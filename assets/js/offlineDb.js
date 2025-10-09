// IndexedDB minimalista sin dependencias
(() => {
    const DB_NAME = 'sve-offline';
    const DB_VER = 1;

    const STORES = {
        httpCache: { keyPath: 'key' },        // { key: url, body: string, headers: object, ts:number }
        outbox: { keyPath: 'id', autoIncrement: true }, // { id, url, method, headers, bodyType, json, params, form, files[], createdAt, tries }
        meta: { keyPath: 'key' }         // { key:'lastSyncAt', value: number }
    };

    function openDb() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VER);
            req.onupgradeneeded = (ev) => {
                const db = ev.target.result;
                Object.entries(STORES).forEach(([name, opts]) => {
                    if (!db.objectStoreNames.contains(name)) {
                        const store = db.createObjectStore(name, { keyPath: opts.keyPath, autoIncrement: !!opts.autoIncrement });
                        if (name === 'httpCache') store.createIndex('ts', 'ts');
                    }
                });
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    async function withStore(name, mode, fn) {
        const db = await openDb();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(name, mode);
            const store = tx.objectStore(name);
            Promise.resolve(fn(store)).then((res) => {
                tx.oncomplete = () => resolve(res);
                tx.onerror = () => reject(tx.error);
            }).catch(reject);
        });
    }

    const OfflineDB = {
        async cachePut(key, response) {
            const headers = {};
            response.headers.forEach((v, k) => { headers[k] = v; });
            const body = await response.clone().text();
            const rec = { key, body, headers, ts: Date.now() };
            return withStore('httpCache', 'readwrite', s => s.put(rec));
        },
        async cacheGet(key) {
            const rec = await withStore('httpCache', 'readonly', s => s.get(key));
            if (!rec) return null;
            return new Response(rec.body, { headers: rec.headers });
        },
        async outboxAdd(entry) {
            entry.createdAt = Date.now();
            entry.tries = entry.tries || 0;
            return withStore('outbox', 'readwrite', s => s.add(entry));
        },
        async outboxAll() {
            return withStore('outbox', 'readonly', s => s.getAll());
        },
        async outboxDelete(id) {
            return withStore('outbox', 'readwrite', s => s.delete(id));
        },
        async metaGet(key) {
            const rec = await withStore('meta', 'readonly', s => s.get(key));
            return rec ? rec.value : null;
        },
        async metaSet(key, value) {
            return withStore('meta', 'readwrite', s => s.put({ key, value }));
        }
    };

    window.OfflineDB = OfflineDB;
})();
