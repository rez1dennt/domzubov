async(page)=>{
 const checks=[],errors=[];const check=(ok,name)=>checks.push({ok,name});page.on('pageerror',e=>errors.push(e.message));
 await page.emulateMedia({reducedMotion:'no-preference'});
 for(const base of ['http://127.0.0.1:8174','http://domzubov.localhost']){
  await page.setViewportSize({width:1280,height:900});await page.goto(base);
  const team=page.locator('[data-team-carousel]').first();await team.locator('[data-team-prev]').scrollIntoViewIfNeeded();
  await team.locator('[data-team-card]').evaluateAll(es=>es.forEach((e,i)=>e.dataset.loopTestId=String(i)));
  const y=await page.evaluate(()=>scrollY);await team.locator('[data-team-prev]').click();
  check(await team.locator('[data-team-card]:visible').evaluateAll(es=>es.some(e=>e.getAnimations().length)),'Animation active at backward seam '+base);
  await page.waitForTimeout(350);check(await team.locator('[data-team-card]:visible').first().getAttribute('data-loop-test-id')==='3','Backward seam '+base);
  for(let i=0;i<7;i++)await team.locator('[data-team-next]').click();await page.waitForTimeout(350);
  check(await team.locator('[data-team-card]:visible').first().getAttribute('data-loop-test-id')==='2','Rapid clicks retain circular position '+base);
  check(Math.abs(await page.evaluate(()=>scrollY)-y)<=1,'No page jump '+base);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');const track=page.locator('[data-gallery-track]').first();await track.scrollIntoViewIfNeeded();
  await track.locator('[data-certificate-open]').nth(2).focus();await page.setViewportSize({width:320,height:900});await page.waitForTimeout(350);
  check(await track.evaluate(e=>document.activeElement===e),'Resize keeps focus usable '+base);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');await track.scrollIntoViewIfNeeded();await track.focus();await page.keyboard.press('Tab');await page.keyboard.press('Tab');await page.waitForTimeout(350);
  check(await track.evaluate(e=>e.firstElementChild.contains(document.activeElement)&&e.scrollLeft===0),'Tab brings partial card into view '+base);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');await track.scrollIntoViewIfNeeded();
  const second=track.locator('[data-certificate-open]').nth(1), expected=await second.getAttribute('data-certificate-title'),r=await second.boundingBox(),box=await track.boundingBox();
  await page.mouse.click(Math.min(r.x+8,box.x+box.width-5),r.y+50);await page.waitForTimeout(300);
  check(await page.locator('#certificate-viewer').evaluate(e=>e.open)&&await page.locator('#certificate-title').innerText()===expected,'Partial certificate click opens correct document '+base);
  await page.keyboard.press('Escape');await page.waitForTimeout(300);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');await track.scrollIntoViewIfNeeded();
  const start=await track.locator('[data-certificate-open]').first().getAttribute('href');let b=await track.boundingBox();
  await page.mouse.move(b.x+b.width*.75,b.y+80);await page.mouse.down();await page.mouse.move(b.x+b.width*.2,b.y+85,{steps:8});await page.mouse.up();await page.waitForTimeout(350);
  check(await track.locator('[data-certificate-open]').first().getAttribute('href')!==start,'Horizontal swipe advances '+base);
  check(!await page.locator('#certificate-viewer').evaluate(e=>e.open),'Swipe does not open document '+base);
  const moved=await track.locator('[data-certificate-open]').first().getAttribute('href');await track.hover();await page.mouse.wheel(-400,0);await page.waitForTimeout(350);
  check(await track.locator('[data-certificate-open]').first().getAttribute('href')!==moved,'Horizontal wheel moves backward '+base);
  const total=await track.locator(':scope > *').count();check(total===16,'No cloned documents '+base);
  check(!await page.evaluate(()=>document.documentElement.scrollWidth>document.documentElement.clientWidth),'No page overflow '+base);
 }
 return {checks:checks.length,failures:checks.filter(x=>!x.ok),errors:[...new Set(errors)]};
}
