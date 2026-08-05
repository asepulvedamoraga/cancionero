const root = document.querySelector('[data-presentation]');

if (root) {
    const slides = [...root.querySelectorAll('[data-slide]')];
    const viewport = root.querySelector('[data-viewport]');
    const indexPanel = root.querySelector('[data-index]');
    let current = 0;
    let zoom = 1;
    let pointerStart = null;
    let controlsTimer = null;

    const text = (selector, value) => {
        const element = root.querySelector(selector);
        if (element) element.textContent = value;
    };

    const resetControlsTimer = () => {
        root.classList.remove('controls-hidden');
        window.clearTimeout(controlsTimer);
        controlsTimer = window.setTimeout(() => {
            if (!indexPanel || indexPanel.hidden) root.classList.add('controls-hidden');
        }, 3500);
    };

    const applyZoom = () => {
        const content = slides[current]?.querySelector('[data-slide-content]');
        if (content) content.style.transform = `scale(${zoom})`;
        text('[data-zoom-reset]', `${Math.round(zoom * 100)}%`);
    };

    const show = index => {
        if (!slides.length) return;
        current = Math.max(0, Math.min(index, slides.length - 1));
        slides.forEach((slide, position) => { slide.hidden = position !== current; });
        const slide = slides[current];
        applyZoom();
        text('[data-song-title]', slide.dataset.songTitle);
        text('[data-song-counter]', `Canción ${slide.dataset.songPosition} de ${slide.dataset.songCount}`);
        text('[data-page-counter]', `Página ${slide.dataset.globalPosition} de ${slide.dataset.totalPages}`);
        text('[data-song-page-counter]', `Página ${slide.dataset.pagePosition} de ${slide.dataset.songPageCount}`);
        const progress = root.querySelector('[data-total-progress] i');
        if (progress) progress.style.width = `${(Number(slide.dataset.globalPosition) / Number(slide.dataset.totalPages)) * 100}%`;
        root.querySelectorAll('[data-go-to]').forEach(button => button.classList.toggle('active', Number(button.dataset.goTo) === current));
        resetControlsTimer();
    };

    const next = () => show(current + 1);
    const previous = () => show(current - 1);
    root.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', next));
    root.querySelectorAll('[data-previous]').forEach(button => button.addEventListener('click', previous));
    root.querySelector('[data-zoom-in]')?.addEventListener('click', () => { zoom = Math.min(2.5, zoom + .15); applyZoom(); resetControlsTimer(); });
    root.querySelector('[data-zoom-out]')?.addEventListener('click', () => { zoom = Math.max(.7, zoom - .15); applyZoom(); resetControlsTimer(); });
    root.querySelector('[data-zoom-reset]')?.addEventListener('click', () => { zoom = 1; applyZoom(); resetControlsTimer(); });
    root.querySelector('[data-fullscreen]')?.addEventListener('click', async () => {
        if (document.fullscreenElement) await document.exitFullscreen();
        else await document.documentElement.requestFullscreen();
        resetControlsTimer();
    });

    const toggleIndex = force => {
        if (!indexPanel) return;
        indexPanel.hidden = typeof force === 'boolean' ? !force : !indexPanel.hidden;
        root.classList.toggle('index-open', !indexPanel.hidden);
        resetControlsTimer();
    };
    root.querySelector('[data-index-toggle]')?.addEventListener('click', () => toggleIndex());
    root.querySelector('[data-index-close]')?.addEventListener('click', () => toggleIndex(false));
    root.querySelectorAll('[data-go-to]').forEach(button => button.addEventListener('click', () => { show(Number(button.dataset.goTo)); toggleIndex(false); }));

    viewport?.addEventListener('pointerdown', event => {
        if (event.target.closest('button,a,iframe')) return;
        pointerStart = { x: event.clientX, y: event.clientY };
    });
    viewport?.addEventListener('pointerup', event => {
        if (!pointerStart) return;
        const deltaX = event.clientX - pointerStart.x;
        const deltaY = event.clientY - pointerStart.y;
        pointerStart = null;
        if (Math.abs(deltaX) > 55 && Math.abs(deltaX) > Math.abs(deltaY)) deltaX < 0 ? next() : previous();
        else resetControlsTimer();
    });

    document.addEventListener('keydown', event => {
        if (['ArrowRight', 'PageDown', ' '].includes(event.key)) { event.preventDefault(); next(); }
        if (['ArrowLeft', 'PageUp'].includes(event.key)) { event.preventDefault(); previous(); }
        if (event.key === 'Home') show(0);
        if (event.key === 'End') show(slides.length - 1);
        if (event.key === '+' || event.key === '=') { zoom = Math.min(2.5, zoom + .15); applyZoom(); }
        if (event.key === '-') { zoom = Math.max(.7, zoom - .15); applyZoom(); }
        if (event.key === '0') { zoom = 1; applyZoom(); }
        if (event.key.toLowerCase() === 'f') root.querySelector('[data-fullscreen]')?.click();
        if (event.key === 'Escape' && indexPanel && !indexPanel.hidden) toggleIndex(false);
        resetControlsTimer();
    });

    root.addEventListener('pointermove', resetControlsTimer);
    show(0);
}
