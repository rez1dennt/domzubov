<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/presentation.php';
$failures=[];$checks=0;
$check=static function(bool $ok,string $label)use(&$failures,&$checks){$checks++;if(!$ok)$failures[]=$label;};
$render=static function(string $route){ob_start();render_original($route);return Dom\HTMLDocument::createFromString('<!doctype html><body>'.ob_get_clean(),LIBXML_NOERROR);};
$about=$render('o-nas');
$check($about->querySelectorAll('.dz-about-team-grid .dz-real-doctor-card')->length===4,'About contains four real doctors');
$check(!preg_match('/75|Главный врач/u',$about->body->textContent),'About contains no imported staff claims');
$check($about->querySelectorAll('.dz-about-approach-quote')->length===1,'About approach uses flow layout');
$home=$render('index');
$check($home->querySelectorAll('.dz-team-controls')->length===1,'Home team controls annotated');
$check($home->querySelectorAll('.dz-hero-secondary')->length===1,'Home price action annotated');
$check($home->querySelectorAll('[data-review-status]:not([hidden])')->length===0,'Review counters hidden');
foreach($home->querySelectorAll('button[data-team-prev],button[data-team-next]') as $button){
 $check($button->querySelector('svg')->getAttribute('viewBox')==='0 0 24 24','Team arrow uses normalized viewBox');
 $check($button->querySelector('path')->getAttribute('d')==='M9 6l6 6-6 6','Team arrow centered in viewBox');
}
$known=array_column(doctor_data()['doctors'],'url');
$expected=['services/lechenie-kariesa'=>'murtazalieva-maret-akhmedovna','services/ustanovka-implanta'=>'badunts-karen-valerievich','services/keramicheskie-viniry'=>'rasulov-magomed-radzhabovich','services/ispravlenie-prikusa'=>'rasulova-yana-borisovna'];
foreach(routes() as $route=>$meta){
 if(!str_starts_with($route,'services/'))continue;
 $doc=$render($route);
 foreach($doc->querySelectorAll('div,h2,h3') as $heading){
  if($heading->childElementCount || trim($heading->textContent)!=='Наши специалисты')continue;
  $cards=$heading->parentElement->querySelectorAll('.dz-real-doctor-card');
  $check($cards->length>0,$route.' specialists populated');
  foreach($cards as $card)$check(in_array($card->querySelector('a')->getAttribute('href'),$known,true),$route.' real profile only');
  if(isset($expected[$route]))$check($cards->length===1&&str_contains($cards->item(0)->querySelector('a')->getAttribute('href'),$expected[$route]),$route.' relevant specialty');
  if($route==='services/sedation-propofol')$check($cards->length===4,'Ambiguous route retains all four profiles');
  if($route==='services/zuby-za-odin-den')$check($cards->length===2&&str_contains($cards->item(0)->querySelector('a')->getAttribute('href'),'badunts-karen-valerievich'),'Implant restoration shows surgeon before orthopedist');
 }
 foreach($doc->querySelectorAll('[class*="max-w-[750px]"]') as $copy){
  $box=$copy->parentElement?->parentElement?->parentElement;
  $check(!$box||!str_contains($box->getAttribute('class')??'','rounded-[30px]'),$route.' imported quote removed');
 }
 if(isset($expected[$route]))$check($doc->querySelectorAll('.dz-clinic-quote')->length===2,$route.' clinic explanations replace quotes');
 foreach($doc->querySelectorAll('[data-feature-grid]') as $grid){
  $icons=[];foreach($grid->querySelectorAll('[data-feature-card]') as $card){$icon=$card->querySelector('[data-dz-icon]');$check($icon!==null,$route.' semantic feature icon');if($icon)$icons[]=$icon->getAttribute('data-dz-icon');}
  $check(count($icons)===count(array_unique($icons)),$route.' distinct feature icons');
 }
}
echo json_encode(['checks'=>$checks,'failures'=>$failures],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
exit($failures?1:0);
