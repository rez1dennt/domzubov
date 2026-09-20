<?php
function doctor_portrait(array $doctor,string $class='',bool $eager=false):string {
    if($doctor['photoAvailable'])return '<img class="'.e($class).'" src="'.e($doctor['photoLocal']).'" alt="'.e($doctor['name']).'" width="750" height="1000" loading="'.($eager?'eager':'lazy').'" '.($eager?'fetchpriority="high"':'').' decoding="'.($eager?'sync':'async').'">';
    return '<div class="dz-portrait-pending '.e($class).'" role="img" aria-label="Фотография '.e($doctor['name']).' пока не добавлена"><span>'.e($doctor['initials']).'</span><small>Фото скоро появится</small></div>';
}
function doctor_card(array $doctor,bool $team=false):string {
 ob_start(); ?>
 <article class="dz-doctor-card dz-real-doctor-card <?= $team?'a11y-doctor-card':'' ?>" <?= $team?'data-team-card':'data-doctor-card' ?> data-search="<?= e($doctor['name'].' '.$doctor['profession'].' '.$doctor['description']) ?>" data-clinics="1" data-specialties="<?= e(implode(',',$doctor['specializations'])) ?>">
   <a class="dz-doctor-portrait-link" href="<?= e($doctor['url']) ?>" aria-label="Подробнее: <?= e($doctor['name']) ?>"><?= doctor_portrait($doctor) ?></a>
   <div class="dz-doctor-card-body"><p class="dz-doctor-card-specialty"><?= e($doctor['profession']) ?></p><h2><a href="<?= e($doctor['url']) ?>"><?= e($doctor['name']) ?></a></h2><p class="dz-doctor-card-description"><?= e($doctor['description']) ?></p><p class="dz-doctor-card-schedule"><?= icon('calendar') ?><span><?= e($doctor['schedule']) ?></span></p><div class="dz-doctor-card-actions"><a class="dz-doctor-profile-link" href="<?= e($doctor['url']) ?>">О враче <?= icon('arrow') ?></a><a class="dz-btn" href="<?= e(BOOKING_URL) ?>">Записаться <?= icon('arrow') ?></a></div></div>
 </article>
 <?php return (string)ob_get_clean();
}
