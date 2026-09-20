<?php
declare(strict_types=1);
const BOOKING_URL='https://idotvip.ru/domzubov';
const COMPANY_PHONE='+7 (928) 699-67-84';
const COMPANY_TEL='+79286996784';
const COMPANY_EMAIL='Domzubov777@yandex.ru';
const COMPANY_HOURS='09:00–21:00';
const COMPANY_INSURANCE_NOTICE='Приём по ДМС и ОМС не ведётся. Лечение предоставляется на платной основе.';
function company_locations(): array { return [
 ['slug'=>'contacts','name'=>'Дом Зубов','area'=>'На Симферопольском бульваре','address'=>'Москва, Симферопольский бульвар, 24, корп. 4','query'=>'Москва, Симферопольский бульвар, 24, корпус 4','hours'=>COMPANY_HOURS,'description'=>'Часы работы: '.COMPANY_HOURS.'. '.COMPANY_INSURANCE_NOTICE.' Если нужна помощь при посещении, сообщите администратору заранее.'],
]; }
function editorial_services(): array {static $data;if($data!==null)return $data;$data=[];foreach(glob(PROJECT_ROOT.'/content/editorial/services-*.json') as $file){foreach(json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR) as $key=>$value)$data[ltrim($key,'/')]=$value;}return $data;}
function editorial_pages(): array {$file=PROJECT_ROOT.'/content/editorial/pages.json';return is_file($file)?json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR):[];}
function booking_button(string $label='Записаться на приём'): string {return '<a class="dz-btn" href="'.BOOKING_URL.'">'.e($label).' '.icon('arrow').'</a>';}
function medical_icon(): string {return '<svg class="dz-icon a11y-keep" viewBox="0 0 32 32" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M16 7c-3-3-9-3-10 2-1 4 1 7 2 11 1 5 2 7 4 7s1-9 4-9 2 9 4 9 3-2 4-7c1-4 3-7 2-11-1-5-7-5-10-2Z"/><path d="m12 7 4 2"/></svg>';}
