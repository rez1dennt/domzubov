<?php
declare(strict_types=1);
require __DIR__.'/../app/site-config.php';
require __DIR__.'/../app/security.php';
function vercel_check(bool $ok,string $message):void {if(!$ok){fwrite(STDERR,"FAIL: $message\n");exit(1);}echo "PASS: $message\n";}
$keys=['DZ_SITE_URL','DZ_ALLOW_INDEXING','VERCEL','VERCEL_ENV','VERCEL_URL','VERCEL_BRANCH_URL','VERCEL_PROJECT_PRODUCTION_URL'];$saved=[];
foreach($keys as $key){$saved[$key]=getenv($key);putenv($key);}
try {
 putenv('VERCEL=1');putenv('VERCEL_ENV=production');putenv('VERCEL_URL=domzubov-build123-team.vercel.app');putenv('VERCEL_PROJECT_PRODUCTION_URL=domzubov.vercel.app');putenv('VERCEL_BRANCH_URL=domzubov-git-main-team.vercel.app');
 vercel_check(dz_site_config()['base_url']==='https://domzubov.vercel.app','Production origin detected from Vercel environment');
 vercel_check(!dz_site_config()['allow_indexing'],'Demo remains noindex without explicit opt-in');
 vercel_check(in_array('domzubov-build123-team.vercel.app',dz_allowed_hosts(),true),'Deployment URL allowed');
 vercel_check(in_array('domzubov-git-main-team.vercel.app',dz_allowed_hosts(),true),'Branch URL allowed');
 vercel_check(!in_array('attacker.vercel.app',dz_allowed_hosts(),true),'Other Vercel tenants are not trusted');
 $_SERVER['REMOTE_ADDR']='127.0.0.1';$_SERVER['HTTP_X_REAL_IP']='198.51.100.9';
 vercel_check(dz_client_address()==='198.51.100.9','Vercel proxy clients have separate rate-limit identities');
 $_SERVER['HTTP_X_REAL_IP']='198.51.100.9, attacker';
 vercel_check(dz_client_address()==='127.0.0.1','Malformed platform IP falls back to connection address');
 putenv('DZ_ALLOW_INDEXING=1');putenv('VERCEL_ENV=preview');
 vercel_check(dz_site_config()['base_url']==='https://domzubov-build123-team.vercel.app','Preview uses its own environment URL');
 vercel_check(!dz_site_config()['allow_indexing'],'Preview stays noindex even with indexing enabled');
 putenv('DZ_SITE_URL=https://clinic-live.ru');putenv('VERCEL_ENV=production');
 vercel_check(dz_site_config()['base_url']==='https://clinic-live.ru','Explicit production domain wins');
 vercel_check(in_array('clinic-live.ru',dz_allowed_hosts(),true),'Custom domain allowed');
 $_SERVER['HTTP_HOST']='spoofed.example.org';$_SERVER['HTTP_X_FORWARDED_HOST']='spoofed.example.org';
 vercel_check(dz_site_config()['base_url']==='https://clinic-live.ru','Request headers cannot control origin');
 putenv('VERCEL_PROJECT_PRODUCTION_URL=good.vercel.app/../bad');putenv('VERCEL_BRANCH_URL=bad.vercel.app:443');
 vercel_check(!in_array('good.vercel.app/../bad',dz_allowed_hosts(),true)&&!in_array('bad.vercel.app:443',dz_allowed_hosts(),true),'Invalid system hostnames rejected');
 putenv('VERCEL');putenv('DZ_SITE_URL');
 $_SERVER['HTTP_X_REAL_IP']='198.51.100.9';
 vercel_check(dz_client_address()==='127.0.0.1','Local server ignores spoofed forwarding headers');
 vercel_check(dz_site_config()['base_url']==='http://127.0.0.1:8174','Local origin unchanged outside Vercel');
 vercel_check(!in_array('domzubov-build123-team.vercel.app',dz_allowed_hosts(),true),'System host variables ignored outside Vercel');
}finally{foreach($saved as $key=>$value)putenv($value===false?$key:$key.'='.$value);}
$config=json_decode(file_get_contents(__DIR__.'/../vercel.json'),true,512,JSON_THROW_ON_ERROR);
vercel_check($config['functions']['api/index.php']['runtime']==='vercel-php@0.9.0','PHP 8.5 runtime pinned');
$routes=$config['routes'];
function resolve_vercel_path(array $routes,string $path):?string {foreach($routes as $route){if(!isset($route['dest']))continue;if(preg_match('~^'.$route['src'].'$~D',$path))return preg_replace('~^'.$route['src'].'$~D',$route['dest'],$path);}return null;}
foreach(['/','/services/ustanovka-implanta','/search-api?q=test','/app/company.php','/content/routes.json','/.git/config','/README.md','/assets/documents/document-1.pdf','/assets/../app/company.php'] as $path)vercel_check(resolve_vercel_path($routes,$path)==='/api/index.php','PHP router owns '.$path);
foreach(['/assets/search.js','/assets/images/doctor-karen-badunts.webp','/assets/fonts/russo-one-cyrillic.woff2','/assets/documents/medicines-3867r-2025.pdf'] as $path)vercel_check(resolve_vercel_path($routes,$path)===$path,'CDN serves approved asset '.$path);
