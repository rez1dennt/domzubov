<?php
require __DIR__.'/../app/bootstrap.php';require __DIR__.'/../app/presentation.php';require __DIR__.'/../app/seo.php';
function clinic_check(bool $ok,string $label):void{if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}}
clinic_check(count(company_locations())===1,'Exactly one clinic');
clinic_check(str_contains(company_locations()[0]['address'],'Симферопольский'),'Correct address');
foreach(['index','o-nas','programma-blagodarnosti','services/lechenie-kariesa'] as $route){
 ob_start();render_original($route);$html=ob_get_clean();clinic_check(!preg_match('/Чертанов|Гончарн|Таганск|по двум адресам/u',$html),'No obsolete clinic data '.$route);
 if($route==='index'){clinic_check(str_contains($html,'09:00–21:00'),'Hours visible');clinic_check(str_contains($html,'по ДМС и ОМС не ведётся'),'Insurance notice');clinic_check(!str_contains($html,'data-dms-card'),'No insurance partners');}
}
foreach(dz_seo_routes() as $route){$seo=dz_seo_prepare($route);clinic_check(!preg_match('/Чертанов|Гончарн|Таганск/u',json_encode($seo,JSON_UNESCAPED_UNICODE)),'Clean metadata '.$route);clinic_check(count(array_filter($seo['schema']['@graph'],fn($n)=>$n['@type']==='Place'))===1,'One structured place');}
echo "PASS: single address, hours, insurance status, page content and structured data\n";
