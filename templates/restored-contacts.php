<?php
require_once PROJECT_ROOT.'/app/presentation.php';
ob_start();render_original('index');$homeHtml=ob_get_clean();
$doc=Dom\HTMLDocument::createFromString('<!doctype html><html><body>'.$homeHtml.'</body></html>',LIBXML_NOERROR);
$section=$doc->querySelector('[data-clinic-picker]');
$heading=$section->firstElementChild;$h1=$doc->createElement('h1');$h1->setAttribute('class',$heading->getAttribute('class'));$h1->textContent='Контакты';$heading->replaceWith($h1);
$contact=$doc->createElement('div');$contact->setAttribute('class','dz-restored-contact-strip');$contact->appendChild(dz_fragment($doc,'<a href="tel:'.COMPANY_TEL.'">'.COMPANY_PHONE.'</a><a href="mailto:'.COMPANY_EMAIL.'">'.COMPANY_EMAIL.'</a>'.booking_button()));$h1->after($contact);
$notice=$doc->createElement('p');$notice->setAttribute('class','dz-contact-summary');$notice->textContent='Часы работы: '.COMPANY_HOURS.'. '.COMPANY_INSURANCE_NOTICE;$contact->after($notice);
echo $doc->saveHtml($section);
