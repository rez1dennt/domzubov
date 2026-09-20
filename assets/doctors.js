(() => {
 'use strict';
 const root=document.querySelector('[data-doctor-directory]');if(!root)return;
 const cards=[...root.querySelectorAll('[data-doctor-card]')],search=root.querySelector('[data-doctor-search]'),clinic=root.querySelector('[data-doctor-clinic]'),specialties=[...root.querySelectorAll('[data-specialty]')],pagination=root.querySelector('[data-doctor-pagination]'),prev=root.querySelector('[data-doctor-prev]'),next=root.querySelector('[data-doctor-next]'),pageLabel=root.querySelector('[data-doctor-page]'),count=root.querySelector('[data-doctor-count]'),empty=root.querySelector('[data-no-results]');
 let selected='',selectedClinic=clinic?.value||root.dataset.initialClinic||'',page=0;const size=12;
 const normalize=value=>String(value||'').toLocaleLowerCase('ru').replaceAll('ё','е').trim();
 const names=new Map(specialties.map(button=>[button.dataset.specialty||'',button.textContent||'']));
 const includesId=(value,id)=>!id||String(value||'').split(',').includes(String(id));
 function render(){
  const query=normalize(search?.value);const matching=cards.filter(card=>{const labels=String(card.dataset.specialties||'').split(',').map(id=>names.get(id)||'').join(' ');return normalize(`${card.dataset.search||''} ${labels}`).includes(query)&&includesId(card.dataset.clinics,selectedClinic)&&includesId(card.dataset.specialties,selected);});
  const pages=Math.ceil(matching.length/size);page=Math.min(page,Math.max(0,pages-1));const visible=new Set(matching.slice(page*size,page*size+size));cards.forEach(card=>{card.hidden=!visible.has(card);});
  if(count)count.textContent=`Найдено специалистов: ${matching.length}`;if(empty)empty.hidden=matching.length>0;if(pagination)pagination.hidden=pages<2;if(pageLabel)pageLabel.textContent=`${page+1} / ${Math.max(1,pages)}`;if(prev)prev.disabled=page===0;if(next)next.disabled=page>=pages-1;
 }
 search?.addEventListener('input',()=>{page=0;render();});
 clinic?.addEventListener('change',()=>{selectedClinic=clinic.value;page=0;render();});
 clinic?.addEventListener('dz:filter',event=>{selectedClinic=String(event.detail?.value||'');page=0;render();});
 specialties.forEach(button=>button.addEventListener('click',()=>{selected=button.dataset.specialty||'';page=0;specialties.forEach(item=>item.setAttribute('aria-pressed',String(item===button)));render();}));
 prev?.addEventListener('click',()=>{if(page>0){page--;render();}});next?.addEventListener('click',()=>{if(!next.disabled){page++;render();}});render();
})();
