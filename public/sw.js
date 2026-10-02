// Service worker — Clinique Saint-Luc
// Choix de sécurité : les pages (qui contiennent des données de santé) ne sont JAMAIS mises en cache.
// Seuls les fichiers statiques (CSS, JS, images) et la page hors connexion le sont.
const CACHE = 'saint-luc-static-v5';
const BASE = self.registration.scope;
const PRECACHE = ['offline.html', 'css/app.css', 'js/app.js', 'img/icon-192.png', 'img/doctor.jpg'];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE).then(c => c.addAll(PRECACHE.map(p => BASE + p))).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(keys => Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const req = event.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);
    if (url.origin !== location.origin) return;

    // Pages : réseau uniquement, page « hors connexion » si le réseau est coupé
    if (req.mode === 'navigate') {
        event.respondWith(fetch(req).catch(() => caches.match(BASE + 'offline.html')));
        return;
    }

    // Fichiers statiques : cache d'abord, mise à jour en arrière-plan
    if (/\.(css|js|png|jpg|svg|webmanifest)$/.test(url.pathname)) {
        event.respondWith(
            caches.open(CACHE).then(async cache => {
                const hit = await cache.match(req);
                const net = fetch(req).then(res => { if (res.ok) cache.put(req, res.clone()); return res; }).catch(() => hit);
                return hit || net;
            })
        );
    }
});
