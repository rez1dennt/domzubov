const statuses=[];let retry=null;
for(let i=0;i<35;i++){
 const response=await fetch('http://127.0.0.1:8174/'+(i%2?'search':'search-api')+'?q='+encodeURIComponent('зуб'));
 statuses.push(response.status);if(response.status===429)retry=response.headers.get('retry-after');await response.arrayBuffer();
}
if(!statuses.includes(429)||statuses.some(s=>![200,429].includes(s))||!Number(retry))throw new Error(JSON.stringify({statuses,retry}));
console.log(JSON.stringify({checks:35,limited:statuses.filter(s=>s===429).length,retryAfter:Number(retry)}));
