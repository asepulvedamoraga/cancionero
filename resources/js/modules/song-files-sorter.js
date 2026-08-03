async function saveOrder(list, status) {
    const files=[...list.querySelectorAll('[data-file-id]')].map(el=>Number(el.dataset.fileId));
    status.textContent='Guardando orden…'; status.className='small mt-3 text-secondary';
    try { const response=await fetch(list.dataset.reorderUrl,{method:'PUT',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({files})}); if(!response.ok)throw new Error(); status.textContent='Orden guardado.'; status.className='small mt-3 text-success'; }
    catch(error){ status.textContent='No se pudo guardar el orden. Recarga la página para restaurarlo.'; status.className='small mt-3 text-danger'; }
}
export function initSongFileSorter(){
    const list=document.querySelector('[data-song-files]'); if(!list)return; const status=document.querySelector('[data-sort-status]'); let active=null; let moved=false;
    list.querySelectorAll('.drag-handle').forEach(handle=>handle.addEventListener('pointerdown',event=>{ event.preventDefault(); active=handle.closest('[data-file-id]'); moved=false; active.classList.add('sorting-active'); handle.setPointerCapture(event.pointerId); }));
    list.addEventListener('pointermove',event=>{ if(!active)return; const target=document.elementFromPoint(event.clientX,event.clientY)?.closest('[data-file-id]'); if(!target||target===active||target.parentElement!==list)return; const rect=target.getBoundingClientRect(); const after=event.clientY>rect.top+rect.height/2 || (Math.abs(event.clientY-(rect.top+rect.height/2))<rect.height/4 && event.clientX>rect.left+rect.width/2); list.insertBefore(active,after?target.nextSibling:target); moved=true; });
    const finish=()=>{ if(!active)return; active.classList.remove('sorting-active'); active=null; if(moved)saveOrder(list,status); };
    list.addEventListener('pointerup',finish); list.addEventListener('pointercancel',finish);
}
