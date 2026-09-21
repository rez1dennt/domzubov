import assert from 'node:assert/strict';

const base = (process.argv[2] || 'http://127.0.0.1:8174').replace(/\/$/, '');
// Independent publication contract: do not derive this list from the application filter.
export const withdrawnRoutes = [
  'services/adaptation-visit-for-children',
  'services/bite-correction',
  'services/germetizacia-fisur-y-detei',
  'services/lechenie-detskogo-cariesa-i-ego-osloznenie',
  'services/palatal-expander',
  'services/profilaktika-i-diagnostika-detei',
  'services/professionalnaya-gigiena-dlya-detej-i-podrostkov',
  'akciya-skidka-detskoe-lechenie', 'kids-holidays', 'novogodnyaya-skidka',
  'doctors/nadejda-vladimirovna-serebriakova',
  'doctors/viktoriia-aleksandrovna-eremenko',
  'doctors/viktoriia-aleksandrovna-sharii',
  'doctors/vladislav--valerevich--liubkin-',
];
const childTerms = /детск|(?<![а-яё])дет(?:и|ей|ям|ьми|ях)(?![а-яё])|(?<![а-яё])реб[её]н|подрост|малыш|несовершеннолет|школьник|молочн\S*\s+(?:зуб|прикус)|сменн\S*\s+прикус|(?:пациент(?:ы|а|ам|ов)?|возраст(?:е|а)?|с|от)\s+(?:[1-9]|1[0-7])\s+(?:лет|года?)(?![а-яё])/iu;
const childImages = /(?:child-(?:visit|hero|play|care)|doctor-cert-maret-apexogenesis)/i;
const failures = [];
let checks = 0;
const check = (condition, label) => { checks++; if (!condition) failures.push(label); };
const normalizedPath = value => decodeURIComponent(new URL(value, base).pathname).replace(/^\/+|\/+$/g, '').replace(/\.(?:html|php)$/i, '').replace(/\/index$/, '');
const decodeText = value => value.replace(/&#(x[\da-f]+|\d+);/gi, (_, code) => String.fromCodePoint(code[0].toLowerCase() === 'x' ? parseInt(code.slice(1), 16) : Number(code))).replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&');

const sitemapResponse = await fetch(base + '/sitemap.xml');
assert.equal(sitemapResponse.status, 200, 'Sitemap is available');
const xml = await sitemapResponse.text();
const urls = [...xml.matchAll(/<loc>([^<]+)<\/loc>/g)].map(match => match[1]);
check(urls.length === 54, '54 published canonical pages');
check(new Set(urls).size === 54, 'Canonical pages are unique');
check(urls.filter(url => new URL(url).pathname.startsWith('/services/')).length === 34, '34 published adult services');

for (const canonical of urls) {
  const path = new URL(canonical).pathname;
  const response = await fetch(base + path, {redirect: 'manual'});
  check(response.status === 200, `${path}: HTTP 200`);
  const html = await response.text();
  check(!/Fatal error|Warning:|Deprecated:|Parse error:/.test(html), `${path}: no runtime errors`);
  const publicMarkup = html.replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, '');
  const text = decodeText(publicMarkup.replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ');
  const textMatch = text.match(childTerms);
  check(!textMatch, `${path}: pediatric public text ${textMatch?.[0] || ''}`);
  for (const match of publicMarkup.matchAll(/(?:alt|title|aria-label|content)="([^"]*)"/gi)) {
    check(!childTerms.test(decodeText(match[1])), `${path}: pediatric metadata or accessibility label ${match[1].slice(0, 120)}`);
  }
  for (const match of html.matchAll(/<script\b[^>]*type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi)) {
    check(!childTerms.test(JSON.stringify(JSON.parse(match[1]))), `${path}: pediatric structured data`);
  }
  for (const match of html.matchAll(/(?:href|src)="([^"]*)"/gi)) {
    const value = decodeText(match[1]);
    if (!value.startsWith('/') && !value.startsWith(base)) continue;
    check(!withdrawnRoutes.includes(normalizedPath(value)), `${path}: link to unpublished route ${value}`);
    check(!childImages.test(value), `${path}: pediatric image or certificate ${value}`);
  }
}

let blockedVariants = 0;
for (const route of withdrawnRoutes) {
  check(!urls.some(url => normalizedPath(url) === route), `${route}: absent from XML sitemap`);
  for (const suffix of ['', '/', '.html', '.php', '/index', '/index.html']) {
    const path = '/' + route + suffix;
    const response = await fetch(base + path, {redirect: 'manual'});
    check(response.status === 404, `${path}: unpublished route returns 404, received ${response.status}`);
    const html = await response.text();
    check(!html.includes('rel="canonical"'), `${path}: no canonical for unpublished page`);
    blockedVariants++;
  }
}

for (const query of ['детский стоматолог', 'стоматолог для ребёнка', 'лечение подростков', 'молочные зубы']) {
  const response = await fetch(base + '/search-api?q=' + encodeURIComponent(query));
  check(response.status === 200, `Search API available: ${query}`);
  const payload = await response.json();
  check(Array.isArray(payload.results) && payload.results.length === 0, `No unavailable pediatric services suggested: ${query}`);
}
for (const query of ['Марет', 'брекеты', 'болит зуб']) {
  const response = await fetch(base + '/search-api?q=' + encodeURIComponent(query));
  check(response.status === 200, `Adult search API available: ${query}`);
  const payload = await response.json();
  check(payload.results.length > 0, `Adult search still returns results: ${query}`);
  for (const result of payload.results) {
    check(!childTerms.test(result.title + ' ' + result.description), `No pediatric claim in result: ${result.url}`);
    check(!withdrawnRoutes.includes(normalizedPath(result.url)), `No unpublished search result: ${result.url}`);
  }
}

console.log(JSON.stringify({pages: urls.length, blockedVariants, checks, failures}, null, 2));
if (failures.length) process.exitCode = 1;
