<?php
require_once PROJECT_ROOT.'/app/doctors-data.php';
$data=doctor_data();$locations=company_locations();$selectedClinic='';
foreach($locations as $index=>$location){if(str_starts_with($route,$location['slug'].'/')){$selectedClinic=$index+1;break;}}
if($selectedClinic!==''){$hero=PROJECT_ROOT.'/content/shared/'.explode('/',$route)[0].'-hero.html';if(is_file($hero))readfile($hero);}
?>
<section class="dz-doctors dz-shell dz-light" data-doctor-directory data-initial-clinic="<?= e((string)$selectedClinic) ?>">
<p class="dz-eyebrow">ДОМ ЗУБОВ</p><?php if($selectedClinic!==''): ?><h2 class="dz-directory-title">Специалисты клиники</h2><?php else: ?><h1>Врачи</h1><?php endif; ?>
<div class="dz-doctor-filters">
 <label class="dz-search"><span class="dz-sr-only">Введите имя или специализацию врача</span><svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="6" stroke="currentColor" stroke-width="1.5"/><path d="m15 15 6 6" stroke="currentColor" stroke-width="1.5"/></svg><input type="search" data-doctor-search placeholder="Введите имя или специализацию врача"></label>
<p class="dz-doctor-location"><?= e(company_locations()[0]['address']) ?><br>Часы работы: <?= COMPANY_HOURS ?></p>
</div>
<div class="dz-specialties" aria-label="Специализации врачей"><button type="button" data-specialty="" aria-pressed="true">ВСЕ СПЕЦИАЛИЗАЦИИ</button><?php foreach($data['specializations'] as $spec): ?><button type="button" data-specialty="<?= (int)$spec['id'] ?>" aria-pressed="false"><?= e($spec['title']) ?></button><?php endforeach; ?></div>
<p class="dz-doctor-count" aria-live="polite" data-doctor-count></p>
<?php require_once __DIR__.'/doctor-card.php'; ?><div class="dz-doctors-grid"><?php foreach($data['doctors'] as $doctor)echo doctor_card($doctor); ?></div>
<p class="dz-no-results" data-no-results hidden>Специалисты не найдены. Попробуйте изменить запрос или выбрать другую специализацию.</p>
<nav class="dz-pagination" data-doctor-pagination aria-label="Страницы списка специалистов" hidden><button class="dz-icon-btn" type="button" data-doctor-prev aria-label="Предыдущая страница">←</button><span data-doctor-page></span><button class="dz-icon-btn" type="button" data-doctor-next aria-label="Следующая страница">→</button></nav>
</section>
