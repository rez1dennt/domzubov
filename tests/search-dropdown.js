async(page)=>{
 await page.bringToFront();
 const base='http://127.0.0.1:8174',checks=[],errors=[];
 const check=(ok,name)=>checks.push({ok,name});page.on('pageerror',e=>errors.push(e.message));
 for(const width of [1440,1100,768,360,320]){
  await page.setViewportSize({width,height:900});await page.goto(base);
  await page.evaluate(()=>{sessionStorage.removeItem('dz-search-history');scrollTo({top:600,behavior:'instant'});});
  const opener=width>=1100?page.locator('.dz-search-toggle'):width<768?page.locator('.dz-mobile-actions [data-search-open]'):page.locator('#mobile-menu [data-search-open]'); const open=async()=>{if(width>=768&&width<1100)await page.locator('.dz-menu-toggle').click();await opener.click();await page.waitForTimeout(500);};
  const y=await page.evaluate(()=>scrollY);await open();await page.waitForTimeout(280);
  check(await page.locator('dialog#site-search').count()===0,'No modal '+width);
  if(await page.locator('dialog#site-search').count())return {checks,failures:checks.filter(x=>!x.ok),errors};
  const input=page.locator('#site-search-input'),panel=page.locator('#site-search-suggestions');
  check(await panel.isVisible(),'Dropdown opens '+width);
  check(await page.evaluate(()=>scrollY)===y,'Open keeps page position '+width);
  check(await panel.evaluate(e=>{const r=e.getBoundingClientRect();return r.left>=0&&r.right<=innerWidth&&r.bottom<=innerHeight;}),'Dropdown fits '+width);
  await input.fill('болит зуб');await page.waitForFunction(()=>document.querySelector('#site-search-results a')?.getAttribute('href').includes('lechenie-kariesa'));
  check((await page.locator('#site-search-results a').first().getAttribute('href')).includes('lechenie-kariesa'),'Keyword results '+width);
  await page.keyboard.press('Escape');await page.waitForTimeout(280);
  check(!await panel.isVisible(),'Escape closes '+width);
  check(await page.evaluate(()=>scrollY)===y,'Close keeps page position '+width);
  await open();await input.fill('брекиты');await page.waitForFunction(()=>document.querySelector('#site-search-results a')?.getAttribute('href').includes('prikusa'));
  await input.press('ArrowDown');await input.press('Enter');await page.waitForURL('**/services/**');
  check(page.url().includes('prikusa'),'Keyboard navigation '+width);
  await open();check(await page.locator('[data-search-history-list]').innerText().then(t=>t.includes('брекиты')),'History persists '+width);
  await page.locator('[data-history-remove]').first().click();check(await page.locator('[data-search-history-list] [data-search-query]').count()===0,'History removal '+width);
  await page.mouse.click(width-30,740);await page.waitForTimeout(280);check(!await panel.isVisible(),'Outside click '+width);
 }
 return {checks:checks.length,failures:checks.filter(x=>!x.ok),errors:[...new Set(errors)]};
}
