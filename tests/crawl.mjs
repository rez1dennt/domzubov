import fs from 'node:fs';
import path from 'node:path';
const root = path.resolve(import.meta.dirname,'..');
const routes = JSON.parse(fs.readFileSync(path.join(root,'content/routes.json'),'utf8'));
const base = process.argv[2] || 'http://127.0.0.1:8174';
const queue = Object.entries(routes);
const failures = [], missing = new Set(), brokenLinks = new Set();
// Keep auditing every historical route; unpublished pediatric records now return 404.
const withdrawn = new Set([
  'services/adaptation-visit-for-children', 'services/bite-correction',
  'services/germetizacia-fisur-y-detei', 'services/lechenie-detskogo-cariesa-i-ego-osloznenie',
  'services/palatal-expander', 'services/profilaktika-i-diagnostika-detei',
  'akciya-skidka-detskoe-lechenie', 'kids-holidays', 'novogodnyaya-skidka',
  'doctors/nadejda-vladimirovna-serebriakova', 'doctors/viktoriia-aleksandrovna-eremenko',
  'doctors/viktoriia-aleksandrovna-sharii', 'doctors/vladislav--valerevich--liubkin-',
]);
function resolve(url) {
  let key = decodeURIComponent(url.split(/[?#]/)[0]).replace(/^\/+|\/+$/g,'').replace(/\.(?:html|php)$/,'') || 'index';
  return routes[key] || routes[key+'/index'] || ['privacy-policy','chertanovskaya','search','sitemap','doctors/rasulov-magomed-radzhabovich','doctors/murtazalieva-maret-akhmedovna','doctors/badunts-karen-valerievich','doctors/rasulova-yana-borisovna'].includes(key);
}
async function worker() {
  while(queue.length) {
    const [key,record] = queue.shift();
    const response = await fetch(base+'/'+(key==='index'?'':key));
    const html = await response.text();
    const expectedStatus = withdrawn.has(key) ? 404 : 200;
    if(response.status!==expectedStatus || /(?:Fatal error|Warning:|Parse error):/.test(html)) failures.push({key,status:response.status,expectedStatus});
    const fragment = html;
    for(const m of fragment.matchAll(/(?:src|poster)="([^"]+)"|url\(&quot;([^&]+)&quot;\)|url\(["']?([^\)"']+)["']?\)/g)) {
      const url=(m[1]||m[2]||m[3]).replaceAll('&amp;','&');
      if(!url.startsWith('/'))continue;
      const file=decodeURIComponent(url.split('?')[0]);
      if(!fs.existsSync(root+file))missing.add(url);
    }
    for(const m of fragment.matchAll(/href="([^"]+)"/g)) {
      if(!m[1].startsWith('/') || /^\/(assets|belayaraduga\.ru|_external)\//.test(m[1]))continue;
      if(!resolve(m[1]))brokenLinks.add(m[1]);
    }
  }
}
await Promise.all(Array.from({length:5},worker));
for(const invalid of ['/nonexistent','/app/bootstrap.php','/content/routes.json','/%2e%2e/app/bootstrap.php']) {
  const r=await fetch(base+invalid);
  if(r.status!==404)failures.push({invalid,status:r.status});
}
const report={pages:Object.keys(routes).length,failures,missingAssets:[...missing],brokenLinks:[...brokenLinks]};
fs.writeFileSync(path.join(root,'docs/http-audit.json'),JSON.stringify(report,null,2));
console.log(JSON.stringify(report,null,2));
if(failures.length||missing.size||brokenLinks.size)process.exitCode=1;
