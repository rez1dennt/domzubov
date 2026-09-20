<?php
declare(strict_types=1);
require_once __DIR__.'/home-copy.php';
require_once __DIR__.'/service-copy.php';
require_once __DIR__.'/secondary-copy.php';
require_once __DIR__.'/visual-components.php';
require_once __DIR__.'/polish-content.php';
function dz_fragment(Dom\HTMLDocument $doc,string $html): Dom\DocumentFragment {$fragment=$doc->createDocumentFragment();$temp=Dom\HTMLDocument::createFromString('<!doctype html><html><body>'.$html.'</body></html>',LIBXML_NOERROR);foreach(iterator_to_array($temp->body->childNodes) as $node)$fragment->appendChild($doc->importNode($node,true));return $fragment;}
function dz_replace_text(Dom\Node $node,array $replacements):void {foreach($node->childNodes as $child){if($child instanceof Dom\Text)$child->data=strtr($child->data,$replacements);else dz_replace_text($child,$replacements);}}
function dz_asset(string $original,string $route,int $index=0):string {
 if(str_contains($original,'/assets/images/')||in_array($original,['/assets/brand.svg','/assets/mark.svg'],true))return $original;
 if(str_contains($original,'adaptation-visit/section1'))return '/assets/images/child-hero-v2.webp';
 if(preg_match('~adaptation-visit/definition([123])~',$original,$m))return '/assets/images/'.['child-play-v2','child-care-v2','child-visit'][(int)$m[1]-1].'.webp';
 if(str_contains($original,'adaptation-visit/stages'))return '/assets/images/child-play-v2.webp';
 if(preg_match('~quote.?photo|/woman\.|2Fwoman\.|Doctor[ _-]~i',$original))return '/assets/images/doctor-placeholder.svg';
 if(preg_match('~doctor|vrach|founder|gazar~i',$original))return '/assets/images/doctor-placeholder.svg';
 if(preg_match('~icons|icon|sensibility|disabled~i',$original))return '/assets/images/tooth-icon.svg';
 if(str_contains($original,'insurance-companies'))return '/assets/images/insurance-placeholder.svg';
 if(preg_match('~before|after|do-posle|result|case~i',$original))return '/assets/images/case-placeholder.svg';
 if(preg_match('~section1|hero-dom|reception~i',$original)){
  if(str_starts_with($route,'services/')){
   if(preg_match('~dete|child|detsk~i',$route))return '/assets/images/child-hero-v2.webp';
   if(preg_match('~implant|sinus|kost~i',$route))return '/assets/images/implant.webp';
   if(preg_match('~prikus|breket|aligner|vinir~i',$route))return '/assets/images/orthodontics.webp';
   if(preg_match('~diagnost|microscop~i',$route))return '/assets/images/technology.webp';
   return '/assets/images/consult-adult.webp';
  }
  return '/assets/images/reception.webp';
 }
 if(preg_match('~section2|dms|care|dars~i',$original))return '/assets/images/consult-woman.webp';
 if(preg_match('~2Ft[1-6]~',$original))return ['/assets/images/technology.webp','/assets/images/treatment-room.webp','/assets/images/implant.webp'][$index%3];
 if(preg_match('~2Fp[1-9]~',$original))return ['/assets/images/care-portrait.webp','/assets/images/child-visit.webp','/assets/images/consult-woman.webp'][$index%3];
 if(preg_match('~2F([1-8])\.jpg~',$original,$match))return '/assets/images/'.['consult-adult','consult-woman','consultation','child-visit','care-portrait','treatment-room','lounge','reception'][(int)$match[1]-1].'.webp';
 if(preg_match('~implant|sinus|kost~i',$route))return '/assets/images/'.['implant','consult-adult','technology','treatment-room'][$index%4].'.webp';
 if(preg_match('~prikus|breket|aligner|vinir~i',$route))return '/assets/images/'.['orthodontics','consult-woman','prevention','consultation'][$index%4].'.webp';
 if(preg_match('~dete|child|detsk~i',$route))return '/assets/images/'.['child-visit','child-play-v2','child-care-v2','consultation'][$index%4].'.webp';
 return '/assets/images/'.['consultation','treatment-room','prevention'][$index%3].'.webp';
}
function dz_home_clinics(Dom\HTMLDocument $doc,Dom\Element $root):void {
 $section=$root->querySelector('[data-section="8"]');
 if(!$section?->querySelector('iframe')){$section=$root->querySelector('iframe');while($section && !$section->querySelector('select'))$section=$section->parentElement;}
 if(!$section)return;$section->setAttribute('data-clinic-picker','');
 $locations=company_locations();$tabs=iterator_to_array($section->querySelectorAll('[class*="md:h-[75px]"]'));
 foreach($tabs as $i=>$tab){if($i>=count($locations)){$tab->parentElement->remove();continue;}$tab->setAttribute('data-clinic-tab',(string)$i);$tab->setAttribute('role','tab');$tab->setAttribute('tabindex','0');$tab->setAttribute('aria-selected',$i===0?'true':'false');$tab->setAttribute('class',$tab->getAttribute('class').' dz-clinic-tab');$parts=$tab->querySelectorAll('.text-p14');$parts->item(0)->textContent=$locations[$i]['area'];$parts->item(1)->textContent=$locations[$i]['address'];}
 $select=$section->querySelector('select');if($select){$select->setAttribute('data-clinic-select','');$select->setAttribute('aria-label','Адрес клиники');$select->textContent='';foreach($locations as $i=>$location){$option=$doc->createElement('option');$option->setAttribute('value',(string)$i);$option->textContent=$location['area'];$select->appendChild($option);}}
 $map=$section->querySelector('iframe');$map->setAttribute('data-clinic-map','');$map->setAttribute('title','Карта: '.$locations[0]['address']);$map->setAttribute('src','https://yandex.ru/map-widget/v1/?text='.rawurlencode($locations[0]['query']).'&z=16');
 $info=$map->parentElement->nextElementSibling;$info->setAttribute('class',$info->getAttribute('class').' dz-clinic-info');
 $heading=$info->firstElementChild->firstElementChild;$heading->textContent=$locations[0]['name'];$heading->setAttribute('data-clinic-title','');
 $description=$heading->nextElementSibling;$description->textContent='';$description->appendChild(dz_fragment($doc,'<p data-clinic-address>'.e($locations[0]['address']).'</p><p data-clinic-description>Часы работы: 09:00–21:00. Приём по ДМС и ОМС не ведётся. Лечение предоставляется на платной основе.</p><a data-clinic-directions class="underline" href="https://yandex.ru/maps/?text='.rawurlencode($locations[0]['query']).'" target="_blank" rel="noopener noreferrer">Построить маршрут</a>'));
 $bottom=$info->lastElementChild;$bottom->textContent='';$bottom->appendChild(dz_fragment($doc,'<a class="text-p14" href="tel:'.COMPANY_TEL.'">'.COMPANY_PHONE.'</a><a class="text-p12" href="'.BOOKING_URL.'">Записаться онлайн →</a>'));
}
function render_original(string $route):void {
 $file=PROJECT_ROOT.'/content/pages/'.routes()[$route]['file'];
 $doc=Dom\HTMLDocument::createFromString('<!doctype html><html><body>'.file_get_contents($file).'</body></html>',LIBXML_NOERROR);$root=$doc->body;
 rewrite_secondary_copy($doc,$root,$route);
 if($route==='index'){rewrite_home_copy($doc,$root);dz_home_clinics($doc,$root);$hero=$root->querySelector('h1');if($hero)$hero->textContent='«ДОМ ЗУБОВ» — стоматология с заботой о вас';}
 elseif($root->querySelector('iframe')){
  $map=$root->querySelector('iframe');$layout=$map;while($layout&&!$layout->querySelector('select'))$layout=$layout->parentElement;
  if($layout&&$layout!==$root){$locations=company_locations();$layout->setAttribute('data-clinic-picker','');$layout->setAttribute('class',$layout->getAttribute('class').' dz-secondary-picker');$map->setAttribute('data-clinic-map','');$map->setAttribute('title','Карта: '.$locations[0]['address']);$map->setAttribute('src','https://yandex.ru/map-widget/v1/?text='.rawurlencode($locations[0]['query']).'&z=16');
   foreach(iterator_to_array($layout->querySelectorAll('.cursor-pointer')) as $i=>$tab){$lines=$tab->querySelectorAll('.text-p14');if($lines->length<2)continue;if($i>=count($locations)){$tab->parentElement->remove();continue;}$tab->setAttribute('data-clinic-tab',(string)$i);$tab->setAttribute('role','tab');$tab->setAttribute('tabindex','0');$tab->setAttribute('class',$tab->getAttribute('class').' dz-clinic-tab');$lines->item(0)->textContent=$locations[$i]['area'];$lines->item(1)->textContent=$locations[$i]['address'];}
   $select=$layout->querySelector('select');$select->textContent='';$select->setAttribute('data-clinic-select','');$select->setAttribute('aria-label','Адрес клиники');foreach($locations as $i=>$location){$option=$doc->createElement('option');$option->setAttribute('value',(string)$i);$option->textContent=$location['area'];$select->appendChild($option);}
  }
 }
 if(str_starts_with($route,'services/'))rewrite_service_copy($doc,$root,$route);
 $names=[];$oldDoctors=json_decode(file_get_contents(PROJECT_ROOT.'/content/doctors.json'),true)['doctors']??[];foreach($oldDoctors as $doctor)$names[$doctor['name']]='Специалист Дома Зубов';
 $names+=['Артем Газаров'=>'Команда Дома Зубов','Артём Газаров'=>'Команда Дома Зубов','основатель клиник «Дом Зубов»'=>'стоматология «Дом Зубов»','reception@belayaraduga.ru'=>COMPANY_EMAIL,'info@belayaraduga.ru'=>COMPANY_EMAIL];dz_replace_text($root,$names);
 foreach($root->querySelectorAll('[class*="italic"]') as $attribution){if(preg_match('/врач|стоматолог|ортодонт|Габеева/ui',$attribution->textContent))$attribution->textContent='Команда «Дом Зубов»';}
 foreach($root->querySelectorAll('a') as $link){$href=$link->getAttribute('href');if(str_starts_with($href,'tel:')){$link->setAttribute('href','tel:'.COMPANY_TEL);$link->textContent=COMPANY_PHONE;}elseif(str_starts_with($href,'mailto:')){$link->setAttribute('href','mailto:'.COMPANY_EMAIL);$link->textContent=COMPANY_EMAIL;}elseif(preg_match('~t\.me|telegram|wa\.me|max\.ru|vk\.com|instagram|youtube|apps\.apple|play\.google~i',$href)){$link->removeAttribute('href');$link->setAttribute('aria-disabled','true');$link->setAttribute('title','Ссылка появится позже');}elseif(str_contains($href,'/doctors/')){$link->setAttribute('href','/doctors');$link->setAttribute('aria-label','Специалист Дома Зубов');}elseif(preg_match('~pdf/|politika|soglasie~i',$href))$link->setAttribute('href','/privacy-policy');}
 // Keep the existing team section and carousel geometry, replacing imported identities.
 require_once PROJECT_ROOT.'/templates/doctor-card.php';
 foreach(iterator_to_array($root->querySelectorAll('.a11y-doctor-card')) as $card){
  $track=$card->parentElement;if(!$track||$track->hasAttribute('data-real-team'))continue;
  $track->setAttribute('data-real-team','');$track->setAttribute('data-team-track','');$track->textContent='';
  foreach(doctor_data()['doctors'] as $teamDoctor)$track->appendChild(dz_fragment($doc,doctor_card($teamDoctor,true)));
 }
 foreach($root->querySelectorAll('section') as $section){$cards=$section->querySelectorAll('[data-team-card]');if($cards->length){$section->setAttribute('data-team-carousel','');$previous=$section->querySelector('button[aria-label*="Предыдущ"]');$next=$section->querySelector('button[aria-label*="Следующ"]');if($previous)$previous->setAttribute('data-team-prev','');if($next)$next->setAttribute('data-team-next','');}}
 // Replace every source image, including baked-in blue backgrounds.
 $imageIndex=0;
 foreach(iterator_to_array($root->querySelectorAll('img')) as $image){if($image->closest('.dz-real-doctor-card'))continue;$src=$image->getAttribute('src');
  if(dz_small_image($image)){$wrapper=$image->parentElement;$context=$wrapper->parentElement?->textContent??'';$wrapper->setAttribute('class',$wrapper->getAttribute('class').' dz-icon-surface');$image->replaceWith(dz_fragment($doc,dz_ui_icon(dz_icon_name($src,$context))));continue;}
  $image->setAttribute('src',dz_asset($src,$route,$imageIndex++));$image->removeAttribute('srcset');$image->removeAttribute('data-nimg');$image->setAttribute('alt',str_contains($image->getAttribute('src'),'placeholder')?'Материалы готовятся к публикации':'Дом Зубов');$image->setAttribute('class',$image->getAttribute('class').' dz-replaced-image');}
 foreach($root->querySelectorAll('[style]') as $element){$style=$element->getAttribute('style');$style=preg_replace_callback('~url\(["\']?([^\)"\']+)["\']?\)~',function($m)use($route,&$imageIndex){return 'url("'.dz_asset($m[1],$route,$imageIndex++).'")';},$style);$element->setAttribute('style',$style);}
 foreach($root->querySelectorAll('[class*="cursor-ew-resize"]') as $case){$case->setAttribute('data-case-demo','');$case->setAttribute('style',str_replace('touch-action:none','touch-action:pan-y',$case->getAttribute('style')??''));$caseGrid=$case->parentElement->parentElement;if(str_contains($caseGrid->getAttribute('class')??'','grid')){$caseGrid->setAttribute('data-case-grid','');$caseGrid->setAttribute('data-case-columns',(string)min(3,$caseGrid->childElementCount));}foreach($case->querySelectorAll('img') as $i=>$image){$image->setAttribute('src','/assets/images/case-'.($i===0?'after':'before').'.svg');$image->setAttribute('alt',$i===0?'После':'До');}$case->setAttribute('aria-label','Сравнение: места для фотографий до и после лечения');}
 foreach(iterator_to_array($root->querySelectorAll('button[data-appointment]')) as $button){$anchor=$doc->createElement('a');$anchor->setAttribute('class',str_replace(['opacity-50','cursor-not-allowed','bg-gray-400'],['','','bg-brand'],$button->getAttribute('class')).' dz-booking-link');$anchor->setAttribute('href',BOOKING_URL);$anchor->textContent=preg_match('/ДАТУ|ВРЕМЯ/u',$button->textContent)?'Выбрать время':'Записаться на приём';if($button->parentElement->localName==='a')$button->parentElement->replaceWith($anchor);else $button->replaceWith($anchor);}
 foreach($root->querySelectorAll('button[data-call]') as $button){$button->setAttribute('data-href','tel:'.COMPANY_TEL);}
 // Source galleries retain their card sizes and visual ordering.
 foreach($root->querySelectorAll('section') as $section){if($section->hasAttribute('data-team-carousel'))continue;$tracks=$section->querySelectorAll('[class*="overflow-x-auto"], [class*="overflow-x-scroll"]');$track=$tracks->item(0);if(!$track)continue;$section->setAttribute('data-gallery','');$track->setAttribute('data-gallery-track','');foreach($section->querySelectorAll('button[aria-label]') as $button){$label=$button->getAttribute('aria-label');if(str_contains($label,'Предыдущ'))$button->setAttribute('data-gallery-prev','');if(str_contains($label,'Следующ'))$button->setAttribute('data-gallery-next','');}}
 // Keep the original contact form in place; transparently prepare an email draft.
 foreach($root->querySelectorAll('.a11y-form-card') as $card){$content=$card->querySelector('.a11y-form-content');if(!$content || !$content->querySelector('input') || !$content->querySelector('[data-demo-submit]'))continue;$form=$doc->createElement('form');$form->setAttribute('class',$content->getAttribute('class'));$form->setAttribute('data-contact-form','');$form->setAttribute('data-email',COMPANY_EMAIL);while($content->firstChild)$form->appendChild($content->firstChild);$content->replaceWith($form);foreach($form->querySelectorAll('input') as $input){$type=$input->getAttribute('type');$input->setAttribute('name',$type==='tel'?'phone':($type==='email'?'email':'name'));$input->setAttribute('required','');if($type==='tel')$input->setAttribute('pattern','[\+0-9\(\) \-]{10,20}');}$submit=$form->querySelector('[data-demo-submit]');if($submit){$submit->removeAttribute('data-demo-submit');$submit->setAttribute('type','submit');$submit->textContent='ПОДГОТОВИТЬ ОБРАЩЕНИЕ';}foreach(iterator_to_array($form->querySelectorAll('[role="switch"]')) as $i=>$switch){$checkbox=$doc->createElement('input');$checkbox->setAttribute('type','checkbox');$checkbox->setAttribute('name',$i===0?'consent':'marketing');$checkbox->setAttribute('class','dz-consent-checkbox');$checkbox->setAttribute('aria-label',$i===0?'Согласие на обработку данных':'Согласие на рассылку');if($i===0)$checkbox->setAttribute('required','');$switch->replaceWith($checkbox);} $status=$doc->createElement('p');$status->setAttribute('data-form-status','');$status->setAttribute('class','dz-form-note');$status->textContent='Откроется почтовая программа с письмом для клиники.';$form->appendChild($status);}
 foreach($root->querySelectorAll('form[data-contact-form]') as $form){
  $form->setAttribute('method','post');$form->setAttribute('action','/contact-unavailable');
  foreach(['name'=>['80','name','Ваше имя'],'phone'=>['20','tel','Номер телефона'],'email'=>['254','email','Электронная почта']] as $name=>[$length,$autocomplete,$label]){foreach($form->querySelectorAll('[name="'.$name.'"]') as $input){$input->setAttribute('maxlength',$length);$input->setAttribute('autocomplete',$autocomplete);$input->setAttribute('aria-label',$label);}}
  foreach($form->querySelectorAll('textarea') as $input)$input->setAttribute('maxlength','2000');
  $form->appendChild(dz_fragment($doc,'<div class="dz-form-trap" aria-hidden="true"><label>Не заполняйте это поле<input type="text" name="website" tabindex="-1" autocomplete="off" maxlength="200" data-form-honeypot></label></div><noscript><p class="dz-form-note">Для подготовки письма включите JavaScript или воспользуйтесь <a href="'.BOOKING_URL.'">онлайн-записью</a>.</p></noscript>'));
  foreach($form->querySelectorAll('button[type="submit"]') as $button)$button->setAttribute('disabled','');
 }
 foreach(iterator_to_array($root->querySelectorAll('form:not([data-contact-form])')) as $form){
  if(!$form->querySelector('a[href="'.BOOKING_URL.'"]'))continue;
  $steps=['Выберите специалиста','Подберите удобное время','Подтвердите запись'];
  foreach(iterator_to_array($form->querySelectorAll('input')) as $i=>$input){$row=$doc->createElement('div');$row->setAttribute('class',$input->getAttribute('class').' dz-booking-step');$row->textContent=sprintf('%02d',$i+1).' · '.($steps[$i]??'Перейдите к записи');$input->replaceWith($row);}
  $intro=$form->previousElementSibling;if($intro?->localName==='p')$intro->textContent='Выберите специалиста и время приёма в сервисе онлайн-записи:';
  $box=$doc->createElement('div');$box->setAttribute('class',$form->getAttribute('class'));while($form->firstChild)$box->appendChild($form->firstChild);$form->replaceWith($box);
 }
 if($route==='index'){
  $gallery=$root->querySelector('[data-section="3"]');if($gallery){$heading=$gallery->querySelector('[class*="text-p36"]');if($heading){$caption=$doc->createElement('p');$caption->setAttribute('class','dz-illustration-note');$caption->textContent='Иллюстрации атмосферы Дома Зубов';$heading->after($caption);}}
  $stories=$root->querySelector('[data-section="7"]');if($stories){$heading=$stories->querySelector('[class*="text-p36"]');if($heading)$heading->textContent='Забота о пациентах — в каждом визите';$note=$doc->createElement('p');$note->setAttribute('class','dz-illustration-note');$note->textContent='Иллюстрации. Здесь появятся истории наших пациентов.';if($heading)$heading->after($note);ob_start();require PROJECT_ROOT.'/templates/restored-reviews.php';$reviews=ob_get_clean();$stories->before(dz_fragment($doc,$reviews));}
 }
 foreach(iterator_to_array($root->querySelectorAll('.dz-illustration-note,figcaption')) as $caption)$caption->remove();
 // Fill the original review slots; never leave a permanent loading message.
 foreach(iterator_to_array($root->querySelectorAll('[data-section]')) as $section){if(str_contains($section->textContent,'Загрузка отзывов')){ob_start();require PROJECT_ROOT.'/templates/restored-reviews.php';$section->replaceWith(dz_fragment($doc,ob_get_clean()));}}
 foreach($root->querySelectorAll('img') as $image){if(str_contains($image->getAttribute('src'),'insurance-placeholder')){$image->setAttribute('alt','Страховая компания — список партнёров уточняется');}}
 $imageDescriptions=['reception'=>'зона ресепшена стоматологии','lounge'=>'зона ожидания','treatment-room'=>'стоматологический кабинет','consultation'=>'обсуждение лечения со стоматологом','consult-adult'=>'консультация взрослого пациента','consult-woman'=>'консультация пациентки','child-visit'=>'знакомство ребёнка со стоматологом','child-hero-v2'=>'детский стоматологический приём','child-play-v2'=>'знакомство ребёнка с кабинетом','child-care-v2'=>'бережный осмотр ребёнка','care-portrait'=>'забота о комфорте пациента','technology'=>'стоматологическое оборудование','prevention'=>'инструменты для профилактического ухода','implant'=>'модель зубов для объяснения имплантации','orthodontics'=>'ортодонтическое лечение'];
 foreach($root->querySelectorAll('img') as $image){
  $image->setAttribute('decoding','async');
  $image->setAttribute('loading',$image->closest('.a11y-hero-section')?'eager':'lazy');
  $name=pathinfo($image->getAttribute('src'),PATHINFO_FILENAME);
  if(isset($imageDescriptions[$name]))$image->setAttribute('alt','Иллюстрация: '.$imageDescriptions[$name]);
 }
 // One clinic: show an address, not a selector offering a fictional choice.
 if(count(company_locations())===1){
  foreach(iterator_to_array($root->querySelectorAll('select[data-clinic-select]')) as $select){if($select->nextElementSibling?->localName==='svg')$select->nextElementSibling->remove();$address=$doc->createElement('p');$address->setAttribute('class','dz-single-location');$address->textContent=company_locations()[0]['address'];$select->replaceWith($address);}
  foreach($root->querySelectorAll('[data-clinic-picker]') as $picker){$heading=$picker->firstElementChild;if($heading&&trim($heading->textContent)==='Клиники')$heading->textContent='Наша клиника';}
 }
 // Old source branding must not survive in attributes or prose.
 $html='';foreach($root->childNodes as $node)$html.=$doc->saveHtml($node);
 $html=preg_replace('~[\w.+-]+@belayaraduga\.ru~i',COMPANY_EMAIL,$html);
 $html=str_replace(['Белая Радуга','Белая радуга','БЕЛАЯ РАДУГА','Белой Радуги','Белой Радуге','white-rainbow'],['Дом Зубов','Дом Зубов','ДОМ ЗУБОВ','Дома Зубов','Доме Зубов','dom-zubov'],$html);
 echo dz_polish_content($html,$route);
}
