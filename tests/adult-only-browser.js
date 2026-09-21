async(page)=>{
 await page.bringToFront();const failures=[],errors=[];let checks=0;
 const check=(ok,label)=>{checks++;if(!ok)failures.push(label);};
 page.on('pageerror',error=>errors.push(error.message));
 const pediatric=/детск|\bchildren\b|(?<![а-яё])дет(?:и|ей|ям|ьми|ях)(?![а-яё])|(?<![а-яё])реб[её]н|подрост|малыш/iu;
 for(const base of ['http://127.0.0.1:8174','http://domzubov.localhost'])for(const width of [1440,768,360,320]){
  const tag=base+' '+width;await page.setViewportSize({width,height:1000});await page.goto(base);
  await page.evaluate(()=>{localStorage.removeItem('accessibility-settings');sessionStorage.setItem('dz-search-history',JSON.stringify(['Детский стоматолог','стоматолог для ребёнка','брекеты']));});await page.reload();
  check(!pediatric.test(await page.locator('body').innerText()),'Adult home '+tag);
  check(await page.evaluate(()=>JSON.stringify(JSON.parse(sessionStorage.getItem('dz-search-history')))==='["брекеты"]'),'Old pediatric history removed '+tag);
  check(await page.locator('#services-menu [role=tabpanel] a').evaluateAll(es=>new Set(es.map(e=>e.href)).size)===34,'34 adult destinations '+tag);
  const grid=await page.locator('[data-section="2"]>div.flex>div').evaluateAll(es=>es.map(e=>{const r=e.getBoundingClientRect();return {x:r.x,y:r.y,right:r.right,width:r.width};}));
  check(grid.length===6,'Six adult service tiles '+tag);
  if(width>=1101)check(Math.abs(grid[3].y-grid[5].y)<1&&Math.abs(grid[3].x-grid[0].x)<1&&Math.abs(grid[5].right-grid[2].right)<2,'Balanced lower service row '+tag);
  else if(width>=768)check(Math.abs(grid[5].width-grid[0].width)<2,'Full final tablet tile '+tag);
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Home fits '+tag);
  const opener=width>=1280?page.locator('.dz-search-toggle'):width<768?page.locator('.dz-mobile-actions [data-search-open]'):page.locator('.dz-menu-toggle');
  await opener.click();if(width===768)await page.locator('#mobile-menu [data-search-open]').click();
  await page.locator('#site-search-input').fill('детский стоматолог');await page.waitForFunction(()=>!document.querySelector('[data-search-empty]').hidden);
  check(await page.locator('#site-search-results a').count()===0,'No pediatric results '+tag);
  await page.locator('#site-search-input').fill('Марет');await page.waitForFunction(()=>document.querySelector('#site-search-results a')?.getAttribute('href').includes('murtazalieva'));
  check(!pediatric.test(await page.locator('#site-search-results').innerText()),'Adult doctor search '+tag);await page.keyboard.press('Escape');
  await page.goto(base+'/doctors');check(await page.locator('[data-specialty="6"]').count()===0,'No pediatric specialty '+tag);
  await page.locator('[data-doctor-search]').fill('Марет');check(await page.locator('[data-doctor-card]:visible').count()===1,'Maret remains available '+tag);
  await page.goto(base+'/doctors/murtazalieva-maret-akhmedovna');
  check(await page.locator('[data-certificate-open]').count()===6,'Six public certificates '+tag);
  check(!pediatric.test(await page.locator('main').innerText()),'Adult Maret profile '+tag);
  const certificate=page.locator('[data-certificate-open]').first();await certificate.scrollIntoViewIfNeeded();await certificate.click();
  check(await page.locator('#certificate-viewer').evaluate(e=>e.open),'Certificate viewer works '+tag);await page.keyboard.press('Escape');
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Profile fits '+tag);
 }
 return {checks,failures,errors:[...new Set(errors)]};
}
