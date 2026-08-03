export function initCopyButtons(){
    document.querySelectorAll('[data-copy-text]').forEach(button=>button.addEventListener('click',async()=>{
        const label=button.querySelector('[data-copy-label]');
        try{
            await navigator.clipboard.writeText(button.dataset.copyText);
            if(label)label.textContent='Enlace copiado';
        }catch{
            const input=button.parentElement?.querySelector('input');
            input?.select();
            document.execCommand('copy');
            if(label)label.textContent='Enlace copiado';
        }
        window.setTimeout(()=>{if(label)label.textContent='Copiar enlace';},1800);
    }));
}