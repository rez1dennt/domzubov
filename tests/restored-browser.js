async(page)=>{
 const base='http://127.0.0.1:8174';
 await page.goto(base);await page.evaluate(()=>localStorage.removeItem('accessibility-settings'));await page.reload();
 const routes=await page.locator('#services-menu [role=tabpanel] a').evaluateAll(es=>[...new Set(es.map(e=>e.getAttribute('href')))]);
 routes.push('/','/services','/o-nas','/prices','/doctors','/doctors/rasulov-magomed-radzhabovich','/doctors/murtazalieva-maret-akhmedovna','/doctors/badunts-karen-valerievich','/doctors/rasulova-yana-borisovna','/reviews','/contacts','/taganka','/chertanovskaya','/insurance-companies','/gift-certificates','/programma-blagodarnosti','/pravila-zapisi','/tax-deduction','/yuridicheskaya-informatsiya','/privacy-policy','/sitemap','/search?q=%D0%B1%D0%BE%D0%BB%D0%B8%D1%82');
 const failures=[],errors=[],resources=[];page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.url().startsWith(base+'/assets/')&&r.status()>=400)resources.push({url:r.url(),status:r.status()});});
 let checks=0;
 for(const width of [1280,768,360,320]){
  await page.setViewportSize({width,height:900});
  for(const route of routes){
   const response=await page.goto(base+route,{waitUntil:'domcontentloaded'});await page.evaluate(()=>document.fonts.ready);await page.waitForTimeout(25);
   const state=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>document.documentElement.clientWidth,h1:document.querySelectorAll('h1').length,warning:/Fatal error|Deprecated:|Warning:/.test(document.body.innerText),foreign:/belayaraduga|Бел[а-яё]*\s+Радуг/i.test(document.documentElement.outerHTML),wide:[...document.querySelectorAll('main *')].filter(e=>e.getBoundingClientRect().right>innerWidth+2&&!e.closest('[data-gallery-track]')).slice(0,3).map(e=>e.className)}));
   if(response.status()!==200||state.overflow||state.h1!==1||state.warning||state.foreign)failures.push({route,width,status:response.status(),...state});checks++;
  }
 }
 const report={routes:routes.length,checks,failures,errors:[...new Set(errors)],resources};return report;
}
