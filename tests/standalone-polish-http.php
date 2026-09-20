<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
$base='http://127.0.0.1:8174';$failures=[];$checks=0;
$check=static function(bool $ok,string $label)use(&$failures,&$checks){$checks++;if(!$ok)$failures[]=$label;};
$fetch=static function(string $path)use($base){$html=file_get_contents($base.$path);return [$html,http_get_last_response_headers()];};
foreach(array_merge(['/','/o-nas'],array_column(doctor_data()['doctors'],'url')) as $path){
 [$html,$headers]=$fetch($path);$check(str_contains($headers[0],'200'),$path.' 200');
 $check(!preg_match('/Fatal error|Warning:|Deprecated:/',$html),$path.' no runtime errors');
 $check(str_contains($html,'/assets/content-polish.css?'),$path.' final CSS loaded');
}
[$css,$headers]=$fetch('/assets/content-polish.css');
$check(str_contains(implode(' ',$headers),'text/css')&&str_contains($css,'.dz-service-specialists'), 'CSS served as stylesheet');
$check((bool)preg_match('/svg\[data-dz-icon\][^{]*\{[^}]*width:24px;height:24px/s',$css),'Semantic icons use compact size');
$check(str_contains(file_get_contents(PROJECT_ROOT.'/.htaccess'),'content-polish\\.css'),'Apache allows final stylesheet');
foreach(routes() as $route=>$meta){
 if(!str_starts_with($route,'services/'))continue;
 [$html,$headers]=$fetch('/'.$route);
 $check(str_contains($headers[0],'200'),$route.' 200');
 $doc=Dom\HTMLDocument::createFromString($html,LIBXML_NOERROR);
 foreach($doc->querySelectorAll('[data-service-specialists]') as $grid)$check($grid->querySelectorAll('.dz-real-doctor-card')->length>0,$route.' live specialists');
}
echo json_encode(['checks'=>$checks,'failures'=>$failures],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
exit($failures?1:0);
