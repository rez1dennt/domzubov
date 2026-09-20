import assert from 'node:assert/strict';
const base='http://127.0.0.1:8174';
const xmlResponse=await fetch(base+'/sitemap.xml');assert.equal(xmlResponse.status,200);assert(xmlResponse.headers.get('content-type').includes('application/xml'));
const xml=await xmlResponse.text(),urls=[...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(m=>m[1]);assert.equal(urls.length,60);
const titles=new Set(),descriptions=new Set();
for(const url of urls){
 const response=await fetch(url);assert.equal(response.status,200,url);assert.equal(response.url,url);
 const html=await response.text();
 assert.equal([...html.matchAll(/rel="canonical"/g)].length,1,url);
 assert(html.includes(`rel="canonical" href="${url}"`));assert(html.includes('content="noindex, nofollow"'));
 const title=html.match(/<title>(.*?)<\/title>/s)?.[1],description=html.match(/name="description" content="([^"]*)"/)?.[1];
 assert(title&&description);assert(!titles.has(title),url);assert(!descriptions.has(description),url);titles.add(title);descriptions.add(description);
 const schema=JSON.parse(html.match(/<script type="application\/ld\+json">(.*?)<\/script>/s)[1]);assert.equal(schema['@context'],'https://schema.org');assert(schema['@graph'].some(e=>e['@type']==='WebPage'&&e.url===url));
 assert(!/aggregateRating|Бел[а-яё]*\s+Радуг|belayaraduga/i.test(JSON.stringify(schema)));
}
for(const [from,to] of [['/index.php','/'],['/services/lechenie-kariesa.html','/services/lechenie-kariesa'],['/services/implantaciya-zubov','/services/ustanovka-implanta'],['/politika-konfidentsialnosti','/privacy-policy'],['/karta-sajta','/sitemap']]){
 const response=await fetch(base+from,{redirect:'manual'});assert.equal(response.status,301,from);assert.equal(response.headers.get('location'),to);
}
const robots=await (await fetch(base+'/robots.txt')).text();assert(robots.includes('Disallow: /'));assert(!robots.includes('Sitemap:'));
console.log('PASS: 60 live canonical pages, unique metadata, JSON-LD, XML, staged robots and 301 redirects');
