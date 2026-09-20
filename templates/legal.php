<?php
$legalFile=PROJECT_ROOT.'/content/legal-resources.json';
$legal=is_file($legalFile)?json_decode(file_get_contents($legalFile),true,512,JSON_THROW_ON_ERROR):[];
$clinicDocuments=[
 ['Договор на платные медицинские услуги','Утверждённая форма договора ООО «Дом Зубов» и приложения с перечнем услуг, сроками и стоимостью.'],
 ['Утверждённый прейскурант','Цены на сайте сохранены для макета. Подписанный прейскурант клиники будет добавлен отдельно.'],
 ['Правила и условия оказания медицинской помощи','Порядок обращения, записи, оплаты, переноса приёма и получения медицинских документов.'],
 ['Политика обработки персональных данных','Утверждённая политика оператора и отдельные согласия для предусмотренных целей обработки.'],
 ['Сведения о медицинских работниках','Профили врачей, предоставленные сведения об образовании, документы и расписание приёма опубликованы в разделе «Врачи». Актуальные сведения о допуске к работе дополняются по мере получения.'],
 ['Приём руководителя','Порядок и график приёма обращений руководителем клиники.'],
];
?>
<section class="dz-section dz-shell dz-legal">
  <nav class="dz-breadcrumb" aria-label="Хлебные крошки"><a href="/">Главная</a><span>/</span><span>Документы</span></nav>
  <p class="dz-eyebrow">Открытая информация</p><h1>Документы<br>и реквизиты</h1>
  <p class="dz-legal-lead">Сведения об ООО «Дом Зубов», документы для пациентов и официальные нормативные материалы.</p>
  <nav class="dz-legal-nav" aria-label="Разделы документов"><a href="#organization">Организация</a><a href="#license">Лицензия</a><a href="#clinic-documents">Документы клиники</a><a href="#regulations">Нормативные акты</a><a href="#authorities">Контролирующие органы</a></nav>
  <aside class="dz-legal-status"><?= icon('document') ?><div><strong>Комплект документов готовится</strong><p>Лицензия и утверждённые документы ООО «Дом Зубов» будут размещены после их получения. Нормативные материалы ниже доступны для ознакомления и не заменяют документы клиники.</p></div></aside>
  <section id="organization" class="dz-legal-section"><p class="dz-eyebrow">01 / Организация</p><h2>ООО «ДОМ ЗУБОВ»</h2>
    <dl class="dz-requisites">
    <?php foreach([
    'Полное наименование'=>'ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ «ДОМ ЗУБОВ»',
    'ИНН'=>'9727136018','КПП'=>'772701001','ОГРН'=>'1267700299283',
    'Юридический адрес'=>'117452, г. Москва, вн. тер. г. муниципальный округ Зюзино, б-р Симферопольский, д. 24, к. 4, помещ. 1/1',
    'Генеральный директор'=>'Расулов Магомед Раджабович, действует на основании Устава',
    'Электронная почта'=>COMPANY_EMAIL,'Телефон'=>COMPANY_PHONE,'Часы работы'=>COMPANY_HOURS,'Условия приёма'=>COMPANY_INSURANCE_NOTICE,
    ] as $label=>$value): ?><div><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div><?php endforeach; ?></dl>
    <details class="dz-legal-details"><summary>Банковские реквизиты <?= icon('chevron') ?></summary><dl class="dz-requisites"><?php foreach(['Расчётный счёт'=>'40702810320000363235','Банк'=>'ООО «Банк Точка»','БИК'=>'044525104','Корреспондентский счёт'=>'30101810745374525104'] as $label=>$value): ?><div><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div><?php endforeach; ?></dl></details>
  </section>
  <section id="license" class="dz-legal-section"><p class="dz-eyebrow">02 / Лицензирование</p><h2>Медицинская лицензия</h2>
    <div class="dz-license-card"><span class="dz-legal-icon"><?= icon('shield') ?></span><div><span class="dz-document-status">Документ готовится к размещению</span><h3>Лицензия ООО «Дом Зубов»</h3><p>Здесь будут номер лицензии, дата предоставления, лицензирующий орган и выписка из реестра с адресами и разрешёнными видами работ. До получения выписки эти сведения не подтверждены.</p><a class="dz-text-link" href="https://roszdravnadzor.gov.ru/services/licenses" target="_blank" rel="noopener noreferrer">Открыть официальный реестр <?= icon('arrow') ?></a></div></div>
    <div class="dz-legal-addresses"><?php foreach(company_locations() as $location): ?><article><span class="dz-eyebrow"><?= e($location['area']) ?></span><h3><?= e($location['address']) ?></h3><p>Сведения о лицензировании по этому адресу будут добавлены из выписки ООО «Дом Зубов».</p></article><?php endforeach; ?></div>
  </section>
  <section id="clinic-documents" class="dz-legal-section"><p class="dz-eyebrow">03 / Пациентам</p><h2>Документы клиники</h2><div class="dz-document-list">
    <?php foreach($clinicDocuments as [$name,$description]): ?><article class="dz-document-row"><span class="dz-legal-icon"><?= icon('document') ?></span><div><h3><?= e($name) ?></h3><p><?= e($description) ?></p></div><span class="dz-document-status"><?= $name==='Сведения о медицинских работниках'?'Профили размещены':'Готовится' ?></span></article><?php endforeach; ?>
    </div><div class="dz-legal-links"><a href="/prices">Стоимость услуг <?= icon('arrow') ?></a><a href="/doctors">Специалисты <?= icon('arrow') ?></a><a href="/pravila-zapisi">Запись на приём <?= icon('arrow') ?></a><a href="/privacy-policy">Обработка данных на сайте <?= icon('arrow') ?></a><a href="/contacts">Адреса и связь <?= icon('arrow') ?></a><a href="/sitemap">Карта сайта <?= icon('arrow') ?></a></div>
    <div class="dz-legal-contact"><h3>Запросить информацию или документ</h3><p>Напишите, какой документ вам нужен, или позвоните администратору. Для обращения к руководителю используйте те же контакты. Не прикладывайте медицинские документы к первому письму — сначала уточните порядок передачи.</p><div><a href="mailto:<?= COMPANY_EMAIL ?>"><?= COMPANY_EMAIL ?></a><a href="tel:<?= COMPANY_TEL ?>"><?= COMPANY_PHONE ?></a></div></div>
  </section>
  <section id="regulations" class="dz-legal-section"><p class="dz-eyebrow">04 / Нормативные материалы</p><h2>Правовая информация</h2><p class="dz-legal-description">Документы органов власти и официальные информационные ресурсы. Для каждого файла указан первоисточник. Публикация нормативного акта содержит редакцию на дату принятия; изменения проверяйте на официальном портале.</p>
    <div class="dz-document-list"><?php foreach($legal['documents']??[] as $document): ?><article class="dz-document-row"><span class="dz-legal-icon"><?= icon('document') ?></span><div><h3><?= e($document['title']) ?></h3><p><?= e($document['description']) ?></p><div class="dz-document-actions"><?php if(!empty($document['local_path'])): ?><a href="<?= e('/'.ltrim($document['local_path'],'/')) ?>" download>Скачать <?= e(strtoupper($document['format']??'PDF')) ?><?php if(!empty($document['bytes'])): ?> · <?= e(number_format($document['bytes']/1024/1024,1,',',' ')) ?> МБ<?php endif; ?> <?= icon('arrow') ?></a><?php endif; ?><a href="<?= e($document['source_url']??$document['url']) ?>" target="_blank" rel="noopener noreferrer">Официальный источник <?= icon('arrow') ?></a></div></div></article><?php endforeach; ?></div>
    <?php if(!empty($legal['services'])): ?><h3 class="dz-legal-subheading">Полезные официальные ресурсы</h3><div class="dz-legal-links"><?php foreach($legal['services'] as $service): ?><a href="<?= e($service['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($service['title']) ?> <?= icon('arrow') ?></a><?php endforeach; ?></div><?php endif; ?>
  </section>
  <section id="authorities" class="dz-legal-section"><p class="dz-eyebrow">05 / Обратная связь</p><h2>Контролирующие органы</h2><div class="dz-authority-grid"><?php foreach($legal['authorities']??[] as $authority): ?><article><h3><?= e($authority['title']) ?></h3><?php if(!empty($authority['address'])): ?><p><?= e($authority['address']) ?></p><?php endif; ?><?php if(!empty($authority['phone'])): ?><p><?= e($authority['phone']) ?></p><?php endif; ?><a class="dz-text-link" href="<?= e($authority['url']) ?>" target="_blank" rel="noopener noreferrer">Сайт и обращения <?= icon('arrow') ?></a></article><?php endforeach; ?></div></section>
</section>
