<?php
declare(strict_types=1);
require_once __DIR__.'/app/security.php';
dz_security_boot();
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/');
$valid=!str_contains($path,"\0")&&!str_contains($path,'\\')&&!preg_match('~(?:^|/)\.\.(?:/|$)~',$path);
if(!$valid || preg_match('~(?:^|/)\.|^/(?:app|content|templates|tools|tests|docs|tmp|var|vendor|node_modules|belayaraduga\.ru|_external)(?:/|$)~i',$path)){http_response_code(404);header('Content-Type: text/plain; charset=UTF-8');echo 'Страница не найдена';return;}
$public=['content-polish.css','foundation.css','search.css','search.js','phone-mask.js','polish.css','polish.js','layout.css','restoration.css','restored.js','doctors.js','theme.css','navigation.css','accessibility.css','editorial.css','site.js','navigation.js','accessibility.js','experience.js','mark.svg','brand.svg'];
$isAsset=in_array(ltrim(substr($path,8),'/'),$public,true)&&str_starts_with($path,'/assets/');
$isAsset=$isAsset || preg_match('~^/assets/(?:images/[a-z0-9-]+\.(?:webp|svg)|fonts/[a-z0-9-]+\.(?:woff2|css))$~',$path);
$isAsset=$isAsset || in_array($path,['/assets/legal.css','/assets/form-guard.js'],true) || preg_match('~^/assets/documents/(?:paid-medical-services-659-2026|medical-website-118n-2025|medicines-3867r-2025|personal-data-amendment-156fz-2025)\.pdf$~',$path);
if($isAsset&&is_file(__DIR__.$path))dz_serve_asset(__DIR__.$path);
if((in_array($path,['/assets/doctor-profiles.css','/assets/doctor-profiles.js'],true)||preg_match('~^/assets/documents/maret-(?:diploma|apexogenesis|periodontitis|endodontics|accreditation)\.pdf$~',$path))&&is_file(__DIR__.$path))dz_serve_asset(__DIR__.$path);
require __DIR__.'/index.php';
