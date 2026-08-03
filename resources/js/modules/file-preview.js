export function initFilePreview() {
    const input=document.querySelector('[data-file-preview]'); const list=document.querySelector('[data-file-preview-list]'); if(!input||!list)return;
    input.addEventListener('change',()=>{ list.innerHTML=''; [...input.files].forEach(file=>{ const item=document.createElement('div'); item.className='file-preview-item'; if(file.type.startsWith('image/')){ const img=document.createElement('img'); img.src=URL.createObjectURL(file); img.onload=()=>URL.revokeObjectURL(img.src); item.append(img); } else { item.innerHTML='<div class="pdf-placeholder"><i class="bi bi-file-earmark-pdf"></i><span>PDF</span></div>'; } const name=document.createElement('small'); name.textContent=file.name; item.append(name); list.append(item); }); });
}
