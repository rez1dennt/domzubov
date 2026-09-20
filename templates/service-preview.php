<?php
function service_preview(): void { $cards=[
['prevention','Лечение зубов','Сохраняем зубы и возвращаем комфорт.','/services/lechenie-kariesa'],
['implant','Имплантация','Восстанавливаем утраченные зубы.','/services/ustanovka-implanta'],
['orthodontics','Исправление прикуса','Брекеты, элайнеры и забота об улыбке.','/services/ispravlenie-prikusa'],
['consultation','Эстетика улыбки','Обсудим изменения, которые нужны именно вам.','/services/otbelivanie-zubov'],
]; ?><div class="dz-preview-grid"><?php foreach($cards as [$photo,$name,$description,$link]): ?><a class="dz-preview-card" href="<?= e($link) ?>"><img src="/assets/images/<?= $photo ?>.webp" alt="<?= e($name) ?> — иллюстрация" width="600" height="450" loading="lazy"><div><span class="dz-medical-icon"><?= medical_icon() ?></span><h3><?= e($name) ?></h3><p><?= e($description) ?></p><span class="dz-preview-arrow"><?= icon('arrow') ?></span></div></a><?php endforeach; ?></div><?php }
