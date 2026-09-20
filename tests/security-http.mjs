import assert from 'node:assert/strict';
import http from 'node:http';
const base=process.env.TEST_BASE||'http://127.0.0.1:8174';
const failures=[];let checks=0;
const check=(value,name)=>{checks++;if(!value)failures.push(name);};
for(const route of ['/','/services/lechenie-kariesa','/search-api?q=зуб','/no-such-page']){
 const response=await fetch(base+route);
 check(response.headers.get('content-security-policy')?.includes("script-src 'self'"),'CSP '+route);
 check(response.headers.get('x-frame-options')==='DENY','Frame protection '+route);
 check(response.headers.get('x-content-type-options')==='nosniff','No sniff '+route);
 check(!response.headers.get('x-powered-by'),'No PHP version '+route);
}
for(const method of ['POST','PUT','PATCH','DELETE','METHOD_OVERRIDE']){
 const response=await fetch(base+'/',{method:method==='METHOD_OVERRIDE'?'POST':method,headers:method==='METHOD_OVERRIDE'?{'X-HTTP-Method-Override':'TRACE'}:{}});
 check(response.status===405,'Reject method '+method);
}
for(const route of ['/app/security.php','/.env','/tmp/test.txt','/.git/config','/assets/documents/document-1.pdf','/%252e%252e/app/bootstrap.php'])check((await fetch(base+route)).status===404,'Private path '+route);
const api=await fetch(base+'/search-api?q=x',{headers:{'Sec-Fetch-Site':'cross-site'}});check(api.status===403,'Cross-site API rejected');
const long=await fetch(base+'/search-api?q='+encodeURIComponent('зуб '.repeat(100)));check(long.status===400,'Oversized search rejected');
const host=await new Promise((resolve,reject)=>{http.get(base+'/',{headers:{Host:'attacker.invalid'}},response=>{response.resume();resolve(response.statusCode);}).on('error',reject);});check(host===421,'Unknown Host rejected');
check((await fetch(base+'/search?q%5B%5D=x')).status===400,'Array query rejected safely');
check((await fetch(base+'/search-api?q=%FF')).status===400,'Invalid UTF-8 rejected');
check((await fetch(base+'/assets/search.js',{method:'POST'})).status===405,'Static assets reject POST');
const script=await fetch(base+'/assets/search.js');check(script.headers.get('x-frame-options')==='DENY','Static asset header');
const form=await (await fetch(base+'/')).text();check(/data-contact-form[^>]*method="post"|method="post"[^>]*data-contact-form/.test(form),'Forms do not leak PII via GET');
check(form.includes('data-form-honeypot'),'Honeypot exists');
console.log(JSON.stringify({checks,failures},null,2));if(failures.length)process.exitCode=1;
