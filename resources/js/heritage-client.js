function applyHeritageBootstrap() {
    const boot = window.HERITAGE_BOOTSTRAP || {};

    window.TRIBE_IMAGES = boot.tribeImages || {};
    window.TRIBES = boot.tribes || [];
    window.__heritageState = Object.assign({ stars: 0, done: {}, tStars: {} }, boot.progress || {});
}

applyHeritageBootstrap();

window.__heritageSaveProgress = function (state) {
    const boot = window.HERITAGE_BOOTSTRAP || {};
    const url = boot.routes?.progress;

    if (!url) {
        return;
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN':
                boot.csrfToken ||
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
        body: JSON.stringify(state),
        credentials: 'same-origin',
    }).catch(() => {});
};

import './heritage-engine-tail.js';

function revealHeritageApp() {
    applyHeritageBootstrap();

    const appPage = document.getElementById('appPage');
    if (appPage) {
        appPage.style.display = 'block';
    }

    if (typeof window.__heritageBootApp === 'function') {
        window.__heritageBootApp();
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', revealHeritageApp);
} else {
    revealHeritageApp();
}
