(() => {
  'use strict';
  const widget=document.querySelector('#site-search');
  if(!widget)return;
  const header=widget.closest('.dz-header'),input=widget.querySelector('[data-search-input]'),form=widget.querySelector('form');
  const dropdown=widget.querySelector('.dz-search-dropdown'),list=widget.querySelector('[data-search-results]');
  const popular=widget.querySelector('[data-search-popular]'),history=widget.querySelector('[data-search-history]'),historyList=widget.querySelector('[data-search-history-list]');
  const empty=widget.querySelector('[data-search-empty]'),status=widget.querySelector('[data-search-status]'),spinner=widget.querySelector('[data-search-spinner]'),clear=widget.querySelector('[data-search-clear]');
  const submit=widget.querySelector('.dz-search-submit'),hint=widget.querySelector('[data-search-hint]');
  const key='dz-search-history',limit=6;
  let timer,closeTimer,controller,active=-1,opener,opened=false,sequence=0;
  const motion=()=>matchMedia('(prefers-reduced-motion: reduce)').matches?0:220;
  const readHistory=()=>{try{const value=JSON.parse(sessionStorage.getItem(key)||'[]');return Array.isArray(value)?value.filter(x=>typeof x==='string'&&x.length<=200).slice(0,limit):[];}catch{return [];}};
  const saveHistory=values=>{try{sessionStorage.setItem(key,JSON.stringify(values));}catch{}};
  const remember=()=>{const q=input.value.trim();if(q)saveHistory([q,...readHistory().filter(x=>x.toLocaleLowerCase('ru')!==q.toLocaleLowerCase('ru'))].slice(0,limit));};
  const clearActive=()=>{active=-1;input.removeAttribute('aria-activedescendant');widget.querySelectorAll('[data-search-option]').forEach(x=>{x.removeAttribute('data-active');if(x.matches('[role=option]'))x.setAttribute('aria-selected','false');});};
  const choices=()=>[...widget.querySelectorAll('[data-search-option]')].filter(e=>e.getClientRects().length);
  const scrollChoice=node=>{const scroller=widget.querySelector('.dz-search-scroll'),r=node.getBoundingClientRect(),box=scroller.getBoundingClientRect();if(r.bottom>box.bottom)scroller.scrollTop+=r.bottom-box.bottom;if(r.top<box.top)scroller.scrollTop-=box.top-r.top;};
  const activate=index=>{const rows=choices();if(!rows.length)return;clearActive();active=(index+rows.length)%rows.length;rows[active].dataset.active='true';if(rows[active].closest('[role=listbox]')){input.setAttribute('aria-activedescendant',rows[active].id);rows[active].setAttribute('aria-selected','true');}scrollChoice(rows[active]);};
  const historyIcon=()=>{const svg=document.createElementNS('http://www.w3.org/2000/svg','svg');svg.setAttribute('viewBox','0 0 24 24');svg.setAttribute('aria-hidden','true');svg.innerHTML='<path d="M3 11a9 9 0 1 1 2 7M3 5v6h6m3-4v5l3 2"/>';return svg;};
  function showHistory(){
    historyList.replaceChildren();const values=readHistory();history.hidden=values.length===0||!!input.value.trim();
    values.forEach((query,i)=>{const row=document.createElement('div');row.className='dz-search-history-row';const button=document.createElement('button');button.type='button';button.className='dz-search-suggestion';button.dataset.searchQuery=query;button.dataset.searchOption='';button.setAttribute('role','option');button.setAttribute('aria-selected','false');button.id=`history-query-${i}`;const label=document.createElement('span');label.textContent=query;button.append(historyIcon(),label);const remove=document.createElement('button');remove.type='button';remove.dataset.historyRemove=query;remove.className='dz-history-remove';remove.setAttribute('aria-label',`Удалить запрос «${query}»`);remove.innerHTML='<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>';row.append(button,remove);historyList.append(row);});document.dispatchEvent(new Event('dz:content-updated'));
  }
  const resetList=()=>{list.replaceChildren();clearActive();};
  const loading=value=>{form.setAttribute('aria-busy',String(value));spinner.classList.toggle('is-visible',value);};
  function render(results,query){
    resetList();popular.hidden=true;history.hidden=true;empty.hidden=results.length>0;submit.hidden=false;hint.textContent=results.length?`Подходящие варианты: ${results.length}`:'Попробуйте другой запрос';
    results.forEach((result,i)=>{const item=document.createElement('li');item.id=`site-search-option-${i}`;item.dataset.searchOption='';item.setAttribute('role','option');item.setAttribute('aria-selected','false');const link=document.createElement('a');link.href=result.url;const title=document.createElement('strong');const at=result.title.toLocaleLowerCase('ru').indexOf(query.toLocaleLowerCase('ru'));if(at<0)title.textContent=result.title;else{const mark=document.createElement('mark');mark.textContent=result.title.slice(at,at+query.length);title.append(document.createTextNode(result.title.slice(0,at)),mark,document.createTextNode(result.title.slice(at+query.length)));}const type=document.createElement('span');type.className='dz-search-result-type';type.textContent=result.type;link.append(title,type);item.append(link);list.append(item);});
    status.textContent=results.length?`Найдено вариантов: ${results.length}`:'Ничего не найдено';document.dispatchEvent(new Event('dz:content-updated'));
  }
  async function search(query){
    const token=++sequence;controller?.abort();clear.hidden=!query;resetList();loading(false);
    if(!query.trim()){popular.hidden=false;empty.hidden=true;submit.hidden=true;hint.textContent='';status.textContent='';showHistory();return;}
    popular.hidden=true;history.hidden=true;empty.hidden=true;submit.hidden=false;hint.textContent='Ищем подходящие страницы…';
    controller=new AbortController();loading(true);
    try{const response=await fetch(`/search-api?q=${encodeURIComponent(query)}`,{headers:{Accept:'application/json'},signal:controller.signal});if(!response.ok){const error=new Error('Search failed');if(response.status===429)error.retryAfter=Math.max(1,Math.min(60,Number(response.headers.get('Retry-After'))||10));throw error;}const data=await response.json();if(token===sequence&&opened)render(data.results||[],query);}
    catch(error){if(error.name!=='AbortError'&&token===sequence){empty.hidden=true;hint.textContent=error.retryAfter?`Много запросов. Повторите поиск через ${error.retryAfter} сек.`:'Подсказки недоступны — откройте все результаты';status.textContent=hint.textContent;}}
    finally{if(token===sequence)loading(false);}
  }
  function close(restore=false){
    if(!opened)return;opened=false;++sequence;clearTimeout(timer);controller?.abort();loading(false);input.setAttribute('aria-expanded','false');clearActive();
    document.querySelectorAll('[data-search-open]').forEach(e=>e.setAttribute('aria-expanded','false'));
    widget.classList.remove('is-open');dropdown.inert=true;
    clearTimeout(closeTimer);closeTimer=setTimeout(()=>{if(opened)return;dropdown.hidden=true;widget.hidden=true;header.classList.remove('dz-search-active');},motion());
    document.documentElement.classList.remove('dz-search-keyboard');if(restore)opener?.focus({preventScroll:true});
  }
  function open(button){
    clearTimeout(closeTimer);opener=button||opener;opened=true;widget.hidden=false;dropdown.hidden=false;dropdown.inert=false;header.classList.add('dz-search-active');
    document.dispatchEvent(new Event('dz:search-open'));document.querySelectorAll('[data-search-open]').forEach(e=>e.setAttribute('aria-expanded','true'));input.setAttribute('aria-expanded','true');
    requestAnimationFrame(()=>{widget.classList.add('is-open');input.focus({preventScroll:true});});search(input.value);
  }
  document.addEventListener('click',event=>{
    const button=event.target.closest('[data-search-open]');
    if(button){if(opened){close(true);return;}const menu=button.closest('dialog[open]');if(menu){menu.addEventListener('close',()=>open(document.querySelector('.dz-menu-toggle')),{once:true});window.dzDialogs.close(menu);}else open(button);return;}
    if(!widget.contains(event.target)){close();return;}
    if(event.target.closest('[data-search-close]')){close(true);return;}
    const remove=event.target.closest('[data-history-remove]');if(remove){saveHistory(readHistory().filter(x=>x!==remove.dataset.historyRemove));showHistory();input.focus({preventScroll:true});return;}
    if(event.target.closest('[data-history-clear]')){saveHistory([]);showHistory();input.focus({preventScroll:true});return;}
    const suggestion=event.target.closest('[data-search-query]');if(suggestion){clearTimeout(timer);input.value=suggestion.dataset.searchQuery;input.focus({preventScroll:true});search(input.value);return;}
    if(event.target.closest('[data-search-results] a'))remember();
  });
  widget.querySelectorAll('[data-search-popular] [data-search-query]').forEach((e,i)=>{e.dataset.searchOption='';e.id=`popular-query-${i}`;});
  input.addEventListener('input',()=>{clearTimeout(timer);clear.hidden=!input.value;clearActive();resetList();controller?.abort();++sequence;timer=setTimeout(()=>search(input.value),120);});
  clear.addEventListener('click',()=>{clearTimeout(timer);input.value='';search('');input.focus({preventScroll:true});});
  form.addEventListener('submit',()=>remember());
  input.addEventListener('keydown',event=>{if(event.key==='ArrowDown'||event.key==='ArrowUp'){event.preventDefault();activate(active+(event.key==='ArrowDown'?1:-1));}else if(event.key==='Enter'&&active>=0){event.preventDefault();const choice=choices()[active];(choice?.querySelector('a')||choice)?.click();}});
  document.addEventListener('keydown',event=>{if(opened&&event.key==='Escape'){event.preventDefault();close(true);}});
  document.addEventListener('focusin',event=>{if(opened&&!widget.contains(event.target)&&!event.target.closest('[data-search-open]'))close();});
  document.addEventListener('dz:dialog-open',()=>close());
  document.addEventListener('dz:nav-open',()=>close());
  addEventListener('pagehide',()=>{close();clearTimeout(closeTimer);widget.hidden=true;dropdown.hidden=true;header.classList.remove('dz-search-active');});
  const geometry=()=>{
    const view=window.visualViewport;
    const keyboard=!!view&&opened&&view.height<innerHeight*.8;
    widget.style.setProperty('--search-viewport-bottom',`${view?view.offsetTop+view.height:innerHeight}px`);
    widget.style.setProperty('--search-bottom-clearance',keyboard?'0px':'var(--mobile-action-space,78px)');
    document.documentElement.classList.toggle('dz-search-keyboard',keyboard);
  };
  addEventListener('resize',geometry);window.visualViewport?.addEventListener('resize',geometry);window.visualViewport?.addEventListener('scroll',geometry);geometry();
})();
