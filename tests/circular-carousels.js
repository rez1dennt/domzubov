async(page)=>{
 const bases=['http://127.0.0.1:8174','http://domzubov.localhost'],failures=[];let checks=0;
 await page.emulateMedia({reducedMotion:'reduce'});
 for(const base of bases)for(const width of [1280,768,360,320]){
  await page.setViewportSize({width,height:900});await page.goto(base);
  for(const type of ['team','review']){
   const root=page.locator(`[data-${type}-carousel]`).first();
   const cards=root.locator(`[data-${type}-card]`);
   await cards.evaluateAll(es=>es.forEach((e,i)=>e.dataset.loopTestId=String(i)));
   const total=await cards.count();const size=await root.locator(`[data-${type}-card]:visible`).count();
   const next=root.locator(`[data-${type}-next]`),prev=root.locator(`[data-${type}-prev]`);
   await next.scrollIntoViewIfNeeded();await page.waitForTimeout(350);const y=await page.evaluate(()=>scrollY);
   for(let step=1;step<=total+2;step++){
    await next.click();await page.waitForTimeout(20);
    const visible=await root.locator(`[data-${type}-card]:visible`).evaluateAll(es=>es.map(e=>Number(e.dataset.loopTestId)));
    const expected=Array.from({length:size},(_,j)=>(step+j)%total);
    if(JSON.stringify(visible)!==JSON.stringify(expected))failures.push({base,width,type,step,visible,expected});checks++;
   }
   for(let step=1;step<=3;step++)await prev.click();
   await page.waitForTimeout(350);
   const visible=await root.locator(`[data-${type}-card]:visible`).evaluateAll(es=>es.map(e=>Number(e.dataset.loopTestId)));
   if(visible[0]!==total-1||Math.abs(await page.evaluate(()=>scrollY)-y)>1)failures.push({base,width,type,backward:visible,scroll:await page.evaluate(()=>scrollY)-y});checks++;
  }
  for(const route of ['/','/doctors/rasulov-magomed-radzhabovich']){
   await page.goto(base+route);
   const galleries=page.locator('[data-gallery]');
   for(let i=0;i<await galleries.count();i++){
    const root=galleries.nth(i),track=root.locator('[data-gallery-track]');
    const buttons=root.locator('[data-gallery-next]');if(!await buttons.count())continue;
    await track.locator(':scope > *').evaluateAll(es=>es.forEach((e,j)=>e.dataset.loopTestId=String(j)));
    const count=await track.locator(':scope > *').count();const buttonVisible=await buttons.isVisible();
    if(buttonVisible)await buttons.scrollIntoViewIfNeeded();else{await track.scrollIntoViewIfNeeded();await track.focus();}
    for(let step=1;step<=count+1;step++){
     if(await buttons.isDisabled()){failures.push({base,width,route,i,disabledAt:step});break;}
     if(buttonVisible)await buttons.click();else await track.press('ArrowRight');await page.waitForTimeout(20);
     const first=await track.locator(':scope > *').first().getAttribute('data-loop-test-id');
     if(Number(first)!==step%count)failures.push({base,width,route,i,step,first});checks++;
    }
    if(await track.locator(':scope > *').count()!==count)failures.push({base,width,route,clonedItems:true});
   }
  }
  if(await page.evaluate(()=>document.documentElement.scrollWidth>document.documentElement.clientWidth))failures.push({base,width,overflow:true});
 }
 await page.emulateMedia({reducedMotion:'no-preference'});return {checks,failures};
}
