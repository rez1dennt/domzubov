<?php
$navigation=json_decode(file_get_contents(PROJECT_ROOT.'/content/navigation.json'),true);
$serviceMenu=json_decode(file_get_contents(PROJECT_ROOT.'/content/service-menu.json'),true)['categories'];
$patientIcons=['wallet','calendar','document','shield','gift','gift','pin'];
?>
<!doctype html><html lang="ru"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#151513">
<?php require __DIR__.'/seo-head.php'; ?>
<link rel="icon" type="image/svg+xml" href="/assets/mark.svg">
<link rel="preload" href="/assets/fonts/russo-one-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/golos-text-cyrillic-400.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/fonts/fonts.css?v=<?= asset_version('/assets/fonts/fonts.css') ?>">
<link rel="stylesheet" href="/assets/layout.css?v=<?= asset_version('/assets/layout.css') ?>">
<link rel="stylesheet" href="/assets/theme.css?v=<?= asset_version('/assets/theme.css') ?>">
<link rel="stylesheet" href="/assets/navigation.css?v=<?= asset_version('/assets/navigation.css') ?>">
<link rel="stylesheet" href="/assets/accessibility.css?v=<?= asset_version('/assets/accessibility.css') ?>">
<?php if(!$restored && $route!=='reviews'): ?><link rel="stylesheet" href="/assets/editorial.css?v=<?= asset_version('/assets/editorial.css') ?>"><?php endif; ?>
<link rel="stylesheet" href="/assets/restoration.css?v=<?= asset_version('/assets/restoration.css') ?>">
<script src="/assets/form-guard.js?v=<?= asset_version('/assets/form-guard.js') ?>" defer></script>
<script src="/assets/restored.js?v=<?= asset_version('/assets/restored.js') ?>" defer></script>
<script src="/assets/site.js?v=<?= asset_version('/assets/site.js') ?>" defer></script>
<script src="/assets/navigation.js?v=<?= asset_version('/assets/navigation.js') ?>" defer></script>
<script src="/assets/accessibility.js?v=<?= asset_version('/assets/accessibility.js') ?>" defer></script>
<?php if($route==='doctors'): ?><script src="/assets/doctors.js?v=<?= asset_version('/assets/doctors.js') ?>" defer></script><?php endif; ?>
<script src="/assets/experience.js?v=<?= asset_version('/assets/experience.js') ?>" defer></script>

<link rel="stylesheet" href="/assets/search.css?v=<?= asset_version('/assets/search.css') ?>">
<link rel="stylesheet" href="/assets/polish.css?v=<?= asset_version('/assets/polish.css') ?>">
<link rel="stylesheet" href="/assets/doctor-profiles.css?v=<?= asset_version('/assets/doctor-profiles.css') ?>">
<link rel="stylesheet" href="/assets/content-polish.css?v=<?= asset_version('/assets/content-polish.css') ?>">
<?php if($doctor): ?><script src="/assets/doctor-profiles.js?v=<?= asset_version('/assets/doctor-profiles.js') ?>" defer></script><?php endif; ?>
<?php if(in_array($route,['yuridicheskaya-informatsiya','sitemap'],true)): ?><link rel="stylesheet" href="/assets/legal.css?v=<?= asset_version('/assets/legal.css') ?>"><?php endif; ?>
<script src="/assets/search.js?v=<?= asset_version('/assets/search.js') ?>" defer></script>
<script src="/assets/phone-mask.js?v=<?= asset_version('/assets/phone-mask.js') ?>" defer></script>
<script src="/assets/polish.js?v=<?= asset_version('/assets/polish.js') ?>" defer></script>
</head><body>
<a class="dz-skip" href="#main">Перейти к содержимому</a>
<header class="dz-header">
  <div class="dz-topbar"><div class="dz-shell"><span class="dz-header-caption">Семейная стоматология</span><button class="dz-a11y" type="button" data-accessibility aria-haspopup="dialog" aria-controls="accessibility-settings"><?= icon('eye') ?><span>Версия для слабовидящих</span></button><a class="dz-topphone" href="tel:+79286996784">+7 (928) 699-67-84</a></div></div>
  <div class="dz-header-main dz-shell">
    <a class="dz-home-link" href="/" aria-label="Дом Зубов — на главную"><?= brand_markup() ?></a>
    <nav class="dz-desktop-nav" aria-label="Основная навигация">
      <button class="dz-nav-trigger" type="button" data-nav-panel="services-menu" aria-expanded="false" aria-controls="services-menu">Услуги <?= icon('chevron') ?></button>
      <a href="/o-nas">О клинике</a><a href="/doctors">Врачи</a><a href="/reviews">Отзывы</a><a href="/prices">Цены</a>
      <button class="dz-nav-trigger" type="button" data-nav-panel="patients-menu" aria-expanded="false" aria-controls="patients-menu">Пациентам <?= icon('chevron') ?></button>
      <a href="/contacts">Контакты</a>
    </nav>
    <?php require_once __DIR__.'/search.php'; echo dz_search_dropdown_template(); ?>
    <button type="button" class="dz-icon-btn dz-search-toggle" data-search-open aria-expanded="false" aria-controls="site-search" aria-label="Поиск по сайту"><?= icon('search') ?></button>
    <a class="dz-btn dz-header-cta" href="https://idotvip.ru/domzubov">Записаться <?= icon('arrow') ?></a>
    <button class="dz-mobile-eye" type="button" data-accessibility aria-label="Версия для слабовидящих" aria-controls="accessibility-settings"><?= icon('eye') ?></button>
    <button type="button" class="dz-menu-toggle" aria-expanded="false" aria-controls="mobile-menu" aria-label="Открыть меню"><span></span><span></span></button>
  </div>
</header>
<div class="dz-nav-scrim" data-nav-scrim hidden></div>
<section class="dz-mega-panel" id="services-menu" aria-label="Все направления стоматологии" hidden>
  <div class="dz-mega-layout">
    <div class="dz-mega-categories"><p class="dz-menu-eyebrow">Направления</p><div class="dz-category-grid" role="tablist" aria-label="Категории услуг" aria-orientation="vertical">
    <?php foreach($serviceMenu as $i=>$category): ?>
      <?php if(empty($category['items'])): ?><a class="dz-category dz-category-all" href="<?= e($category['link']) ?>"><?= e($category['name']) ?> <?= icon('arrow') ?></a>
      <?php else: ?><button type="button" class="dz-category" id="service-category-<?= $i ?>" role="tab" aria-selected="<?= $i===0?'true':'false' ?>" aria-controls="service-links-<?= $i ?>" tabindex="<?= $i===0?'0':'-1' ?>" data-service-category="<?= $i ?>"><?= e($category['name']) ?> <?= icon('chevron') ?></button><?php endif; ?>
    <?php endforeach; ?>
    </div></div>
    <div class="dz-mega-content">
    <?php foreach($serviceMenu as $i=>$category): if(empty($category['items']))continue; ?>
      <div id="service-links-<?= $i ?>" role="tabpanel" aria-labelledby="service-category-<?= $i ?>" data-service-links="<?= $i ?>" <?= $i===0?'':'hidden' ?>><p class="dz-menu-eyebrow">Услуги</p><h2><?= e($category['name']) ?></h2><div class="dz-service-links"><?php foreach($category['items'] as $item): ?><a href="<?= e($item['link']) ?>"><?= e($item['name']) ?><?= icon('arrow') ?></a><?php endforeach; ?></div></div>
    <?php endforeach; ?>
    </div>
    <aside class="dz-menu-promo"><div><p class="dz-menu-eyebrow">Подарочные сертификаты</p><h2>Дарите заботу</h2><a href="/gift-certificates" class="dz-btn">Позаботиться <?= icon('arrow') ?></a></div><img src="/assets/images/consultation.webp" alt="Консультация стоматолога — иллюстрация" width="380" height="220"></aside>
  </div>
</section>
<section class="dz-mega-panel dz-patients-panel" id="patients-menu" aria-label="Информация пациентам" hidden>
  <div class="dz-patients-intro"><p class="dz-menu-eyebrow">Пациентам</p><h2>Всё для вашего<br>спокойствия</h2><p>Стоимость, документы и полезная информация перед приёмом.</p><a class="dz-text-link" href="/contacts">Связаться с клиникой <?= icon('arrow') ?></a></div>
  <div class="dz-patient-links"><?php foreach($navigation[2]['links'] as $i=>$link): ?><a href="<?= e($link['href']) ?>"><span class="dz-menu-icon"><?= icon($patientIcons[$i]??'document') ?></span><span><?= e($link['label']) ?></span><?= icon('arrow','dz-link-arrow') ?></a><?php endforeach; ?><a href="/yuridicheskaya-informatsiya"><span class="dz-menu-icon"><?= icon('document') ?></span><span>Юридическая информация</span><?= icon('arrow','dz-link-arrow') ?></a></div>
</section>
<dialog id="mobile-menu" class="dz-mobile-menu" aria-label="Навигация по сайту">
  <div class="dz-mobile-menu-head"><?= brand_markup() ?><button type="button" class="dz-icon-btn" data-close aria-label="Закрыть меню"><?= icon('close') ?></button></div>
  <button type="button" class="dz-mobile-search" data-search-open aria-expanded="false" aria-controls="site-search"><?= icon('search') ?><span>Найти услугу или информацию</span></button>
  <nav aria-label="Мобильная навигация">
    <details class="dz-mobile-services"><summary>Услуги <?= icon('chevron') ?></summary><div class="dz-mobile-categories">
    <?php foreach($serviceMenu as $category): ?><?php if(empty($category['items'])): ?><a href="<?= e($category['link']) ?>"><?= e($category['name']) ?><?= icon('arrow') ?></a><?php else: ?><details><summary><?= e($category['name']) ?><?= icon('chevron') ?></summary><div><?php foreach($category['items'] as $item): ?><a href="<?= e($item['link']) ?>"><?= e($item['name']) ?><?= icon('arrow') ?></a><?php endforeach; ?></div></details><?php endif; ?><?php endforeach; ?>
    </div></details>
    <a href="/o-nas">О клинике <?= icon('arrow') ?></a><a href="/doctors">Врачи <?= icon('arrow') ?></a><a href="/reviews">Отзывы <?= icon('arrow') ?></a><a href="/prices">Цены <?= icon('arrow') ?></a>
    <details><summary>Пациентам <?= icon('chevron') ?></summary><div><?php foreach($navigation[2]['links'] as $link): ?><a href="<?= e($link['href']) ?>"><?= e($link['label']) ?><?= icon('arrow') ?></a><?php endforeach; ?></div></details>
    <a href="/contacts">Контакты <?= icon('arrow') ?></a>
  </nav>
  <button class="dz-mobile-accessibility" type="button" data-accessibility><?= icon('eye') ?> Версия для слабовидящих</button>
  <a class="dz-menu-phone" href="tel:+79286996784">+7 (928) 699-67-84</a>
  <?= booking_button() ?>
</dialog>
