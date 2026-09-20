<?php
declare(strict_types=1);
/** Small, consistent native line icons. Never route icon slots to photography. */
function dz_ui_icon(string $name):string {
 $paths=[
 'tooth'=>'<path d="M16 7c-3-3-9-3-10 2-1 4 1 7 2 11 1 5 2 7 4 7s1-9 4-9 2 9 4 9 3-2 4-7c1-4 3-7 2-11-1-5-7-5-10-2Z"/><path d="m12 7 4 2"/>',
 'smile'=>'<circle cx="16" cy="16" r="11"/><path d="M10 18c3 5 9 5 12 0M11 12h.1M21 12h.1"/>',
 'heart'=>'<path d="M16 27 5 16C-2 7 10 1 16 9c6-8 18-2 11 7L16 27Z"/>',
 'shield'=>'<path d="m16 3 11 4v9c0 7-11 13-11 13S5 23 5 16V7l11-4Z"/><path d="m11 16 4 4 7-8"/>',
 'wallet'=>'<path d="M27 11V6H8a4 4 0 0 0 0 8h20v13H8a4 4 0 0 1-4-4V10"/><path d="M28 18h-7v5h7"/>',
 'receipt'=>'<path d="M8 3h16v26l-4-3-4 3-4-3-4 3V3Z"/><path d="M12 9h8m-8 5h8m-8 5h4"/>',
 'calendar'=>'<rect x="5" y="7" width="22" height="22" rx="4"/><path d="M10 3v8M22 3v8M5 14h22m-16 6h3m4 0h3m-10 5h3"/>',
 'gift'=>'<rect x="4" y="11" width="24" height="6" rx="2"/><path d="M7 17v12h18V17M16 11v18"/><path d="M16 11h-6a4 4 0 1 1 4-5l2 5Zm0 0h6a4 4 0 1 0-4-5l-2 5Z"/>',
 'sparkle'=>'<path d="m16 3 3.5 9.5L29 16l-9.5 3.5L16 29l-3.5-9.5L3 16l9.5-3.5L16 3ZM26 3v6M23 6h6"/>',
 'clock'=>'<circle cx="16" cy="16" r="12"/><path d="M16 8v8l5 3"/>',
 'search'=>'<circle cx="14" cy="14" r="9"/><path d="m21 21 8 8"/>',
 'doctor'=>'<circle cx="16" cy="9" r="5"/><path d="M5 29v-4c0-7 5-11 11-11s11 4 11 11v4M11 17v6a5 5 0 0 0 10 0v-6"/><circle cx="21" cy="23" r="2"/>',
 'child'=>'<circle cx="16" cy="16" r="11"/><path d="M13 5c-3 5 5 6 5 2M10 14h.1M22 14h.1M12 20c2 2 6 2 8 0"/>',
 'check'=>'<circle cx="16" cy="16" r="12"/><path d="m10 16 4 4 9-10"/>',
 'pin'=>'<path d="M25 13c0 7-9 16-9 16S7 20 7 13a9 9 0 1 1 18 0Z"/><circle cx="16" cy="13" r="3"/>',
 'brush'=>'<path d="m7 26 12-12m-7 1 5 5M18 7l7 7M20 5l7 7M22 3l7 7M16 9l7 7"/><path d="m5 28-1-4L17 11l5 5L9 29l-4-1Z"/>',
 ];
 return '<svg class="dz-feature-icon a11y-keep" viewBox="0 0 32 32" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['tooth']).'</svg>';
}
function dz_icon_name(string $path,string $context=''):string {
 if(preg_match('/нет скрытых/iu',$context))return 'shield';
 if(preg_match('/стоимость.*контрол/iu',$context))return 'receipt';
 if(preg_match('/индивидуаль.*план|план.*оплат/iu',$context))return 'calendar';
 if(str_contains($path,'baby-smile'))return 'smile';
 if(str_contains($path,'baby-afraid'))return 'heart';
 if(str_contains($path,'baby-tooth'))return 'tooth';
 $s=strtolower(rawurldecode(str_replace('_2F','/',$path))).' '.$context;
 foreach(['wallet'=>'wallet|payment|pay|money|стоим|оплат|платеж|платёж','receipt'=>'receipt|document|plan|план','calendar'=>'calendar|date|приём|запис','gift'=>'gift|present|подар','clock'=>'clock|time|hour|время|срок','shield'=>'shield|safe|afraid|security|защит|безопас|страх','child'=>'baby|child|дет|ребён|ребен','smile'=>'smile|улыб','heart'=>'heart|care|забот','sparkle'=>'shine|white|spark|бел|эстет','brush'=>'brush|clean|гигиен|налёт','doctor'=>'doctor|консульта','check'=>'check|гарант'] as $name=>$pattern){if(preg_match('~'.$pattern.'~iu',$s))return $name;}
 return 'tooth';
}
function dz_small_image(Dom\Element $image):bool {
 $path=$image->getAttribute('src');if(str_contains($path,'/assets/brand.svg')||str_contains($path,'/assets/mark.svg'))return false;
 $w=(int)$image->getAttribute('width');$h=(int)$image->getAttribute('height');
 if(preg_match('~(?:/icons/|2Ficons)~i',$path))return true;
 if($w>64||$h>64)return false;
 if($w>0&&$w<=64&&($h===0||$h<=64))return true;
 $parent=$image->parentElement?->getAttribute('class')??'';
 preg_match_all('~(?:w|h)-\[(\d+)px\]~',$parent,$sizes);
 foreach($sizes[1]??[] as $size){if((int)$size>=16&&(int)$size<=80)return true;}
 return preg_match('~(?:/icons/|2Ficons|baby-(?:smile|afraid|tooth)|money|wallet|receipt)~i',$path)===1;
}
