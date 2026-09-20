(() => {
 'use strict';
 const reduced=matchMedia('(prefers-reduced-motion: reduce)');
 // Content is fully visible without JavaScript and under reduced motion.
 if(!reduced.matches && 'IntersectionObserver' in window){
  const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-revealed');observer.unobserve(entry.target);}}),{threshold:0,rootMargin:'0px 0px -36px 0px'});
  document.querySelectorAll('main>.js-reveal,main>.dz-source-reviews').forEach(section=>{
   if(section.matches('[data-team-carousel],[data-review-carousel],[data-gallery],[data-clinic-picker]')||section.querySelector('input,form'))return;
   if(section.getBoundingClientRect().top>innerHeight){section.classList.add('dz-reveal-ready');observer.observe(section);}
  });
 }
 let scheduled=false;
 const progress=()=>{scheduled=false;const max=document.documentElement.scrollHeight-innerHeight;document.documentElement.style.setProperty('--page-progress',`${max>0?Math.min(100,scrollY/max*100):0}%`);};
 addEventListener('scroll',()=>{if(!scheduled){scheduled=true;requestAnimationFrame(progress);}},{passive:true});addEventListener('resize',progress);progress();
 // Photo frames remain stable while images load.
 document.querySelectorAll('main img[loading=lazy]').forEach(img=>img.addEventListener('load',()=>img.classList.add('is-loaded'),{once:true}));
})();
