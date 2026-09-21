<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
$html=file_get_contents('http://127.0.0.1:8174/');
$checks=[
  'Service mega menu'=>str_contains($html,'id="services-menu"'),
  'Patient panel'=>str_contains($html,'id="patients-menu"'),
  'Mobile action bar'=>str_contains($html,'class="dz-mobile-actions"'),
  'Accessibility settings'=>str_contains($html,'id="accessibility-settings"'),
  'Clinic chooser removed from header'=>!str_contains(explode('</header>',$html)[0],'Выбрать клинику'),
  'Local Russo One font'=>is_file(PROJECT_ROOT.'/assets/fonts/russo-one-cyrillic.woff2'),
];
$menu=json_decode(file_get_contents(PROJECT_ROOT.'/content/service-menu.json'),true);
$links=[];foreach($menu['categories'] as $category)foreach($category['items']??[] as $item)$links[]=$item['link'];
$checks['All 34 adult service destinations']=count(array_unique($links))===34;
foreach(array_unique($links) as $link)$checks['Service route '.$link]=resolve_route($link)!==null;
foreach($checks as $name=>$ok)echo ($ok?'PASS':'FAIL').': '.$name."\n";
exit(in_array(false,$checks,true)?1:0);
