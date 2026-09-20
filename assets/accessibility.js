(() => {
  'use strict';
  const root=document.documentElement;
  const panel=document.getElementById('accessibility-settings');
  const defaults={enabled:false,fontSize:100,colorScheme:'normal',imagesHidden:false,lineHeight:'normal',letterSpacing:'normal'};
  const allowed={fontSize:[100,125,150],colorScheme:['normal','black-white','white-black','gold'],lineHeight:['normal','medium','large'],letterSpacing:['normal','medium','large']};
  let settings={...defaults};
  try {
    const saved=JSON.parse(localStorage.getItem('accessibility-settings')||'null');
    if(saved&&typeof saved==='object'){
      settings.enabled=saved.enabled===true;settings.imagesHidden=saved.imagesHidden===true;
      Object.keys(allowed).forEach(key=>{if(allowed[key].includes(saved[key]))settings[key]=saved[key];});
    }
    localStorage.removeItem('dz-accessibility');
  }catch(_){}
  function scaleText(){
    document.querySelectorAll('.dz-a11y-scaled').forEach(element=>{element.classList.remove('dz-a11y-scaled');element.style.removeProperty('--reading-size');});
    if(!settings.enabled||settings.fontSize===100)return;
    const multiplier=settings.fontSize/100;
    const elements=[...document.querySelectorAll('main *,footer *,.dz-mega-panel *,#mobile-menu *,#site-search *,#patient-review-detail *,#certificate-viewer *,#appointment *,#form-notice *,#accessibility-settings *,.dz-desktop-nav *,.dz-a11y > span,.dz-header-cta,.dz-mobile-actions > *')].filter(element=>{
      if(element.closest('.dz-logo'))return false;
      if(element.closest('svg')||['IMG','SVG','PATH','INPUT','SELECT','OPTION'].includes(element.tagName))return false;
      return [...element.childNodes].some(node=>node.nodeType===Node.TEXT_NODE&&node.textContent.trim());
    });
    const sizes=elements.map(element=>parseFloat(getComputedStyle(element).fontSize)*multiplier);
    elements.forEach((element,index)=>{element.style.setProperty('--reading-size',sizes[index]+'px');element.classList.add('dz-a11y-scaled');});
  }
  function apply(announce=false){
    root.dataset.a11yEnabled=String(settings.enabled);
    root.dataset.a11yScheme=settings.enabled?settings.colorScheme:'normal';
    root.dataset.a11yImages=settings.enabled&&settings.imagesHidden?'hidden':'visible';
    root.dataset.a11yLine=settings.enabled?settings.lineHeight:'normal';
    root.dataset.a11ySpacing=settings.enabled?settings.letterSpacing:'normal';
    root.dataset.a11ySize=String(settings.enabled?settings.fontSize:100);
    panel.querySelectorAll('[data-setting]').forEach(group=>group.querySelectorAll('button').forEach(button=>button.setAttribute('aria-pressed',String(String(settings[group.dataset.setting])===button.dataset.value))));
    const imageButton=panel.querySelector('[data-toggle-images]');imageButton.setAttribute('aria-pressed',String(settings.imagesHidden));
    imageButton.querySelector('span').textContent=settings.imagesHidden?'Изображения скрыты':'Скрыть изображения';
    document.querySelectorAll('[data-accessibility]').forEach(button=>button.setAttribute('aria-pressed',String(settings.enabled)));
    scaleText();
    try{localStorage.setItem('accessibility-settings',JSON.stringify(settings));}catch(_){}
    if(announce)panel.querySelector('[data-accessibility-status]').textContent='Настройки применены';
  }
  document.querySelectorAll('[data-accessibility]').forEach(button=>button.addEventListener('click',()=>{
    settings.enabled=true;apply();window.dzDialogs.open(panel,button);
  }));
  panel.querySelectorAll('[data-setting]').forEach(group=>group.addEventListener('click',event=>{
    const button=event.target.closest('button[data-value]');if(!button)return;
    const key=group.dataset.setting;settings[key]=key==='fontSize'?Number(button.dataset.value):button.dataset.value;settings.enabled=true;apply(true);
  }));
  panel.querySelector('[data-toggle-images]').addEventListener('click',()=>{settings.imagesHidden=!settings.imagesHidden;apply(true);});
  panel.querySelector('[data-reset-accessibility]').addEventListener('click',()=>{settings={...defaults,enabled:true};apply(true);});
  panel.querySelector('[data-disable-accessibility]').addEventListener('click',()=>{settings={...defaults};apply();window.dzDialogs.close(panel);});
  let resize;
  document.addEventListener('dz:content-updated',()=>{if(settings.enabled)scaleText();});
  addEventListener('resize',()=>{clearTimeout(resize);resize=setTimeout(()=>{if(settings.enabled)scaleText();},150);});
  apply();document.fonts.ready.then(()=>{if(settings.enabled)scaleText();});
})();
