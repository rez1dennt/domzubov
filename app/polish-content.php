<?php
/** Shared final corrections for the standalone legacy page fragments. */
require_once __DIR__ . '/semantic-icons.php';

function dz_polish_content(string $html, string $route): string {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><html><body><div id="dz-polish-root">'.$html.'</div></body></html>', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $xp = new DOMXPath($doc);
    $root = $doc->getElementById('dz-polish-root');
    if (!$root) return $html;
    $route = trim($route, '/');
    $add_class = static function (DOMElement $node, string $class): void {
        if (!in_array($class, preg_split('/\s+/', $node->getAttribute('class')), true)) $node->setAttribute('class', trim($node->getAttribute('class').' '.$class));
    };
    $set_text = static function (DOMElement $node, string $text) use ($doc): void {
        while ($node->firstChild) $node->removeChild($node->firstChild);
        $node->appendChild($doc->createTextNode($text));
    };
    $set_icon = static function (DOMElement $surface, string $key) use ($doc): void {
        $svg = new DOMDocument();
        $svg->loadXML(dz_semantic_icon($key), LIBXML_NONET);
        while ($surface->firstChild) $surface->removeChild($surface->firstChild);
        $surface->appendChild($doc->importNode($svg->documentElement, true));
    };

    // Choose by each card's meaning, with a distinct icon for every card in its grid.
    $rules = [
        'pin' => '/адрес|бульвар|локаци/ui', 'phone' => '/телефон|звонк/ui',
        'calendar' => '/онлайн-запис|записаться|визит|приём/ui', 'family' => '/взросл.*дет|семь/ui',
        'heart' => '/забот|довер|отношен/ui', 'shield' => '/безопас|стерил|защит/ui',
        'technology' => '/инновац|технолог|цифров/ui', 'target' => '/предсказуем|точност|точн/ui',
        'enamel' => '/эмал|пятн|цвет/ui', 'fracture' => '/полость|скол|трещин|разруш/ui',
        'temperature' => '/чувствитель|холод|горяч/ui', 'restore' => '/пломб|повтор|перелеч/ui',
        'microscope' => '/микроскоп|увеличен/ui', 'scan' => '/томограф|снимок|\bкт\b|рентген/ui',
        'search' => '/диагност|осмотр|выяв|скрыт/ui', 'crown' => '/коронк|керамик|циркон/ui',
        'implant' => '/имплант/ui', 'aligners' => '/элайнер|капп/ui', 'braces' => '/брекет/ui',
        'brush' => '/гигиен|щ[её]тк|нал[её]т|очищ/ui', 'sparkles' => '/эстет|отбел|красот|винир/ui',
        'child' => '/реб[её]н|дет|молочн/ui', 'moon' => '/сон|седац|тревог|страх|анестез/ui',
        'pulse' => '/контрол|наблюд|боль|воспален/ui', 'air' => '/дыхани|газ|кислород/ui',
        'bone' => '/кост|опор/ui', 'growth' => '/десн|ткан|пульп/ui',
        'balance' => '/прикус|положени|жеван/ui', 'ruler' => '/размер|форм|длин|объ[её]м/ui',
        'wallet' => '/стоим|цен|оплат/ui', 'plan' => '/план|этап|подготов/ui',
        'clock' => '/врем|срок|быстр/ui', 'dialogue' => '/обсужд|объясн|консульт|вопрос/ui',
        'leaf' => '/береж|сохран|щадящ/ui', 'book' => '/рекоменд|обуч|уход/ui',
        'layers' => '/восстанов|протез|конструк/ui', 'smile' => '/улыб/ui',
    ];
    $fallback = ['search','plan','shield','leaf','target','layers','dialogue','check','clock','heart','book','smile'];
    foreach ($xp->query('//*[@data-feature-grid]') as $grid) {
        $used = [];
        foreach ($xp->query('.//*[@data-feature-card]', $grid) as $card) {
            $surface = $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," dz-icon-surface ")]', $card)->item(0);
            if (!$surface) continue;
            $title = $xp->query('.//*[@data-feature-title]', $card)->item(0);
            $candidates = [];
            foreach ([$title ? $title->textContent : '', $card->textContent, str_replace('-', ' ', $route)] as $text) {
                foreach ($rules as $key => $pattern) if (preg_match($pattern, $text)) $candidates[] = $key;
            }
            foreach (array_merge($candidates, $fallback, array_keys(dz_semantic_icon_catalog())) as $key) {
                if (!isset($used[$key])) { $set_icon($surface, $key); $used[$key] = true; break; }
            }
        }
    }

    // Some legacy indication rows are outside the annotated feature grid.
    if (str_starts_with($route, 'services/')) {
        $other_surfaces = $xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," dz-icon-surface ") and not(ancestor::*[@data-feature-card])]');
        foreach ($other_surfaces as $surface) foreach ($xp->query('.//*[@data-dz-icon]', $surface) as $svg) $svg->removeAttribute('data-dz-icon');
        foreach ($other_surfaces as $surface) {
            $section = $xp->query('ancestor::section[1]', $surface)->item(0) ?: $surface->parentNode->parentNode;
            $used = [];
            foreach ($xp->query('.//*[@data-dz-icon]', $section) as $svg) $used[$svg->getAttribute('data-dz-icon')] = true;
            $candidates = [];
            $text = $surface->parentNode->textContent;
            foreach ($rules as $key => $pattern) if (preg_match($pattern, $text)) $candidates[] = $key;
            if (preg_match('/рефлекс|спазм/u', $text)) array_unshift($candidates, 'air');
            if (preg_match('/Мониторинг|показател/u', $text)) array_unshift($candidates, 'pulse');
            if (str_contains($text, 'Видеозапись')) array_unshift($candidates, 'technology');
            foreach (array_merge($candidates, $fallback, array_keys(dz_semantic_icon_catalog())) as $key) {
                if (!isset($used[$key])) { $set_icon($surface, $key); break; }
            }
        }
    }

    if ($route === 'o-nas') {
        foreach ($xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," dz-icon-surface ")]') as $surface) {
            $text = $surface->parentNode->textContent;
            foreach ($rules as $key => $pattern) if (preg_match($pattern, $text)) { $set_icon($surface, $key); break; }
            if (str_contains($text, 'Онлайн-запись')) $set_icon($surface, 'calendar');
            if (str_contains($text, 'До начала процедур')) $set_icon($surface, 'plan');
            if (str_contains($text, 'основные направления')) $set_icon($surface, 'family');
        }
        $approach = $xp->query('//h3[normalize-space(.)="Подход команды"]')->item(0);
        if ($approach) {
            $split = $approach->parentNode->parentNode;
            $add_class($split, 'dz-about-approach');
            $media = $xp->query('./div[2]', $split)->item(0);
            if ($media) {
                $add_class($media, 'dz-about-approach-media');
                $quote = $xp->query('./div[2]', $media)->item(0);
                if ($quote) {
                    $add_class($quote, 'dz-about-approach-quote');
                    $p = $xp->query('.//p', $quote)->item(0);
                    if ($p) $set_text($p, 'Команда объясняет решения простыми словами и оставляет пациенту время задать вопросы.');
                }
            }
        }
        // Use the same factual profiles as the directory and individual doctor pages.
        $team_heading = $xp->query('//*[not(*) and normalize-space(.)="Наша команда"]')->item(0);
        if ($team_heading) {
            $team_doc = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            $team_html = implode('', array_map(static fn(array $doctor): string => doctor_card($doctor, true), doctor_data()['doctors']));
            $team_doc->loadHTML('<?xml encoding="UTF-8"><div data-real-team>'.$team_html.'</div>', LIBXML_NONET);
            libxml_clear_errors(); libxml_use_internal_errors($previous);
            $team_xp = new DOMXPath($team_doc);
            $cards = $team_xp->query('//*[@data-real-team]/*[@data-team-card]');
            if ($cards->length === 4) {
                $container = $team_heading->parentNode;
                $intro = $xp->query('./p', $container)->item(0);
                if ($intro) $set_text($intro, 'Познакомьтесь с врачами «Дома Зубов»: их направлениями работы и профессиональным опытом.');
                $old_grid = $xp->query('./div[contains(@class,"grid-cols")]', $container)->item(0);
                if ($old_grid) {
                    $grid = $doc->createElement('div'); $grid->setAttribute('class', 'dz-about-team-grid');
                    foreach ($cards as $card) {
                        $copy = $doc->importNode($card, true); $copy->removeAttribute('data-team-card'); $copy->removeAttribute('hidden');
                        $grid->appendChild($copy);
                    }
                    $container->replaceChild($grid, $old_grid);
                }
            }
        }
    }

    if (str_starts_with($route, 'services/')) {
        foreach ($xp->query('//*[not(*) and normalize-space(.)="Наши специалисты"]') as $heading) {
            $grid = $xp->query('following-sibling::div[1]', $heading)->item(0);
            if (!$grid || $xp->query('.//*[contains(concat(" ",normalize-space(@class)," ")," dz-real-doctor-card ")]', $grid)->length) continue;
            $add_class($grid, 'dz-service-specialists');
            $grid->setAttribute('data-service-specialists', '');
            $cards_doc = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            $cards_doc->loadHTML('<?xml encoding="UTF-8"><div id="dz-service-team">'.implode('', array_map(static fn(array $doctor): string => doctor_card($doctor), dz_service_doctors($route))).'</div>', LIBXML_NONET);
            libxml_clear_errors(); libxml_use_internal_errors($previous);
            while ($grid->firstChild) $grid->removeChild($grid->firstChild);
            foreach ($cards_doc->getElementById('dz-service-team')->childNodes as $card) $grid->appendChild($doc->importNode($card, true));
        }
        $quotes = dz_polished_quotes();
        $slug = substr($route, strlen('services/'));
        $index = 0;
        foreach (iterator_to_array($xp->query('//div[contains(concat(" ",normalize-space(@class)," ")," max-w-[750px] ")]')) as $text_node) {
            $box = $text_node->parentNode->parentNode->parentNode;
            if (!$box instanceof DOMElement || !str_contains($box->getAttribute('class'), 'rounded-[30px]')) continue;
            $text = $quotes[$slug][$index] ?? trim($text_node->textContent, " \t\n\r\0\x0B«».");
            $index++;
            $quote = $doc->createElement('blockquote'); $quote->setAttribute('class', 'dz-clinic-quote');
            $p = $doc->createElement('p'); $p->appendChild($doc->createTextNode($text)); $quote->appendChild($p);
            $cite = $doc->createElement('cite'); $cite->appendChild($doc->createTextNode('Дом Зубов')); $quote->appendChild($cite);
            $box->parentNode->replaceChild($quote, $box);
        }
    }

    foreach ($xp->query('//*[@data-review-status]') as $status) { $status->setAttribute('hidden', 'hidden'); $status->setAttribute('aria-hidden', 'true'); }
    foreach ($xp->query('//button[@data-team-prev or @data-team-next or @data-gallery-prev or @data-gallery-next or @data-review-prev or @data-review-next]') as $button) {
        $svg_doc = new DOMDocument();
        $svg_doc->loadXML('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>', LIBXML_NONET);
        if ($button->hasAttribute('data-team-prev') || $button->hasAttribute('data-gallery-prev') || $button->hasAttribute('data-review-prev')) $svg_doc->documentElement->setAttribute('style', 'transform:rotate(180deg)');
        while ($button->firstChild) $button->removeChild($button->firstChild);
        $button->appendChild($doc->importNode($svg_doc->documentElement, true));
    }
    foreach ($xp->query('//*[@data-team-prev]') as $prev) {
        $add_class($prev->parentNode, 'dz-team-controls');
        $next = $xp->query('following-sibling::*[1]', $prev->parentNode)->item(0);
        if ($next) $add_class($next, 'dz-team-directory-link');
    }
    foreach ($xp->query('//*[contains(concat(" ",normalize-space(@class)," ")," a11y-hero-section ")]//a[@href="/prices"]') as $link) {
        $button = $xp->query('./button', $link)->item(0);
        if ($button) { $link->setAttribute('class', trim($button->getAttribute('class').' dz-hero-secondary')); $set_text($link, trim($button->textContent)); }
        else $add_class($link, 'dz-hero-secondary');
    }
    $output = '';
    foreach ($root->childNodes as $child) $output .= $doc->saveHTML($child);
    return $output;
}

/** Match known directions; broad or ambiguous services display the factual clinic team. */
function dz_service_doctors(string $route): array {
    $specialties = [1];
    if (preg_match('~ispravlenie-prikusa|bite-correction|palatal-expander~', $route)) $specialties = [4];
    elseif (preg_match('~prosthetics|protezirovanie|koronki|viniry|semnye-i-nesemnye~', $route)) $specialties = [3];
    elseif (preg_match('~ustanovka-implanta|surgical-dentistry|sinus-lifting|uvelichenie-obema|esteticheskaya-parodontologiya~', $route)) $specialties = [2];
    elseif (str_contains($route, 'zuby-za-odin-den')) $specialties = [2,3];
    elseif (preg_match('~sedation|anesthesia|indream|tomografiya~', $route)) $specialties = [];
    $doctors = doctor_data()['doctors'];
    $selected = $specialties ? array_values(array_filter($doctors, static fn(array $doctor): bool => (bool)array_intersect($specialties, $doctor['specializations']))) : $doctors;
    if (str_contains($route, 'zuby-za-odin-den')) usort($selected, static fn(array $a, array $b): int => (int)in_array(2, $b['specializations'], true) <=> (int)in_array(2, $a['specializations'], true));
    return $selected;
}

/** Editorial replacements retain each source block's topic without guarantees or invented attribution. */
function dz_polished_quotes(): array {
    return [
        'diagnostika-skrytogo-kariesa' => ['Кариес может долго оставаться незаметным. Диагностика помогает выявить изменения до появления выраженных жалоб.', 'Врач использует прицельные снимки и осмотр под увеличением, чтобы оценить скрытые участки и выбрать дальнейшую тактику.'],
        'esteticheskaya-parodontologiya-rozovaya-estetika' => ['Контур и объём десны влияют на вид улыбки. План коррекции учитывает состояние мягких тканей и чувствительность зубов.', 'Оголение корней требует оценки врача. При выборе лечения учитывают чувствительность, риск кариеса и состояние окружающих тканей.'],
        'ispravlenie-prikusa' => ['Возможности исправления прикуса оценивают индивидуально. Возраст, состояние зубов и тканей влияют на выбор метода и сроки лечения.', 'В «Доме Зубов» способ исправления прикуса подбирают с учётом клинической ситуации, образа жизни и пожеланий пациента. Врач объясняет этапы и ограничения каждого варианта.'],
        'ispravlenie-prikusa-with-aligners' => ['Элайнеры последовательно меняют положение зубов. Врач объясняет режим ношения, правила смены капп и возможные ощущения во время лечения.', 'Возможность лечения элайнерами зависит от характера нарушения прикуса. В отдельных случаях врач может предложить комбинированное лечение.'],
        'keramicheskie-viniry' => ['Виниры помогают скорректировать форму и цвет зубов. При планировании врач оценивает состояние эмали и необходимый объём подготовки.', 'Керамические реставрации подбирают с учётом состояния зубов, нагрузки и эстетических пожеланий пациента.'],
        'kompyuternaya-tomografiya-zubov-i-polosti-rta' => ['Компьютерная томография помогает врачу оценить клиническую ситуацию и обосновать план лечения на основании диагностических данных.', 'Объёмное изображение позволяет подробнее изучить участки, которые трудно оценить по обычному снимку. Необходимость КТ определяет врач.'],
        'lechenie-detskogo-cariesa-i-ego-osloznenie' => ['Здоровье молочных зубов важно для развития постоянных. Своевременный осмотр помогает определить, когда требуется лечение.', 'Врач объясняет ребёнку предстоящие действия понятными словами и учитывает его готовность к лечению. Доверие формируется постепенно.'],
        'lechenie-kariesa' => ['Кариес часто развивается незаметно. Снимки, осмотр под увеличением и изоляция рабочего поля помогают врачу провести лечение.', 'Задача лечения — восстановить форму и функцию зуба. После установки реставрации важны домашний уход и контрольные осмотры.'],
        'lechenie-kornevyh-kanalov-pod-mikroskopom' => ['Воспаление пульпы не всегда означает потерю зуба. После диагностики врач оценивает возможность лечения каналов и сохранения зуба.', 'Лечение каналов включает изоляцию зуба, очистку и герметичное закрытие каналов. Каждый этап требует точности и контроля.'],
        'obuchenie-gigiene-zubov' => ['Даже небольшие ошибки в уходе могут влиять на здоровье зубов и дёсен. На приёме врач показывает, как скорректировать привычки.', 'Обучение помогает освоить ежедневный уход на практике. Рекомендации подбирают под состояние полости рта и привычки пациента.'],
        'otbelivanie-zubov' => ['Систему отбеливания выбирают после оценки состояния зубов. Возможная чувствительность и длительность эффекта зависят от индивидуальных особенностей и ухода.', 'Перед отбеливанием врач оценивает необходимость профессиональной гигиены. После удаления налёта можно обсудить дальнейшее изменение оттенка.'],
        'palatal-expander' => ['Нёбный расширитель применяют по показаниям. Врач объясняет семье, как пользоваться аппаратом и какие ощущения требуют внимания.', 'Ортодонтическое лечение строится по согласованному плану. Семья получает инструкции по использованию аппарата и контрольным визитам.'],
        'personal-hygiene' => ['Средства гигиены подбирают индивидуально: с учётом состояния дёсен, чувствительности и стоматологических конструкций.'],
        'professionalnaya-gigiena' => ['Регулярность профессиональной гигиены врач определяет индивидуально. Она дополняет домашний уход и помогает контролировать состояние зубов и дёсен.', 'Профессиональная гигиена включает последовательное удаление налёта и зубного камня, обработку поверхности зубов и рекомендации по уходу.'],
        'professionalnaya-gigiena-dlya-breketov' => ['Во время ортодонтического лечения особенно важно поддерживать чистоту полости рта. Профессиональная гигиена дополняет ежедневный уход.', 'Вокруг брекетов появляются участки, где легче задерживается налёт. Врач показывает, как очищать их и следить за состоянием эмали и дёсен.'],
        'profilaktika-i-diagnostika-detei' => ['Профилактический приём помогает ребёнку познакомиться с врачом и научиться заботиться о зубах в понятной и спокойной обстановке.', 'В профилактике участвуют ребёнок, родители и врач. На приёме семья получает рекомендации по уходу и наблюдению за состоянием зубов.'],
        'prosthetics' => ['После установки протеза врач объясняет правила питания и ухода. Ограничения зависят от конструкции и этапа лечения.'],
        'protezirovanie-zubov-u-lyudej-v-vozraste' => ['При выборе протеза учитывают состояние зубов, привычки и возможности пациента. Цель лечения — восстановить жевательную функцию и повседневный комфорт.', 'Тактику восстановления выбирают после диагностики. При недостатке костной ткани врач обсуждает доступные варианты опоры и объём вмешательства.'],
        'sedation-propofol' => ['Во время седации команда контролирует состояние пациента. Подготовку, проведение процедуры и восстановление обсуждают на консультации.'],
        'semnye-i-nesemnye-konstruktsii-na-implantah' => ['Конструкцию на имплантах выбирают индивидуально, с учётом костной ткани, функции и пожеланий пациента.', 'Несъёмные конструкции помогают восстановить зубной ряд. Врач объясняет правила ухода, питания и контрольных осмотров.', 'Материал конструкции выбирают с учётом нагрузки, состояния тканей и желаемого внешнего вида.'],
        'sinus-lifting' => ['Цель синус-лифтинга — создать условия для установки импланта при недостатке костной ткани. Необходимость и объём процедуры определяют после диагностики.', 'Перед синус-лифтингом врач объясняет ход процедуры, обезболивание и восстановление. Срок установки импланта зависит от клинической ситуации.'],
        'sohranenie-zhiznesposobnosti-pulpy' => ['После диагностики врач оценивает возможность сохранить жизнеспособность пульпы. Выбор метода зависит от состояния зуба.', 'Сохранение пульпы требует точной работы и контроля. После лечения врач назначает наблюдение для оценки состояния зуба.'],
        'surgical-dentistry/impacted-tooth-extraction' => ['Ретинированный зуб оценивают по осмотру и снимкам. При выборе тактики учитывают воспаление, положение зуба и влияние на соседние ткани.'],
        'surgical-dentistry/permanent-tooth-extraction' => ['Перед удалением врач оценивает возможность сохранить зуб. Решение, альтернативы и дальнейший план обсуждают с пациентом.'],
        'surgical-dentistry/wisdom-tooth-extraction' => ['Зубы мудрости не всегда требуют удаления. Показания определяют по их положению, состоянию окружающих тканей и жалобам пациента.'],
        'therapeutic-dentistry' => ['В некоторых случаях повторное лечение каналов помогает сохранить зуб. Возможность такого лечения оценивают после диагностики.', 'В лечении важны и клиническая задача, и самочувствие пациента. Команда объясняет этапы и поддерживает диалог во время приёма.'],
        'tselnokeramicheskie-koronki' => ['Цельнокерамические коронки помогают восстановить форму и внешний вид зуба. Подходящий материал выбирают с учётом клинической ситуации.', 'Срок службы керамической коронки зависит от состояния зуба, нагрузки, качества фиксации и последующего ухода.'],
        'tsirkonievye-koronki-na-zubah-i-implantah' => ['Циркониевые коронки используют для восстановления зубов и конструкций на имплантах. При выборе учитывают прочность, внешний вид и состояние тканей.', 'Материал коронки влияет на её внешний вид у десны. Врач обсуждает особенности доступных вариантов до начала лечения.'],
        'ustanovka-implanta' => ['Диагностика и цифровое планирование помогают определить положение импланта и будущей коронки до вмешательства.', 'Перед имплантацией врач объясняет план, обезболивание и этапы восстановления. После установки важны уход и контрольные осмотры.'],
        'uvelichenie-obema-kostnoj-i-myagkoj-tkani' => ['После длительного отсутствия зуба объём кости и десны может измениться. Пластика тканей помогает подготовить условия для дальнейшего восстановления.', 'После пластики требуется период восстановления. Врач заранее обсуждает возможные ощущения, уход и сроки следующего этапа лечения.'],
        'zuby-za-odin-den' => ['При подходящих условиях временную конструкцию можно установить в день имплантации. Возможность такого протокола определяют после диагностики.', 'Цель восстановления зубного ряда — вернуть жевательную функцию и уверенность в улыбке. Этапы, сроки и ограничения обсуждают индивидуально.'],
    ];
}
