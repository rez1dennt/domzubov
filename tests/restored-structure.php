<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';require __DIR__.'/../app/presentation.php';
ob_start();render_original('index');$html=ob_get_clean();$doc=Dom\HTMLDocument::createFromString('<!doctype html><html><body>'.$html.'</body></html>',LIBXML_NOERROR);
$sections=$doc->querySelectorAll('body > [data-section]');assert($sections->length===10);
$checks=['original home sections'=>$sections->length===10,'original bento cards'=>$doc->querySelectorAll('[data-section="2"] > div.flex > div')->length===7,'clinic photo mosaic'=>$doc->querySelectorAll('[data-section="3"] img')->length===8,'technology gallery'=>$doc->querySelectorAll('[data-section="4"] img')->length===7,'four named doctors'=>$doc->querySelectorAll('[data-team-card]')->length===4,'one clinic address'=>$doc->querySelectorAll('[data-clinic-tab]')->length===1,'same map layout'=>$doc->querySelectorAll('[data-clinic-map]')->length===1,'reviews four cards layout'=>str_contains($html,'data-review-page-size="4"'),'no old brand'=>!preg_match('/belayaraduga|Бел[а-яё]*\s+Радуг/ui',$html),'no old phone'=>!str_contains($html,'495) 132')];
foreach($checks as $key=>$pass)echo ($pass?'PASS':'FAIL').': '.$key."\n";exit(in_array(false,$checks,true)?1:0);
