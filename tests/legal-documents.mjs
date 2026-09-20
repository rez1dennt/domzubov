import fs from 'node:fs';
import crypto from 'node:crypto';
import assert from 'node:assert/strict';
const base='http://127.0.0.1:8174';
const data=JSON.parse(fs.readFileSync('content/legal-resources.json','utf8'));
const page=await fetch(base+'/yuridicheskaya-informatsiya');const html=await page.text();
assert.equal(page.status,200);assert(html.includes('Документ готовится к размещению'));
assert(html.includes('9727136018'));assert(!/Бел[а-яё]*\s+Радуг|document-\d+\.pdf/i.test(html));
let downloads=0;
for(const doc of data.documents){
 if(!doc.local_path)continue;
 const original=fs.readFileSync(doc.local_path);
 assert.equal(crypto.createHash('sha256').update(original).digest('hex'),doc.sha256);
 const response=await fetch(base+'/'+doc.local_path);
 assert.equal(response.status,200);assert(response.headers.get('content-type').includes('pdf'));
 const downloaded=Buffer.from(await response.arrayBuffer());assert(downloaded.equals(original));
 assert(html.includes(doc.local_path));downloads++;
}
assert.equal(downloads,4);
for(const blocked of ['/assets/documents/document-1.pdf','/docs/legal-templates.md','/content/legal-resources.json'])assert.equal((await fetch(base+blocked)).status,404,blocked);
const map=await fetch(base+'/sitemap');assert.equal(map.status,200);const mapText=await map.text();assert(mapText.includes('Карта сайта'));assert(mapText.includes('/privacy-policy'));
console.log(`PASS: ${downloads} authentic downloads, hashes, licence placeholder, sitemap and private files`);
