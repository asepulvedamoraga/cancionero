import './bootstrap';
import { initFilePreview } from './modules/file-preview';
import { initCopyButtons } from './modules/copy-buttons';
import { initSongFileSorter } from './modules/song-files-sorter';
import { initSongDuplicateCheck } from './modules/song-duplicate-check';
import { initRepertoireSorter } from './modules/repertoire-sorter';
import { initRepertoireSelector } from './modules/repertoire-selector';
import { initSongUploadFeedback } from './modules/song-upload-feedback';

const initPublicHeader = () => {
	const header = document.querySelector('[data-public-header]');
	if (!header) {
		return;
	}

	const syncHeaderState = () => {
		const isScrolled = window.scrollY > 12;
		header.classList.toggle('is-scrolled', isScrolled);
	};

	syncHeaderState();
	window.addEventListener('scroll', syncHeaderState, { passive: true });
};

document.documentElement.classList.add('js');
document.addEventListener('DOMContentLoaded', () => {
	initFilePreview();
	initCopyButtons();
	initSongFileSorter();
	initSongDuplicateCheck();
	initRepertoireSorter();
	initRepertoireSelector();
	initSongUploadFeedback();
	initPublicHeader();

	document.querySelectorAll('form[data-confirm]').forEach(form => {
		form.addEventListener('submit', event => {
			if (!window.confirm(form.dataset.confirm)) {
				event.preventDefault();

				return;
			}

			const expected = form.dataset.confirmText?.trim();
			if (!expected) {
				return;
			}

			const answer = window.prompt(form.dataset.confirmPrompt || `Escribe ${expected} para confirmar.`, '');
			if (answer?.trim() !== expected) {
				event.preventDefault();
			}
		});
	});
});
