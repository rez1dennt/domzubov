<?php
$reviews=json_decode(file_get_contents(PROJECT_ROOT.'/content/reviews.json'),true)['results'];
$reviewDoctors=[];$reviewServices=[];
foreach($reviews as $review) {
    if($review['doctor']) $reviewDoctors[$review['doctor']['id']]=$review['doctor']['full_name'];
    if($review['service']) $reviewServices[$review['service']['id']]=$review['service']['name'];
}
asort($reviewDoctors);asort($reviewServices);
?>
<div class="dz-reviews" data-reviews>
<div class="dz-review-filters">
<label data-review-doctor-label hidden><span>Специалист</span><select data-review-doctor><option value="">Все специалисты</option><?php foreach($reviewDoctors as $id=>$name): ?><option value="<?= (int)$id ?>"><?= e($name) ?></option><?php endforeach; ?></select></label>
<label data-review-service-label hidden><span>Услуга</span><select data-review-service><option value="">Все услуги</option><?php foreach($reviewServices as $id=>$name): ?><option value="<?= (int)$id ?>"><?= e($name) ?></option><?php endforeach; ?></select></label>
</div>
<p class="dz-doctor-count" data-review-count aria-live="polite"></p>
<div class="dz-review-grid">
<?php foreach($reviews as $review): ?>
<article class="dz-review-card" data-review data-doctor="<?= (int)($review['doctor']['id']??0) ?>" data-service="<?= (int)($review['service']['id']??0) ?>">
<div class="dz-review-top"><span class="dz-quote" aria-hidden="true">“</span><span class="dz-review-rating" aria-label="Оценка <?= (int)$review['rating'] ?> из 5"><?= str_repeat('★',(int)$review['rating']) ?></span></div>
<p class="dz-review-body"><?= nl2br(e(brand_text($review['text']))) ?></p>
<button class="dz-review-expand" type="button" aria-expanded="false">Читать полностью</button>
<div class="dz-review-author"><strong><?= e($review['author_name']) ?></strong><time datetime="<?= e(substr($review['created_at'],0,10)) ?>"><?= e(date('d.m.Y',strtotime($review['created_at']))) ?></time></div>
<?php if($review['doctor']): ?><?php if(isset(routes()['doctors/'.$review['doctor']['slug']])): ?><a class="dz-review-specialist" href="/doctors/<?= e($review['doctor']['slug']) ?>"><?= e($review['doctor']['full_name']) ?></a><?php else: ?><span class="dz-review-specialist"><?= e($review['doctor']['full_name']) ?></span><?php endif; ?><?php endif; ?>
</article>
<?php endforeach; ?>
</div>
<nav class="dz-pagination" data-review-pagination aria-label="Страницы отзывов"><button class="dz-icon-btn" type="button" data-review-prev aria-label="Предыдущая страница отзывов">←</button><span data-review-page></span><button class="dz-icon-btn" type="button" data-review-next aria-label="Следующая страница отзывов">→</button></nav>
</div>
