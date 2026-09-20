async(page)=>{
 await page.bringToFront();
 const checks=[],errors=[];const check=(ok,name)=>checks.push({ok,name});page.on('pageerror',e=>errors.push(e.message));
 const settle=async viewport=>viewport.evaluate(v=>new Promise((resolve,reject)=>{if(v.dataset.carouselMoving==='false')return resolve();const timeout=setTimeout(()=>reject(new Error('Carousel did not settle within 7 seconds')),7000);const done=()=>{clearTimeout(timeout);v.dzCarousel.off('settle',done);resolve();};v.dzCarousel.on('settle',done);}));
 for(const base of ['http://127.0.0.1:8174','http://domzubov.localhost'])for(const motion of ['no-preference','reduce']){
  await page.emulateMedia({reducedMotion:motion});const tag=base+' '+motion;
  await page.setViewportSize({width:1280,height:900});await page.goto(base);
  const team=page.locator('[data-team-carousel]').first(),rail=team.locator('.dz-carousel-viewport');await team.locator('[data-team-prev]').scrollIntoViewIfNeeded();
  const y=await page.evaluate(()=>scrollY);await team.locator('[data-team-prev]').click();await settle(rail);
  check(await rail.getAttribute('data-carousel-index')==='3','Backward seam '+tag);
  for(let i=0;i<7;i++)await team.locator('[data-team-next]').click();await settle(rail);
  check(await rail.getAttribute('data-carousel-index')==='2','Rapid clicks retain circular position '+tag);
  check(Math.abs(await page.evaluate(()=>scrollY)-y)<=1,'No page jump '+tag);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');
  const track=page.locator('[data-gallery-track]').first(),viewport=page.locator('.dz-carousel-viewport').first();
  await viewport.scrollIntoViewIfNeeded();await track.locator('[data-certificate-open]').nth(2).focus();await page.setViewportSize({width:320,height:900});await page.waitForTimeout(150);await settle(viewport);
  check(await viewport.evaluate(e=>e.contains(document.activeElement)&&!document.activeElement.closest('[inert]')),'Resize keeps focus usable '+tag);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');await viewport.scrollIntoViewIfNeeded();await viewport.focus();await page.keyboard.press('Tab');await page.keyboard.press('Tab');await settle(viewport);
  check(await viewport.evaluate(e=>{const a=document.activeElement.closest('.dz-carousel-slide'),b=e.getBoundingClientRect(),r=a?.getBoundingClientRect();return r&&r.left>=b.left-1&&r.right<=b.right+1&&e.scrollLeft===0;}),'Tab brings partial card into view '+tag);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');await viewport.scrollIntoViewIfNeeded();
  const second=track.locator('[data-certificate-open]').nth(1),expected=await second.getAttribute('data-certificate-title'),r=await second.boundingBox(),box=await viewport.boundingBox();
  await page.mouse.click(Math.min(r.x+8,box.x+box.width-5),r.y+50);
  check(await page.locator('#certificate-viewer').evaluate(e=>e.open)&&await page.locator('#certificate-title').innerText()===expected,'Partial certificate click opens correct document '+tag);
  await page.keyboard.press('Escape');await page.waitForTimeout(300);
  await page.goto(base+'/doctors/rasulov-magomed-radzhabovich');await viewport.scrollIntoViewIfNeeded();
  const start=await viewport.getAttribute('data-carousel-index');const b=await viewport.boundingBox();
  await page.mouse.move(b.x+b.width*.85,b.y+80);await page.mouse.down();await page.mouse.move(b.x+b.width*.1,b.y+85,{steps:12});await page.mouse.up();await settle(viewport);
  check(await viewport.getAttribute('data-carousel-index')!==start,'Horizontal swipe advances '+tag);
  check(!await page.locator('#certificate-viewer').evaluate(e=>e.open),'Swipe does not open document '+tag);
  const moved=await viewport.getAttribute('data-carousel-index');await viewport.hover();await page.mouse.wheel(-400,0);await page.waitForTimeout(100);await settle(viewport);
  check(await viewport.getAttribute('data-carousel-index')!==moved,'Horizontal wheel moves backward '+tag);
  check(await track.locator(':scope > *').count()===16,'No cloned documents '+tag);
  check(!await page.evaluate(()=>document.documentElement.scrollWidth>document.documentElement.clientWidth),'No page overflow '+tag);
 }
 await page.emulateMedia({reducedMotion:'no-preference'});
 return {checks:checks.length,failures:checks.filter(x=>!x.ok),errors:[...new Set(errors)]};
}
