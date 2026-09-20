<?php declare(strict_types=1); ?>
<title><?= e($seo['title']) ?></title>
<meta name="description" content="<?= e($seo['description']) ?>">
<meta name="robots" content="<?= e($seo['robots']) ?>">
<?php if ($seo['canonical'] !== null): ?>
<link rel="canonical" href="<?= e($seo['canonical']) ?>">
<meta property="og:url" content="<?= e($seo['canonical']) ?>">
<?php endif; ?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="Дом Зубов">
<meta property="og:locale" content="ru_RU">
<meta property="og:title" content="<?= e($seo['title']) ?>">
<meta property="og:description" content="<?= e($seo['description']) ?>">
<meta property="og:image" content="<?= e($seo['image']) ?>">
<meta property="og:image:alt" content="<?= e($seo['image_alt']) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($seo['title']) ?>">
<meta name="twitter:description" content="<?= e($seo['description']) ?>">
<meta name="twitter:image" content="<?= e($seo['image']) ?>">
<meta name="twitter:image:alt" content="<?= e($seo['image_alt']) ?>">
<?php if ($seo['schema'] !== null): ?>
<script type="application/ld+json"><?= dz_seo_json($seo['schema']) ?></script>
<?php endif; ?>
