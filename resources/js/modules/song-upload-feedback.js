const pdfSelected = input => [...(input.files ?? [])].some(file => file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf'));

const buildOverlay = message => {
    const overlay = document.createElement('div');
    overlay.className = 'song-upload-feedback';
    overlay.hidden = true;
    overlay.innerHTML = `<div class="song-upload-feedback__card" role="status" aria-live="polite"><div class="song-upload-feedback__spinner" aria-hidden="true"></div><strong>Convirtiendo PDF</strong><p>${message}</p><div class="song-upload-feedback__progress"><i></i></div></div>`;

    return overlay;
};

export function initSongUploadFeedback() {
    document.querySelectorAll('form[data-song-upload-form]').forEach(form => {
        const inputs = [...form.querySelectorAll('input[type="file"]')].filter(input => (input.accept || '').includes('application/pdf'));
        if (!inputs.length) return;

        const message = form.dataset.songUploadMessage || 'Esto puede tardar unos segundos mientras se generan las páginas para la presentación.';
        const overlay = buildOverlay(message);
        form.append(overlay);

        form.addEventListener('submit', () => {
            if (!inputs.some(pdfSelected)) return;

            form.classList.add('is-uploading');
            overlay.hidden = false;
            form.querySelectorAll('button, input, select, textarea, a').forEach(element => {
                if ('disabled' in element) {
                    element.disabled = true;
                }
            });
        });
    });
}