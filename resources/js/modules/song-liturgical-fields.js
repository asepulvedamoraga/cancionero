const parseJsonArray = value => {
	if (!value) {
		return [];
	}

	try {
		const parsed = JSON.parse(value);

		return Array.isArray(parsed) ? parsed : [];
	} catch {
		return [];
	}
};

const normalizeSlug = value => String(value || '').trim().toLowerCase();
const normalizeId = value => String(value || '').trim();
const defaultLiturgicalSlugs = ['musica-liturgica', 'musica-religiosa'];
const defaultLiturgicalIds = ['1', '2'];

const shouldShowLiturgicalFields = (select, allowedSlugs, allowedIds) => {
	const selectedOption = select.options[select.selectedIndex];
	const selectedSlug = normalizeSlug(selectedOption?.dataset?.categorySlug);

	if (selectedSlug && allowedSlugs.has(selectedSlug)) {
		return true;
	}

	const selectedId = normalizeId(select.value);
	if (selectedId && allowedIds.has(selectedId)) {
		return true;
	}

	return false;
};

export const initSongLiturgicalFields = () => {
	document.querySelectorAll('[data-song-liturgical-fields]').forEach(container => {
		const categorySelect =
			container.querySelector('[data-song-category-select]') ||
			container.closest('.app-form-section')?.querySelector('[data-song-category-select]') ||
			container.closest('form')?.querySelector('[data-song-category-select]');
		const liturgicalBlocks = Array.from(container.querySelectorAll('[data-song-liturgical-field]'));

		if (!categorySelect || liturgicalBlocks.length === 0) {
			return;
		}

		const parsedSlugs = parseJsonArray(container.dataset.liturgicalCategorySlugs).map(normalizeSlug).filter(Boolean);
		const parsedIds = parseJsonArray(container.dataset.liturgicalCategoryIds).map(normalizeId).filter(Boolean);
		const allowedSlugs = new Set(parsedSlugs.length > 0 ? parsedSlugs : defaultLiturgicalSlugs);
		const allowedIds = new Set(parsedIds.length > 0 ? parsedIds : defaultLiturgicalIds);

		const syncVisibility = () => {
			const visible = shouldShowLiturgicalFields(categorySelect, allowedSlugs, allowedIds);

			liturgicalBlocks.forEach(block => {
				block.classList.toggle('d-none', !visible);
				block.setAttribute('aria-hidden', visible ? 'false' : 'true');
			});
		};

		syncVisibility();
		categorySelect.addEventListener('change', syncVisibility);
	});
};
