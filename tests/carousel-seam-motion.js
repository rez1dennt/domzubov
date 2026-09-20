async(page)=>{
 await page.bringToFront();
 const failures=[],measurements=[];await page.emulateMedia({reducedMotion:'no-preference'});
 for(const base of ['http://127.0.0.1:8174','http://domzubov.localhost'])for(const width of [1280,768,360,320]){
  await page.setViewportSize({width,height:900});
  for(const route of ['/','/doctors/rasulov-magomed-radzhabovich']){
   await page.goto(base+route);
   const roots=page.locator('[data-team-carousel],[data-review-carousel],[data-gallery]');
   for(let i=0;i<await roots.count();i++){
    const root=roots.nth(i);if(!await root.locator('.dz-carousel-viewport').count())continue;
    await root.locator('.dz-carousel-viewport').scrollIntoViewIfNeeded();
    const result=await root.evaluate(async root=>{
     const viewport=root.querySelector('.dz-carousel-viewport'),api=viewport.dzCarousel,slides=api.slideNodes();
     const kind=root.hasAttribute('data-team-carousel')?'team':root.hasAttribute('data-review-carousel')?'review':'gallery';
     let previous,steps=0,maxVelocity=0,blank=false,fade=false,hidden=false,maxGapExcess=0;
     const sample=(time=performance.now())=>{
      const box=viewport.getBoundingClientRect();
      const current=slides.map(s=>{const r=s.getBoundingClientRect();return {x:r.x,right:r.right,visible:r.right>box.left+.5&&r.left<box.right-.5};});
      if(previous)current.forEach((s,i)=>{if(s.visible&&previous.slides[i].visible){const delta=Math.abs(s.x-previous.slides[i].x),dt=time-previous.time;maxVelocity=Math.max(maxVelocity,delta/Math.max(dt,1));if(delta>.1)steps++;}});
            const visible=current.filter(s=>s.visible).sort((a,b)=>a.x-b.x);
      const allowedGap=parseFloat(getComputedStyle(slides[0]).marginRight)+2;
      let edge=box.left;
      for(const card of visible){maxGapExcess=Math.max(maxGapExcess,card.x-edge-allowedGap);edge=Math.max(edge,card.right);}
      maxGapExcess=Math.max(maxGapExcess,box.right-edge-allowedGap);
      blank ||= !visible.length;
      fade ||= slides.some(s=>Number(getComputedStyle(s).opacity)<.999);
      hidden ||= slides.some(s=>s.hidden||getComputedStyle(s).display==='none');
      previous={slides:current,time};
     };
     const run=async(direction,rapid=false)=>{
      api.scrollTo(direction>0?slides.length-1:0,true);previous=null;
      const firstFrameTime=await new Promise(r=>requestAnimationFrame(r));sample(firstFrameTime);
      const button=root.querySelector(`[data-${kind}-${direction>0?'next':'prev'}]`);const click=()=>{if(button)button.click();else viewport.dispatchEvent(new KeyboardEvent('keydown',{key:direction>0?'ArrowRight':'ArrowLeft',bubbles:true}));};click();
      let ticks=0;const started=performance.now();
      await new Promise(resolve=>{const frame=time=>{sample(time);ticks++;if(rapid&&(ticks===5||ticks===10))click();if(performance.now()-started>1100)resolve();else requestAnimationFrame(frame);};requestAnimationFrame(frame);});
      return api.selectedScrollSnap();
     };
     const forward=await run(1),backward=await run(-1),rapid=await run(1,true);
     return {kind,steps,maxVelocity,maxGapExcess,blank,fade,hidden,forward,backward,rapid,count:slides.length,unchangedOrder:slides.every((s,i)=>s===viewport.firstElementChild.children[i])};
    });
    const context={base,width,route,i};measurements.push({...context,...result});
    if(result.maxGapExcess>0||result.maxVelocity>6||result.steps<30||result.blank||result.fade||result.hidden||!result.unchangedOrder||result.forward!==0||result.backward!==result.count-1||result.rapid!==2%result.count)failures.push({...context,...result});
   }
  }
 }
 return {checks:measurements.length,failures,maxVelocity:Math.max(...measurements.map(m=>m.maxVelocity))};
}
