export function initRepertoireSelector() {
    const selector = document.querySelector('[data-song-selector]');
    if (!selector) return;

    const checks = [...selector.querySelectorAll('input[name="song_ids[]"]')];
    const count = selector.querySelector('[data-selected-count]');
    const submit = selector.querySelector('[data-add-selected]');
    const search = selector.querySelector('[name="song_q"]');
    const clear = selector.querySelector('[data-clear-search]');

    const update = () => {
        const selected = checks.filter(check => check.checked).length;
        count.textContent = selected === 1 ? '1 canción seleccionada' : `${selected} canciones seleccionadas`;
        submit.disabled = selected === 0;
    };

    checks.forEach(check => check.addEventListener('change', update));
    clear?.addEventListener('click', () => {
        search.value = '';
        selector.querySelectorAll('select').forEach(select => { select.value = ''; });
        search.focus();
    });
    update();
}
