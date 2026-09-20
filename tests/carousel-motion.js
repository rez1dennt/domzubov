async(page)=>{
 await page.bringToFront();
 const failures=[],checks=[];await page.emulateMedia({reducedMotion:'no-preference'});
 for(const base of ['http://127.0.0.1:8174','http://domzubov.localhost'])for(const width of [1280,360,320]){
  await page.setViewportSize({width,height:900});await page.goto(base);
  for(const kind of ['team','review']){
   const root=page.locator(`[data-${kind}-carousel]`).first();await root.locator(`[data-${kind}-next]`).scrollIntoViewIfNeeded();
   const result=await root.evaluate(async(root,kind)=>{
    const slides=[...root.querySelectorAll(`[data-${kind}-card]`)];const outgoing=slides[0],next=root.querySelector(`[data-${kind}-next]`);
    const before=slides.map(s=>s);const frames=[];const viewport=root.querySelector('.dz-carousel-viewport');const t=performance.now();next.click();
    await new Promise(resolve=>{const sample=()=>{
      const elapsed=performance.now()-t;
      const r=outgoing.getBoundingClientRect(),box=viewport?.getBoundingClientRect();
      frames.push({elapsed,hidden:outgoing.hidden,display:getComputedStyle(outgoing).display,opacity:slides.map(s=>Number(getComputedStyle(s).opacity)),x:r.x,inViewport:!!box&&r.right>box.left+.5&&r.left<box.right-.5});
      if(elapsed<1000)requestAnimationFrame(sample);else resolve();
    };requestAnimationFrame(sample);});
    const after=[...root.querySelectorAll(`[data-${kind}-card]`)];
    return {frames,unchangedOrder:before.every((s,i)=>s===after[i]),count:slides.length};
   },kind);
   const fades=result.frames.some(f=>f.opacity.some(o=>o<.999));
   const disappeared=result.frames.some(f=>f.elapsed<450&&(f.hidden||f.display==='none'));
   const travel=Math.abs(result.frames[0].x-result.frames.at(-1).x);
   const motionFrames=result.frames.filter((f,i)=>i&&Math.abs(f.x-result.frames[i-1].x)>.1);
   const biggestStep=Math.max(0,...result.frames.slice(1).map((f,i)=>f.inViewport&&result.frames[i].inViewport?Math.abs(f.x-result.frames[i].x):0));
   if(fades||disappeared||!result.unchangedOrder||motionFrames.length<12||biggestStep>100)failures.push({base,width,kind,fades,disappeared,unchangedOrder:result.unchangedOrder,motionFrames:motionFrames.length,biggestStep,travel});
   checks.push({base,width,kind,motionFrames:motionFrames.length,biggestStep});
  }
 }
 return {checks:checks.length,failures,measurements:checks};
}
