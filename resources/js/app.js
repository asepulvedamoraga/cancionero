import './bootstrap';
import { initFilePreview } from './modules/file-preview';
import { initCopyButtons } from './modules/copy-buttons';
import { initSongFileSorter } from './modules/song-files-sorter';
import { initSongDuplicateCheck } from './modules/song-duplicate-check';
import { initRepertoireSorter } from './modules/repertoire-sorter';
import { initRepertoireSelector } from './modules/repertoire-selector';
document.documentElement.classList.add('js');
document.addEventListener('DOMContentLoaded',()=>{ initFilePreview(); initCopyButtons(); initSongFileSorter(); initSongDuplicateCheck(); initRepertoireSorter(); initRepertoireSelector(); document.querySelectorAll('form[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{ if(!window.confirm(form.dataset.confirm))event.preventDefault(); })); });
