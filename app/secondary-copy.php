<?php
declare(strict_types=1);

function dz_secondary_text(?Dom\Element $element, string $text): void
{
    if ($element !== null) $element->textContent = $text;
}

function dz_secondary_text_preserve(?Dom\Element $element, string $text): void
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

function dz_secondary_exact(Dom\Element $root, string $selector, string $text): ?Dom\Element
{
    foreach ($root->querySelectorAll($selector) as $element) {
        if (trim((string) preg_replace('/\s+/u', ' ', $element->textContent)) === $text) return $element;
    }
    return null;
}

function rewrite_secondary_copy(Dom\HTMLDocument $doc, Dom\Element $root, string $route): void
{
    $route = '/' . trim($route, '/');

    if ($route === '/o-nas') {
        $clinicIq = dz_secondary_exact($root, '.font-medium.uppercase', 'УМНАЯ СИСТЕМА CLINICIQ');
        if ($clinicIq !== null) {
            dz_secondary_text($clinicIq, 'СОПРОВОЖДЕНИЕ ПАЦИЕНТА');
            $description = $clinicIq->nextElementSibling;
            if ($description !== null) {
                dz_secondary_text(
                    $description,
                    'Администратор помогает подготовиться к визиту, напоминает о записи и подсказывает, как связаться с клиникой после приёма.'
                );
            }
        }

        $founderTitle = dz_secondary_exact($root, 'h3', 'Основатель клиники');
        if ($founderTitle !== null) {
            $copy = $founderTitle->parentElement;
            dz_secondary_text($founderTitle, 'Подход команды');
            dz_secondary_text($copy?->querySelector('h4'), 'ЗАБОТА, ПОНЯТНЫЙ ПЛАН И ДИАЛОГ');
            $paragraphs = $copy ? iterator_to_array($copy->querySelectorAll('p')) : [];
            dz_secondary_text_preserve(
                $paragraphs[0] ?? null,
                'Команда «Дома Зубов» объединяет специалистов, которые обсуждают клиническую ситуацию и согласуют последовательность лечения.'
            );
            dz_secondary_text(
                $paragraphs[1] ?? null,
                'На консультации врач выслушивает пациента, проводит осмотр и объясняет, какие диагностические данные нужны для выбора тактики.'
            );
            dz_secondary_text_preserve(
                $paragraphs[2] ?? null,
                'Если в лечении участвуют специалисты разных профилей, их рекомендации объединяют в один план. Пациент видит этапы, альтернативы и стоимость до начала процедур.'
            );
            $quote = $founderTitle->parentElement?->parentElement?->querySelector('.bg-abyssal p');
            dz_secondary_text($quote, '«Мы объясняем решения простыми словами и оставляем пациенту время задать вопросы»');
        }

        $historyTitle = dz_secondary_exact($root, 'h2', 'Наша история');
        if ($historyTitle !== null) {
            $historySection = $historyTitle;
            while ($historySection !== null && $historySection->localName !== 'section') {
                $historySection = $historySection->parentElement;
            }
            dz_secondary_text($historyTitle, 'Как строится забота о вас');
            dz_secondary_text(
                $historyTitle->nextElementSibling,
                'Шесть последовательных шагов — от первого разговора до поддерживающих визитов'
            );
            if ($historySection !== null) {
                $stepNumbers = iterator_to_array($historySection->querySelectorAll('h3.md\:text-p40'));
                $stepTexts = iterator_to_array($historySection->querySelectorAll('p.md\:text-p16'));
                $steps = [
                    'Знакомимся и обсуждаем, что вас беспокоит, чего вы ожидаете от лечения и какой прошлый опыт важно учесть.',
                    'Проводим осмотр и назначаем только те исследования, которые нужны для уточнения клинической ситуации.',
                    'Объясняем результаты диагностики простыми словами, показываем найденные изменения и отвечаем на вопросы.',
                    'Сравниваем возможные варианты, согласуем последовательность этапов и заранее обсуждаем стоимость.',
                    'Проводим лечение в согласованном темпе. Если нужны специалисты разных профилей, координируем их работу.',
                    'Проверяем результат, даём рекомендации по домашнему уходу и планируем профилактические визиты.'
                ];
                foreach ($stepNumbers as $index => $number) {
                    if ($index < 6) $number->textContent = sprintf('%02d', $index + 1);
                }
                foreach ($stepTexts as $index => $text) {
                    if (isset($steps[$index])) $text->textContent = $steps[$index];
                }
            }
        }

        $factsTitle = dz_secondary_exact($root, 'div', 'Факты и цифры');
        if ($factsTitle !== null) {
            $factsSection = $factsTitle;
            while ($factsSection !== null && $factsSection->localName !== 'section') {
                $factsSection = $factsSection->parentElement;
            }
            dz_secondary_text($factsTitle, 'Что доступно пациентам');
            dz_secondary_text(
                $factsTitle->nextElementSibling,
                'Проверяемые сведения о записи, адресах и организации лечения в «Доме Зубов»'
            );
            if ($factsSection !== null) {
                $facts = [
                    'Приём проходит по адресу: Москва, Симферопольский бульвар, 24, корп. 4. Часы работы: 09:00–21:00.',
                    'В клинике представлены основные направления стоматологии для взрослых и детей.',
                    'Онлайн-запись доступна на сайте: можно выбрать подходящее время без звонка.',
                    'До начала процедур врач объясняет этапы и обсуждает план лечения с пациентом.',
                    'Единый телефон для записи и вопросов: +7 (928) 699-67-84.'
                ];
                $factNodes = iterator_to_array($factsSection->querySelectorAll('.md\:text-p18.text-p16, .text-p16:not(.md\:text-p18)'));
                foreach ($factNodes as $index => $node) {
                    dz_secondary_text_preserve($node, $facts[$index % count($facts)]);
                }
            }
        }
        return;
    }

    if ($route === '/programma-blagodarnosti') {
        $locationsHeading = dz_secondary_exact($root, 'h2', 'Локации и контакты');
        $locations = $locationsHeading?->parentElement;
        if ($locations !== null) {
            $desktop = $locations->querySelector('.md\:w-\[319px\]');
            if ($desktop !== null) {
                $cards = [];
                foreach ($desktop->children as $index => $child) {
                    if ($index > 0) $cards[] = $child;
                }
                $own = array_map(static fn(array $location):array=>[$location['area'],$location['address']],company_locations());
                foreach ($cards as $index => $card) {
                    if ($index >= count($own)) {
                        $card->remove();
                        continue;
                    }
                    $lines = iterator_to_array($card->querySelectorAll('.text-p14'));
                    dz_secondary_text_preserve($lines[0] ?? null, $own[$index][0]);
                    dz_secondary_text($lines[1] ?? null, $own[$index][1]);
                }
            }
            $options = iterator_to_array($locations->querySelectorAll('select option'));
            $optionLabels = array_column(company_locations(),'address');
            foreach ($options as $index => $option) {
                if ($index >= count($optionLabels)) $option->remove();
                else $option->textContent = $optionLabels[$index];
            }
            $summary = $locations->querySelector('h2 + p');
            dz_secondary_text($summary, 'Москва, Симферопольский бульвар, 24, корп. 4. Часы работы: 09:00–21:00.');
        }

        $telegramTitle = dz_secondary_exact($root, 'h3', 'НАПИСАТЬ В TELEGRAM');
        if ($telegramTitle !== null) {
            $card = $telegramTitle->parentElement;
            dz_secondary_text($telegramTitle, 'ЗАПИСАТЬСЯ ОНЛАЙН');
            $image = $card?->querySelector('.flex-grow img');
            if ($image !== null) {
                $image->setAttribute('src', '/assets/brand.svg');
                $image->setAttribute('alt', 'Дом Зубов');
            }
            dz_secondary_text(
                $card?->querySelector('.flex-grow p'),
                'Выберите время онлайн или напишите нам: Domzubov777@yandex.ru'
            );
            $link = $card?->querySelector('a[href]');
            if ($link !== null) {
                $link->setAttribute('href', 'https://idotvip.ru/domzubov');
                $link->setAttribute('aria-label', 'Записаться онлайн');
                $link->removeAttribute('target');
                $link->removeAttribute('rel');
            }
        }
        return;
    }

    if ($route === '/pravila-zapisi') {
        $telegramItem = dz_secondary_exact($root, 'li', 'в нашем официальном Telegram-аккаунте');
        if ($telegramItem !== null) {
            $telegramItem->textContent = 'через ';
            $link = $doc->createElement('a');
            $link->setAttribute('href', 'https://idotvip.ru/domzubov');
            $link->setAttribute('class', 'underline');
            $link->textContent = 'форму онлайн-записи';
            $telegramItem->append($link);
        }
    }
}
