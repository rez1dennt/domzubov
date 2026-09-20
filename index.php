<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/app/security.php';
dz_security_boot();
require_once __DIR__.'/app/seo.php';
require __DIR__.'/app/search.php';
$route=resolve_route($_SERVER['REQUEST_URI']??'/');
$path=trim(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH),'/');
if($path==='robots.txt'){header('Content-Type: text/plain; charset=UTF-8');echo dz_seo_robots();exit;}
if($path==='sitemap.xml'){header('Content-Type: application/xml; charset=UTF-8');header('X-Robots-Tag: noindex');echo dz_seo_sitemap_xml();exit;}
if($path==='search-api'){dz_guard_search();header('Content-Type: application/json; charset=UTF-8');echo json_encode(dz_search_api_payload(dz_search_query($_GET['q']??'')),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;}
if($path==='search'){dz_guard_search(false);$route='search';}
if(in_array($path,['privacy-policy','sitemap'],true))$route=$path;
$doctor=doctor_profile($route);
if(!$doctor && $route && (str_starts_with($route,'doctors/') || str_ends_with($route,'/doctors'))){header('Location: /doctors',true,301);exit;}
if($route && in_array($route,['dinamo','hamovniki','zilart'],true)){header('Location: /contacts',true,301);exit;}
if($route && (routes()[$route]['document']??false)){header('Location: /yuridicheskaya-informatsiya',true,301);exit;}
if($route && in_array($route,dz_seo_routes(),true)){
    $canonicalPath=$route==='index'?'/':'/'.$route;
    if((parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)?:'/')!==$canonicalPath){header('Location: '.$canonicalPath,true,301);exit;}
}
$service=editorial_services()[$route??'']??null;
$editorial=editorial_pages()[$route??'']??editorial_pages()['/'.($route??'')]??null;
$titles=['index'=>'Дом Зубов — стоматология в Москве','contacts'=>'Контакты','doctors'=>'Врачи','reviews'=>'Отзывы','prices'=>'Стоимость лечения','services'=>'Услуги стоматологии','yuridicheskaya-informatsiya'=>'Юридическая информация','sitemap'=>'Карта сайта','search'=>'Поиск по сайту','privacy-policy'=>'Конфиденциальность'];
$title=$titles[$route??'']??$service['title']??$editorial['title']??preg_replace('/\s*[—–].*$/u','',routes()[$route??'']['title']??'Страница не найдена');
$page=['title'=>$title.($route==='index'?'':' — Дом Зубов'),'description'=>$service['lead']??$editorial['lead']??'Стоматология «Дом Зубов» в Москве. Услуги, стоимость лечения, адрес и онлайн-запись.'];
$seo=dz_seo_prepare($route,$page,$service,$editorial);
header('X-Robots-Tag: '.$seo['robots']);
if($route===null)http_response_code(404);
header('Content-Type: text/html; charset=UTF-8');header('X-Content-Type-Options: nosniff');header('Referrer-Policy: strict-origin-when-cross-origin');
$restored=in_array($route,['index','services','prices','o-nas','gift-certificates','programma-blagodarnosti','pravila-zapisi','tax-deduction'],true)||str_starts_with($route??'','services/');
require __DIR__.'/templates/header.php';
?>
<main id="main" class="dz-main <?= $route==='index'?'dz-home':'dz-inner' ?>" data-route="<?= e($route??'404') ?>">
<?php if($route==='search'){require_once __DIR__.'/templates/search.php';echo dz_search_page_template(dz_search_query($_GET['q']??''),dz_search(dz_search_query($_GET['q']??''),50));}elseif($restored){require_once __DIR__.'/app/presentation.php';render_original($route);}elseif(in_array($route,['contacts','taganka','chertanovskaya'],true)){require __DIR__.'/templates/restored-contacts.php';}elseif($doctor){require __DIR__.'/templates/doctor-profile.php';}elseif($route==='doctors'){require __DIR__.'/templates/doctors.php';}elseif($route==='reviews'){require __DIR__.'/templates/restored-reviews.php';}else require __DIR__.'/templates/content.php'; ?>
</main>
<?php require __DIR__.'/templates/footer.php'; ?>
