(() => {
 'use strict';
 const links=[...document.querySelectorAll('[data-certificate-open]')],dialog=document.querySelector('#certificate-viewer');
 if(!links.length||!dialog)return;
 const picture=dialog.querySelector('[data-certificate-full]'),canvas=dialog.querySelector('.dz-certificate-canvas'),title=dialog.querySelector('#certificate-title'),counter=dialog.querySelector('[data-certificate-counter]'),download=dialog.querySelector('[data-certificate-download]'),zoom=dialog.querySelector('[data-certificate-zoom]');
 let index=0;
 picture.addEventListener('load',()=>{if(picture.naturalWidth&&picture.naturalHeight)canvas.style.setProperty('--certificate-ratio',String(picture.naturalWidth/picture.naturalHeight));});
 const resetZoom=()=>{canvas.classList.remove('is-zoomed');zoom.setAttribute('aria-pressed','false');zoom.textContent='Увеличить';canvas.scrollTo({top:0,left:0,behavior:'instant'});};
 function show(next){index=(next+links.length)%links.length;const link=links[index];resetZoom();picture.src=link.href;picture.alt=link.dataset.certificateTitle;title.textContent=link.dataset.certificateTitle;download.href=link.dataset.certificateOriginal;counter.textContent=`${index+1} / ${links.length}`;document.dispatchEvent(new Event('dz:content-updated'));}
 links.forEach((link,i)=>link.addEventListener('click',event=>{if(event.ctrlKey||event.metaKey||event.shiftKey||event.altKey)return;event.preventDefault();show(i);window.dzDialogs.open(dialog,link);}));
 dialog.querySelector('[data-certificate-prev]').addEventListener('click',()=>show(index-1));dialog.querySelector('[data-certificate-next]').addEventListener('click',()=>show(index+1));
 zoom.addEventListener('click',()=>{const expanded=!canvas.classList.contains('is-zoomed');canvas.style.setProperty('--certificate-full-width',Math.max(picture.naturalWidth,canvas.clientWidth*1.7)+'px');canvas.classList.toggle('is-zoomed',expanded);zoom.setAttribute('aria-pressed',String(expanded));zoom.textContent=expanded?'Уменьшить':'Увеличить';if(!expanded)canvas.scrollTo({top:0,left:0,behavior:'instant'});});
 dialog.addEventListener('keydown',event=>{if(event.key==='ArrowRight'||event.key==='ArrowLeft'){event.preventDefault();show(index+(event.key==='ArrowRight'?1:-1));}});
 dialog.addEventListener('close',resetZoom);
})();
