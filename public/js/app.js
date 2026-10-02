document.addEventListener('DOMContentLoaded', () => {
    // Humeur du jour : choix unique
    document.querySelectorAll('[data-chips]').forEach(group => {
        group.addEventListener('click', e => {
            const chip = e.target.closest('.chip');
            if (!chip) return;
            group.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === chip && !chip.classList.contains('active')));
        });
    });

    // Confirmation avant action sensible (ex. annuler un RDV)
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => { if (!confirm(form.dataset.confirm)) e.preventDefault(); });
    });

    // Conseils selon les symptômes
    document.querySelectorAll('[data-tips]').forEach(group => {
        group.addEventListener('click', e => {
            const chip = e.target.closest('.chip');
            if (!chip) return;
            group.querySelectorAll('.chip').forEach(c => c.classList.toggle('active', c === chip));
            document.querySelectorAll('.tip').forEach(t => { t.hidden = t.id !== chip.dataset.target; });
        });
    });

    // Prise de rendez-vous : jours -> créneaux
    const form = document.getElementById('booking');
    if (!form) return;

    const days = [...form.querySelectorAll('.day')];
    const box = form.querySelector('#slots');
    const dateInput = form.querySelector('[name=date]');
    const timeInput = form.querySelector('[name=time]');
    const submit = form.querySelector('[type=submit]');
    const label = form.querySelector('#slots-label');

    const pick = (button, time) => {
        box.querySelectorAll('.slot').forEach(s => s.classList.toggle('active', s === button));
        timeInput.value = time;
        submit.disabled = false;
    };

    const render = slots => {
        box.innerHTML = '';
        if (!slots.length) { box.innerHTML = '<p class="muted">Plus de créneau ce jour-là. Choisissez une autre date.</p>'; return; }
        slots.forEach(time => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'slot';
            b.innerHTML = `<strong>${time}</strong><small>${parseInt(time, 10) < 12 ? 'Matin' : 'Après-midi'}</small>`;
            b.addEventListener('click', () => pick(b, time));
            box.append(b);
        });
    };

    const load = async day => {
        days.forEach(d => d.classList.toggle('active', d === day));
        dateInput.value = day.dataset.date;
        timeInput.value = '';
        submit.disabled = true;
        label.textContent = day.dataset.label;
        box.innerHTML = '<p class="muted">Chargement…</p>';
        try {
            const res = await fetch(`${form.dataset.slotsUrl}?date=${day.dataset.date}`, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error();
            render((await res.json()).slots);
        } catch {
            box.innerHTML = '<p class="muted">Impossible de charger les horaires. Réessayez.</p>';
        }
    };

    days.forEach(day => day.addEventListener('click', () => load(day)));
    const initial = days.find(d => d.dataset.date === dateInput.value && !d.disabled) || days.find(d => !d.disabled);
    if (initial) load(initial);
});

/* ===== PWA : bouton « Installer » et service worker ===== */
(() => {
    let deferred = null;
    const buttons = () => document.querySelectorAll('[data-install]');
    const standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone;
    const ios = /iphone|ipad|ipod/i.test(navigator.userAgent);

    window.addEventListener('beforeinstallprompt', e => {
        e.preventDefault();
        deferred = e;
        buttons().forEach(b => (b.hidden = false));
    });
    window.addEventListener('appinstalled', () => {
        deferred = null;
        buttons().forEach(b => (b.hidden = true));
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (ios && !standalone) buttons().forEach(b => (b.hidden = false));
        buttons().forEach(b => b.addEventListener('click', async () => {
            if (deferred) {
                deferred.prompt();
                await deferred.userChoice;
                deferred = null;
                buttons().forEach(x => (x.hidden = true));
            } else {
                alert("Sur iPhone : touchez le bouton Partager, puis « Sur l'écran d'accueil ».");
            }
        }));
    });

    const secure = location.protocol === 'https:' || ['localhost', '127.0.0.1'].includes(location.hostname);
    if ('serviceWorker' in navigator && secure) {
        window.addEventListener('load', () => {
            const manifest = document.querySelector('link[rel=manifest]');
            if (manifest) navigator.serviceWorker.register(manifest.href.replace('manifest.webmanifest', 'sw.js')).catch(() => {});
        });
    }
})();
