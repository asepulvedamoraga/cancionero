const debounce = (callback, delay = 350) => {
    let timeoutId;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), delay);
    };
};

export function initRepertoireSelector() {
    const selector = document.querySelector('[data-song-selector]');
    if (!selector) {
        return;
    }

    const searchForm = selector.querySelector('[data-selector-search-form]');
    const submitForm = selector.querySelector('[data-selector-submit-form]');
    const resultsWrapper = selector.querySelector('[data-selector-results]');
    const selectedCount = selector.querySelector('[data-selected-count]');
    const submitButton = selector.querySelector('[data-add-selected]');
    const clearButton = selector.querySelector('[data-clear-search]');
    const hiddenInputs = selector.querySelector('[data-selector-hidden-inputs]');
    const selectedChips = selector.querySelector('[data-selected-songs-chips]');
    const searchField = selector.querySelector('[name="song_q"]');
    const searchUrl = selector.dataset.searchUrl || searchForm?.action;

    if (!searchForm || !submitForm || !resultsWrapper || !selectedCount || !submitButton || !hiddenInputs || !selectedChips || !searchUrl) {
        return;
    }

    const selectedSongs = new Map();
    let activeRequest = null;

    const renderSelectedSummary = () => {
        const total = selectedSongs.size;
        selectedCount.textContent = total === 1 ? '1 canción seleccionada' : `${total} canciones seleccionadas`;
        submitButton.disabled = total === 0;

        if (total === 0) {
            selectedChips.innerHTML = '<span class="song-selector-picked__empty">Aún no has seleccionado canciones.</span>';
            return;
        }

        selectedChips.innerHTML = Array.from(selectedSongs.entries())
            .map(([songId, song]) => `<button type="button" class="song-selector-chip" data-remove-song-id="${songId}"><span>${song.title}</span><i class="bi bi-x"></i></button>`)
            .join('');

        selectedChips.querySelectorAll('[data-remove-song-id]').forEach(button => {
            button.addEventListener('click', () => {
                const songId = button.getAttribute('data-remove-song-id');
                if (!songId) {
                    return;
                }

                selectedSongs.delete(songId);
                const visible = resultsWrapper.querySelector(`[data-song-option][data-song-id="${songId}"]`);
                const visibleCheck = visible?.querySelector('[data-song-check]');
                if (visibleCheck) {
                    visibleCheck.checked = false;
                }
                renderHiddenInputs();
                renderSelectedSummary();
            });
        });
    };

    const renderHiddenInputs = () => {
        hiddenInputs.innerHTML = '';

        selectedSongs.forEach((song, songId) => {
            const songInput = document.createElement('input');
            songInput.type = 'hidden';
            songInput.name = 'song_ids[]';
            songInput.value = songId;

            const toneInput = document.createElement('input');
            toneInput.type = 'hidden';
            toneInput.name = `song_tones[${songId}]`;
            toneInput.value = song.toneId || '';

            hiddenInputs.append(songInput, toneInput);
        });
    };

    const syncVisibleState = () => {
        resultsWrapper.querySelectorAll('[data-song-option]').forEach(option => {
            const songId = option.getAttribute('data-song-id');
            if (!songId) {
                return;
            }

            const check = option.querySelector('[data-song-check]');
            const tone = option.querySelector('[data-song-tone]');
            const title = option.getAttribute('data-song-title') || 'Canción';

            if (!check || !tone) {
                return;
            }

            if (selectedSongs.has(songId)) {
                check.checked = true;
                const selected = selectedSongs.get(songId);
                if (selected?.toneId) {
                    tone.value = selected.toneId;
                }
            }

            check.addEventListener('change', () => {
                if (check.checked) {
                    selectedSongs.set(songId, {
                        title,
                        toneId: tone.value || '',
                    });
                } else {
                    selectedSongs.delete(songId);
                }

                renderHiddenInputs();
                renderSelectedSummary();
            });

            tone.addEventListener('change', () => {
                if (!check.checked) {
                    return;
                }

                selectedSongs.set(songId, {
                    title,
                    toneId: tone.value || '',
                });
                renderHiddenInputs();
            });
        });
    };

    const setLoading = isLoading => {
        resultsWrapper.classList.toggle('is-loading', isLoading);
        resultsWrapper.setAttribute('aria-busy', isLoading ? 'true' : 'false');

        resultsWrapper.querySelectorAll('[data-selector-loading]').forEach(node => {
            node.hidden = !isLoading;
        });
    };

    const fetchResults = async url => {
        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();
        setLoading(true);

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const html = await response.text();
            resultsWrapper.innerHTML = html;
            syncVisibleState();
            renderHiddenInputs();
            renderSelectedSummary();
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            setLoading(false);
        }
    };

    const refreshWithFormData = () => {
        const params = new URLSearchParams(new FormData(searchForm));
        params.set('selector_partial', '1');
        fetchResults(`${searchUrl}?${params.toString()}`);
    };

    const debouncedRefresh = debounce(refreshWithFormData, 380);

    searchForm.addEventListener('submit', event => {
        event.preventDefault();
        refreshWithFormData();
    });

    searchForm.addEventListener('input', event => {
        if (!(event.target instanceof HTMLInputElement || event.target instanceof HTMLSelectElement)) {
            return;
        }

        debouncedRefresh();
    });

    searchForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', refreshWithFormData);
    });

    clearButton?.addEventListener('click', () => {
        searchForm.reset();
        if (searchField) {
            searchField.focus();
        }
        refreshWithFormData();
    });

    resultsWrapper.addEventListener('click', event => {
        const link = event.target.closest('[data-selector-pagination] a, .pagination a');
        if (!link) {
            return;
        }

        event.preventDefault();
        const url = new URL(link.href);
        url.searchParams.set('selector_partial', '1');
        fetchResults(url.toString());
    });

    submitForm.addEventListener('submit', () => {
        renderHiddenInputs();
    });

    syncVisibleState();
    renderHiddenInputs();
    renderSelectedSummary();
}
