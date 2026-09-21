<?php
declare(strict_types=1);

function dz_home_set_text(?Dom\Element $element, string $text): void
{
    if ($element !== null) {
        $element->textContent = $text;
    }
}

/**
 * Replaces only direct text nodes, preserving existing <br> and other elements.
 * The number of segments should match the text runs around those elements.
 */
function dz_home_set_segments(?Dom\Element $element, array $segments): void
{
    if ($element === null) return;
    $index = 0;
    foreach ($element->childNodes as $child) {
        if ($child instanceof Dom\Text) {
            $child->data = (string) ($segments[$index] ?? '');
            $index++;
        }
    }
}

function dz_home_set_text_preserve(?Dom\Element $element, string $text): void
{
    if ($element === null) return;
    $nodes = [];
    $collect = function (Dom\Node $parent) use (&$collect, &$nodes): void {
        foreach ($parent->childNodes as $child) {
            if ($child instanceof Dom\Text) $nodes[] = $child;
            elseif ($child instanceof Dom\Element) $collect($child);
        }
    };
    $collect($element);
    if (!$nodes) {
        $element->append($text);
        return;
    }
    foreach ($nodes as $index => $node) $node->data = $index === 0 ? $text : '';
}

function rewrite_home_copy(Dom\HTMLDocument $doc, Dom\Element $container): void
{
    $clinic = $container->querySelector('[data-section="3"]');
    if ($clinic !== null) {
        dz_home_set_text($clinic->querySelector('[class*="text-p36"]'), 'Наша клиника');
    }
    // Hero: retain the original multi-span heading and both action buttons.
    $hero = $container->querySelector('.a11y-hero-wrapper');
    if ($hero !== null) {
        $words = ['«ДОМ', 'ЗУБОВ»', '—', 'СТОМАТОЛОГИЯ', 'С ЗАБОТОЙ'];
        foreach ($hero->querySelectorAll('h1 span') as $index => $span) {
            if (isset($words[$index])) $span->textContent = $words[$index];
        }
        dz_home_set_text(
            $hero->querySelector('.whitespace-pre-line'),
            'Диагностика, профилактика и лечение для взрослых — спокойно, понятно и по согласованному плану.'
        );
    }

    // 1. Founder block becomes a collective care philosophy; portrait/layout stay intact.
    $about = $container->querySelector('[data-section="1"]');
    if ($about !== null) {
        dz_home_set_segments($about->querySelector('.text-p20.xl\:text-p36'), [
            'В «Доме Зубов» лечение начинается с внимательного разговора — от жалобы ',
            ' к точной диагностике, от отдельных процедур ',
            ' к понятному плану.'
        ]);
        $quote = $about->querySelector('.text-p10.xl\:text-p14 > div');
        dz_home_set_segments($quote, [
            '«Мы бережно относимся к здоровым тканям, объясняем варианты лечения и не торопим с решением. Наша задача — ',
            'помочь пациенту понять ситуацию и вместе выбрать обоснованный путь к здоровой улыбке».'
        ]);
        dz_home_set_text(
            $about->querySelector('.text-p10.xl\:text-p12.italic'),
            'Команда стоматологии «Дом Зубов»'
        );
    }

    // 2. Preserve the wide lead card plus six service cards.
    $services = $container->querySelector('[data-section="2"]');
    if ($services !== null) {
        $uppercase = iterator_to_array($services->querySelectorAll('.uppercase'));
        $descriptions = iterator_to_array($services->querySelectorAll('.text-p14.mt-\[15px\]'));
        $titles = [
            'Стоматология для взрослых — с вниманием к самочувствию и задачам каждого пациента.',
            'Здоровье начинается с профилактики.',
            'Консультация без спешки.',
            'Комфорт во время лечения.',
            'Естественная эстетика улыбки.',
            'Лечение с сохранением тканей.',
            'Хирургия по понятному плану.'
        ];
        $texts = [
            'Гигиена и регулярные осмотры — забота о зубах до появления проблемы.',
            'Обсуждаем жалобы, объясняем результаты осмотра и составляем план лечения.',
            'Обсудим тревогу и подберём подходящий способ обезболивания по показаниям.',
            'Подбираем форму и оттенок с учётом вашей улыбки и состояния зубов.',
            'От кариеса до сложного восстановления — последовательно и бережно.',
            'Удаление, имплантация и восстановление тканей — после тщательной подготовки.'
        ];
        foreach ($uppercase as $index => $node) {
            if (isset($titles[$index])) dz_home_set_text_preserve($node, $titles[$index]);
        }
        foreach ($descriptions as $index => $node) {
            if (isset($texts[$index])) $node->textContent = $texts[$index];
        }
    }

    // 4. Technology section: medically cautious descriptions, unchanged cards/gallery.
    $technology = $container->querySelector('[data-section="4"]');
    if ($technology !== null) {
        dz_home_set_text(
            $technology->querySelector('.md\:text-p18.text-p14.text-darkText'),
            'Технологии помогают врачу собирать больше диагностических данных, точнее планировать отдельные этапы и контролировать ход лечения. Подходящие методы выбирают по клинической ситуации.'
        );
        $quoteCard = $technology->querySelector('.bg-abyssal');
        if ($quoteCard !== null) {
            $quoteText = $quoteCard->querySelector('.text-p14.xl\:text-p18');
            dz_home_set_text_preserve($quoteText, 'Мы используем оборудование как инструмент врача: по показаниям, с объяснением пользы и ограничений для пациента.');
        }

        $featureTitles = iterator_to_array($technology->querySelectorAll('.font-medium.uppercase'));
        $featureTexts = iterator_to_array($technology->querySelectorAll('.font-medium.uppercase + .text-p14'));
        $newTitles = [
            'УВЕЛИЧЕНИЕ И ОПТИКА',
            'ТРЁХМЕРНАЯ ДИАГНОСТИКА',
            'ДЕНТАЛЬНЫЙ ЛАЗЕР',
            'МОНИТОРИНГ ВО ВРЕМЯ ЛЕЧЕНИЯ',
            'ЦИФРОВОЕ ПЛАНИРОВАНИЕ'
        ];
        $newTexts = [
            'Оптическое увеличение помогает рассмотреть рабочую область и аккуратно выполнять этапы, где особенно важна точность.',
            'Объёмные снимки назначают по показаниям, когда врачу нужно оценить положение зубов, корней и окружающих тканей.',
            'Лазер может применяться для отдельных манипуляций с мягкими тканями. Решение зависит от диагноза и плана лечения.',
            'Во время продолжительных процедур команда наблюдает за состоянием пациента и использует необходимое контрольное оборудование.',
            'Снимки, фотографии и цифровые модели помогают сопоставить данные и заранее обсудить последовательность лечения.'
        ];
        foreach ($featureTitles as $index => $node) {
            if (isset($newTitles[$index])) $node->textContent = $newTitles[$index];
        }
        foreach ($featureTexts as $index => $node) {
            if (isset($newTexts[$index])) $node->textContent = $newTexts[$index];
        }
    }

    // 5. Gift block.
    $gift = $container->querySelector('[data-section="5"]');
    if ($gift !== null) {
        dz_home_set_text($gift->querySelector('.md\:text-p36.text-p24'), 'Подарите заботу');
        dz_home_set_segments($gift->querySelector('.md\:text-p18.text-p14'), [
            'Сертификат «Дом Зубов» — деликатный способ поддержать близкого, который планирует заняться здоровьем зубов. ',
            'Актуальные номиналы и условия использования уточните у администратора.'
        ]);
    }

    // Keep the existing section and contact form, with the confirmed payment conditions.
    $insurance = $container->querySelector('[data-section="9"]');
    if ($insurance !== null) {
        $intro=$insurance->querySelector('.text-abyss.md\:text-p18');
        if($intro){
            $intro->previousElementSibling->textContent='Условия приёма';
            $intro->textContent=COMPANY_INSURANCE_NOTICE.' Стоимость и план лечения обсуждаются до начала процедур.';
            $partners=$intro->nextElementSibling;
            if($partners?->querySelector('[data-dms-card]'))$partners->remove();
        }
        dz_home_set_text($insurance->querySelector('.md\:text-p36.text-p20.md\:text-\[p36\]'),'Есть вопросы перед приёмом?');
        dz_home_set_text($insurance->querySelector('.md\:text-p18.text-p14.font-medium'),'Напишите нам — поможем разобраться в стоимости услуг и порядке записи.');
    }
}
