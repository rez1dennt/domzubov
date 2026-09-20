async(page)=>{
 await page.bringToFront();
 const failures=[];let checks=0;
 await page.emulateMedia({reducedMotion:'reduce'});
 for(const base of ['http://127.0.0.1:8174','http://domzubov.localhost'])for(const width of [1280,768,360,320]){
  await page.setViewportSize({width,height:900});
  for(const route of ['/','/doctors/rasulov-magomed-radzhabovich']){
   await page.goto(base+route);
   const roots=page.locator('[data-team-carousel],[data-review-carousel],[data-gallery]');
   for(let i=0;i<await roots.count();i++){
    const root=roots.nth(i),viewport=root.locator('.dz-carousel-viewport');
    if(!await viewport.count())continue;
    await viewport.scrollIntoViewIfNeeded();
    const result=await root.evaluate(async root=>{
     const viewport=root.querySelector('.dz-carousel-viewport'),api=viewport.dzCarousel;
     const slides=api.slideNodes(),count=slides.length,failures=[];
     const kind=root.hasAttribute('data-team-carousel')?'team':root.hasAttribute('data-review-carousel')?'review':'gallery';
     const next=root.querySelector('[data-'+kind+'-next]'),prev=root.querySelector('[data-'+kind+'-prev]');
     const visible=()=>slides.map((s,i)=>({i,r:s.getBoundingClientRect()})).filter(s=>s.r.right>viewport.getBoundingClientRect().left+.5&&s.r.left<viewport.getBoundingClientRect().right-.5).sort((a,b)=>a.r.left-b.r.left).map(s=>s.i);
     const size=visible().length,y=scrollY;
     for(let step=1;step<=count+2;step++){
      next.click();await new Promise(r=>setTimeout(r,60));
      const actual=visible(),expected=Array.from({length:kind === 'gallery' ? actual.length : size},(_,j)=>(step+j)%count);
      if(JSON.stringify(actual)!==JSON.stringify(expected))failures.push({step,actual,expected});
      if(next.disabled||prev?.disabled)failures.push({step,disabled:true});
     }
     for(let j=0;j<3;j++){if(prev)prev.click();else viewport.dispatchEvent(new KeyboardEvent('keydown',{key:'ArrowLeft',bubbles:true}));await new Promise(r=>setTimeout(r,60));}
     if(visible()[0]!==count-1)failures.push({backward:visible()});
     if(Math.abs(scrollY-y)>1)failures.push({pageJump:scrollY-y});
     if(!slides.every((s,j)=>s===viewport.firstElementChild.children[j])||viewport.firstElementChild.children.length!==count)failures.push({orderChangedOrClones:true});
     return {checks:count+5,failures,kind};
    });
    checks+=result.checks;failures.push(...result.failures.map(f=>({base,width,route,i,kind:result.kind,...f})));
   }
   if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth))failures.push({base,width,route,overflow:true});
  }
 }
 await page.emulateMedia({reducedMotion:'no-preference'});return {checks,failures};
}
