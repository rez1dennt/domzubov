import assert from 'node:assert/strict';
const base='http://127.0.0.1:8174';
for(const path of ['/','/contacts','/services/otbelivanie-zubov','/doctors','/reviews','/prices']){
 const response=await fetch(base+path);const html=await response.text();
 assert.equal(response.status,200,path);
 assert(!/belayaraduga|belradbot|9705103146|74951323103|Бел[а-яё]*\s+Радуг/iu.test(html),`Foreign brand: ${path}`);
 assert(html.includes('https://idotvip.ru/domzubov'),`Booking: ${path}`);
 assert(html.includes('Domzubov777@yandex.ru'),`Email: ${path}`);
}
console.log('Brand, contacts and booking checks passed');
