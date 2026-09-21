<?php
declare(strict_types=1);

/** Temporarily unpublished while the clinic offers care to adults only. */
function dz_unpublished_routes(): array {
    return [
        'services/adaptation-visit-for-children',
        'services/bite-correction',
        'services/germetizacia-fisur-y-detei',
        'services/lechenie-detskogo-cariesa-i-ego-osloznenie',
        'services/palatal-expander',
        'services/profilaktika-i-diagnostika-detei',
        'services/professionalnaya-gigiena-dlya-detej-i-podrostkov',
        'akciya-skidka-detskoe-lechenie', 'kids-holidays', 'novogodnyaya-skidka',
    ];
}

function dz_route_unpublished(string $route): bool {
    $route = trim($route, '/');
    $route = preg_replace('~\.(?:html|php)$~i', '', $route);
    $route = preg_replace('~/index$~', '', $route);
    if (in_array($route, dz_unpublished_routes(), true)) return true;
    // Imported profiles are historical records, not current pediatric offers.
    $record = routes()[$route] ?? [];
    return str_starts_with($route, 'doctors/') && preg_match('~Детский врач|детск.*стоматолог~iu', $record['title'] ?? '') === 1;
}

/** Remove unpublished blocks from imported layouts without changing the archive. */
function dz_adult_care_layout(Dom\HTMLDocument $doc, Dom\Element $root, string $route): void {
    foreach (iterator_to_array($root->querySelectorAll('*')) as $node) {
        if (!$node->isConnected || $node->childElementCount !== 0) continue;
        $text = trim($node->textContent);
        if ($text === 'Детская стоматология') {
            $block = $route === 'services'
                ? $node->closest('.overflow-hidden')
                : $node->closest('section');
            if ($block) $block->remove();
        } elseif ($route === 'index' && $text === 'Лечение детей') {
            $node->closest('.cursor-pointer')?->remove();
        } elseif ($route === 'services/indream' && $text === 'Лечение во сне для детей') {
            $block = $node;
            while ($block->parentElement && $block->parentElement !== $root) $block = $block->parentElement;
            $block->remove();
        } elseif (in_array($text, ['Подходит ли для детей?', 'Можно ли делать КТ детям?', 'С какого возраста ребёнка можно учить чистить зубы?', 'Можно ли отбеливать зубы подросткам?', 'Нужно ли готовить ребёнка к визиту?', 'Что делать, если малыш всё равно плачет?'], true)) {
            $node->closest('.section-box')?->remove();
        } elseif ($node->localName === 'span' && preg_match('~^(?:Консультация детского|Повторная консультация детского|Детский адаптационный|Профессиональная гигиена.*молочный|Седация закисью азота.*для детей)~u', $text)) {
            $node->parentElement?->remove();
        } elseif ($node->localName === 'a' && dz_route_unpublished((string) $node->getAttribute('href'))) {
            $node->remove();
        }
    }
    // These are precise edits to mixed adult/pediatric explanatory paragraphs.
    dz_replace_text($root, [
        'облегчает домашний уход для всей семьи' => 'облегчает домашний уход для взрослых пациентов',
        'Прицельные снимки безопасны и подходят даже детям' => 'Прицельные снимки назначаются по показаниям врача',
        ' — особенно это важно для детей и пациентов с чувствительной эмалью' => ' — особенно это важно для пациентов с чувствительной эмалью',
        'Исследование подходит даже детям и кормящим мамам' => 'Необходимость исследования определяет врач',
        'Методика подходит и детям, и взрослым, так как не перегружает организм и не требует длительного восстановления' => 'Возможность применения метода у взрослого пациента врач оценивает с учётом состояния здоровья и объёма лечения',
        'безопасной для взрослых и детей' => 'подходящей для взрослых пациентов по показаниям',
        ' Родителям рассказываем, как чистить зубы детям до того возраста, когда они смогут делать это сами.' => '',
        'Для детей превращаем процесс в игру, чтобы он приносил радость' => 'Отрабатываем технику чистки, чтобы её было удобно повторять дома',
        'Учитываем возраст и индивидуальные особенности (для детей, взрослых, пациентов с брекетами или каппами)' => 'Учитываем состояние зубов и особенности ухода при брекетах или каппах',
        'из-за наследственности, нехватки места или ранней потери молочных зубов' => 'из-за наследственности или нехватки места',
        'Особенно удобно для детей и пациентов с повышенной чувствительностью.' => 'Возможность такого лечения оценивает врач после обследования взрослого пациента.',
    ]);
    if ($route === 'pravila-zapisi') {
        foreach (iterator_to_array($root->querySelectorAll('li,p')) as $node) {
            if (preg_match('~свидетельство о рождении|Присутствие родителя|паспорт родителя~u', $node->textContent)) $node->remove();
        }
    }
    if ($route === 'tax-deduction') {
        foreach ($root->querySelectorAll('div') as $node) {
            if ($node->childElementCount === 0 && str_contains($node->textContent, 'медицинские услуги, в том числе дорогостоящие, оказанные')) {
                $node->textContent = 'Документы об оплате лечения можно запросить у администратора. Право на вычет, состав документов и действующие условия уточняйте в ФНС.';
            }
        }
    }
}
