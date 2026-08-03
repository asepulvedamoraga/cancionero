async function saveOrder(list, originalOrder, status) {
    status.textContent = 'Guardando orden...';
    status.className = 'small mt-3 text-secondary';
    try {
        const songs = [...list.querySelectorAll('.repertoire-song-item')].map(item => Number(item.dataset.songId));
        const response = await fetch(list.dataset.reorderUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ songs }),
        });
        if (!response.ok) throw new Error('No fue posible guardar el orden.');
        status.textContent = 'Orden guardado.';
        status.className = 'small mt-3 text-success';
    } catch (error) {
        originalOrder.forEach(item => list.appendChild(item));
        status.textContent = error.message;
        status.className = 'small mt-3 text-danger';
    }
}

export function initRepertoireSorter() {
    const list = document.querySelector('[data-repertoire-songs]');
    if (!list) return;
    const status = document.querySelector('[data-repertoire-sort-status]');
    let active = null;
    let moved = false;
    let originalOrder = [];

    list.querySelectorAll('.repertoire-drag-handle').forEach(handle => handle.addEventListener('pointerdown', event => {
        event.preventDefault();
        active = handle.closest('.repertoire-song-item');
        moved = false;
        originalOrder = [...list.querySelectorAll('.repertoire-song-item')];
        active.classList.add('sorting-active');
        handle.setPointerCapture(event.pointerId);
    }));

    list.addEventListener('pointermove', event => {
        if (!active) return;
        const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('.repertoire-song-item');
        if (!target || target === active || target.parentElement !== list) return;
        const box = target.getBoundingClientRect();
        list.insertBefore(active, event.clientY < box.top + box.height / 2 ? target : target.nextSibling);
        moved = true;
    });

    const finish = () => {
        if (!active) return;
        active.classList.remove('sorting-active');
        active = null;
        if (moved) saveOrder(list, originalOrder, status);
    };
    list.addEventListener('pointerup', finish);
    list.addEventListener('pointercancel', finish);
}
