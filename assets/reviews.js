(() => {
  const root=document.querySelector('[data-reviews]');if(!root)return;
  const cards=[...root.querySelectorAll('[data-review]')];
  const doctor=root.querySelector('[data-review-doctor]'),service=root.querySelector('[data-review-service]');
  const buttons=[...document.querySelectorAll('main button')].filter(b=>['ВСЕ ОТЗЫВЫ','По специалистам','По видам услуг'].includes(b.textContent.trim()));
  let page=0;
  function render(){
    const filtered=cards.filter(card=>(!doctor.value||card.dataset.doctor===doctor.value)&&(!service.value||card.dataset.service===service.value));
    const count=Math.max(1,Math.ceil(filtered.length/12));page=Math.min(page,count-1);
    const visible=new Set(filtered.slice(page*12,page*12+12));cards.forEach(card=>card.hidden=!visible.has(card));
    root.querySelector('[data-review-count]').textContent=`Найдено отзывов: ${filtered.length}`;
    root.querySelector('[data-review-page]').textContent=`${page+1} / ${count}`;
    root.querySelector('[data-review-prev]').disabled=page===0;root.querySelector('[data-review-next]').disabled=page===count-1;
  }
  buttons.forEach((button,i)=>{
    button.setAttribute('aria-pressed',String(i===0));
    button.addEventListener('click',()=>{
      buttons.forEach(item=>{const active=item===button;item.setAttribute('aria-pressed',String(active));item.classList.toggle('dz-filter-selected',active);});
      root.querySelector('[data-review-doctor-label]').hidden=i!==1;root.querySelector('[data-review-service-label]').hidden=i!==2;
      doctor.value='';service.value='';page=0;render();
    });
  });
  [doctor,service].forEach(select=>select.addEventListener('change',()=>{page=0;render();}));
  ['prev','next'].forEach(direction=>root.querySelector(`[data-review-${direction}]`).addEventListener('click',()=>{page+=direction==='prev'?-1:1;render();root.scrollIntoView({block:'start',behavior:'instant'});}));
  cards.forEach(card=>{
    const button=card.querySelector('.dz-review-expand');
    button.addEventListener('click',()=>{const open=button.getAttribute('aria-expanded')!=='true';button.setAttribute('aria-expanded',String(open));card.classList.toggle('dz-review-open',open);button.textContent=open?'Свернуть':'Читать полностью';});
  });
  render();
})();
