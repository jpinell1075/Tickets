(function () {
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const existing = document.querySelector(`script[src="${src}"]`);
            if (existing) return resolve();

            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.body.appendChild(s);
        });
    }

    function waitForImages() {
        const imgs = Array.from(document.images || []);
        if (!imgs.length) return Promise.resolve();
        return Promise.all(imgs.map(img => {
            if (img.complete) return Promise.resolve();
            return new Promise(res => {
                img.addEventListener('load', res, { once: true });
                img.addEventListener('error', res, { once: true });
            });
        }));
    }

    function waitForFonts() {
        return document.fonts?.ready?.catch(() => {}) ?? Promise.resolve();
    }

    async function waitForPagesInDom(expectedTotal) {
        const root = document.querySelector('.pagedjs_pages');
        if (!root || !expectedTotal) return;

        let stable = 0;
        let lastCount = -1;

        while (stable < 8) {
            const count = root.querySelectorAll('.pagedjs_page').length;

            if (count >= expectedTotal && count === lastCount) {
                stable++;
            } else {
                stable = 0;
            }

            lastCount = count;
            await new Promise(r => setTimeout(r, 150));
        }
    }

    async function waitForPagedRendered() {
        if (!window.loadPagedJs) return;

        window.PagedConfig = {
            auto: false,
        };

        await loadScript('/js/exports/paged.polyfill.js');

        if (!window.PagedPolyfill?.preview) return;

        const flow = await window.PagedPolyfill.preview();
        await waitForPagesInDom(flow?.total);
    }

    function afterPrintAction() {
        if (window.openInNewTab) return window.close();
        window.history.back();
    }

    let started = false;
    async function start() {
        if (started) return;
        started = true;

        await waitForImages();
        console.log('Images loaded');
        await waitForFonts();
        console.log('Fonts loaded');
        await waitForPagedRendered();
        console.log('Paged rendered');

        await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));

        window.addEventListener('afterprint', afterPrintAction, { once: true });
        window.print();
    }

    document.addEventListener('DOMContentLoaded', start, { once: true });
    window.addEventListener('load', start, { once: true });
})();