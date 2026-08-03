export function initSongDuplicateCheck(){
    const root=document.querySelector('[data-song-duplicate-check]');
    if(!root)return;
    const input=root.querySelector('#title');
    const results=root.querySelector('[data-song-duplicate-results]');
    let timer;
    let controller;
    const clear=()=>{results.replaceChildren();results.hidden=true;};
    input.addEventListener('input',()=>{
        window.clearTimeout(timer);
        controller?.abort();
        const query=input.value.trim();
        if(query.length<3){clear();return;}
        timer=window.setTimeout(async()=>{
            controller=new AbortController();
            try{
                const url=new URL(root.dataset.suggestionsUrl,window.location.origin);
                url.searchParams.set('q',query);
                const response=await fetch(url,{headers:{Accept:'application/json'},signal:controller.signal});
                if(!response.ok)throw new Error('No se pudieron buscar coincidencias.');
                const data=await response.json();
                clear();
                if(!data.songs.length)return;
                const heading=document.createElement('strong');
                heading.className='d-block small mb-2';
                heading.textContent='¿Ya existe esta canción? Revisa antes de crear otra versión:';
                const list=document.createElement('div');
                list.className='list-group list-group-flush';
                data.songs.forEach(song=>{
                    const link=document.createElement('a');
                    link.className='list-group-item list-group-item-action px-0 py-2';
                    link.href=song.url;
                    link.target='_blank';
                    link.rel='noopener';
                    const title=document.createElement('span');
                    title.className='d-block fw-semibold';
                    title.textContent=song.title+(song.mine?' · Mía':'');
                    const meta=document.createElement('small');
                    meta.className='text-secondary';
                    meta.textContent=`${song.author} · Subida por ${song.owner}`;
                    link.append(title,meta);
                    list.append(link);
                });
                results.append(heading,list);
                results.hidden=false;
            }catch(error){if(error.name!=='AbortError')clear();}
        },300);
    });
}