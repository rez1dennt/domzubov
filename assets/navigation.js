(() => {
  'use strict';
  const triggers=[...document.querySelectorAll('[data-nav-panel]')];
  const panels=[...document.querySelectorAll('.dz-mega-panel')];
  const scrim=document.querySelector('[data-nav-scrim]');
  const tabs=[...document.querySelectorAll('[data-service-category]')];
  let active=null, opener=null, closingTimer;
  const motion=()=>matchMedia('(prefers-reduced-motion: reduce)').matches?0:220;
  function close(returnFocus=false){
    if(!active)return;
    const panel=active;active=null;
    triggers.forEach(trigger=>trigger.setAttribute('aria-expanded','false'));
    panel.classList.remove('is-open');scrim.classList.remove('is-open');
    panel.inert=true;
    clearTimeout(closingTimer);
    closingTimer=setTimeout(()=>{if(active!==panel)panel.hidden=true;if(!active)scrim.hidden=true;},motion());
    if(returnFocus)opener?.focus({preventScroll:true});
  }
  function select(tab,focus=false){
    tabs.forEach(item=>{const selected=item===tab;item.setAttribute('aria-selected',String(selected));item.tabIndex=selected?0:-1;});
    document.querySelectorAll('[data-service-links]').forEach(panel=>panel.hidden=panel.dataset.serviceLinks!==tab.dataset.serviceCategory);
    if(focus)tab.focus({preventScroll:true});
  }
  triggers.forEach(trigger=>trigger.addEventListener('click',()=>{
    const panel=document.getElementById(trigger.dataset.navPanel);
    if(active===panel){close();return;}
    document.dispatchEvent(new Event('dz:nav-open'));
    clearTimeout(closingTimer);
    panels.forEach(item=>{item.hidden=item!==panel;item.classList.remove('is-open');item.inert=item!==panel;});
    triggers.forEach(item=>item.setAttribute('aria-expanded',String(item===trigger)));
    active=panel;opener=trigger;scrim.hidden=false;
    if(panel.id==='services-menu')select(tabs[0]);
    requestAnimationFrame(()=>{panel.classList.add('is-open');scrim.classList.add('is-open');});
    if(trigger.matches(':focus-visible'))panel.querySelector('button,a')?.focus({preventScroll:true});
  }));
  tabs.forEach(tab=>{
    tab.addEventListener('click',()=>select(tab));
    tab.addEventListener('pointerenter',event=>{if(event.pointerType==='mouse')select(tab);});
    tab.addEventListener('keydown',event=>{
      const index=tabs.indexOf(tab);let next=index;
      if(event.key==='ArrowDown'||event.key==='ArrowRight')next=(index+1)%tabs.length;
      else if(event.key==='ArrowUp'||event.key==='ArrowLeft')next=(index+tabs.length-1)%tabs.length;
      else if(event.key==='Home')next=0;else if(event.key==='End')next=tabs.length-1;else return;
      event.preventDefault();select(tabs[next],true);
    });
  });
  scrim.addEventListener('click',()=>close(true));
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&active){event.preventDefault();close(true);}});
  document.addEventListener('click',event=>{if(active&&!event.target.closest('.dz-mega-panel,[data-nav-panel]'))close();});
  document.addEventListener('focusin',event=>{if(active&&!event.target.closest('.dz-mega-panel,.dz-header'))close();});
  document.addEventListener('dz:dialog-open',()=>close());
  document.addEventListener('dz:search-open',()=>close());
  matchMedia('(max-width:1099px)').addEventListener('change',event=>{if(event.matches)close();});
  addEventListener('pagehide',()=>{clearTimeout(closingTimer);active=null;panels.forEach(panel=>{panel.hidden=true;panel.classList.remove('is-open');});scrim.hidden=true;triggers.forEach(trigger=>trigger.setAttribute('aria-expanded','false'));});
  function geometry(){document.documentElement.style.setProperty('--scrollbar-gap',Math.max(0,innerWidth-document.documentElement.clientWidth)+'px');}
  geometry();addEventListener('resize',geometry);
  const bottomBar=document.querySelector('.dz-mobile-actions');
  new ResizeObserver(()=>document.documentElement.style.setProperty('--mobile-action-space',bottomBar.getBoundingClientRect().height+'px')).observe(bottomBar);
})();
