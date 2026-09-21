<?php
declare(strict_types=1);
require_once __DIR__.'/site-config.php';

/** Explicit current pages, independent of the imported archive route registry. */
function dz_seo_pages(): array {
    $pages = [
        'index' => ['Дом Зубов — стоматология в Москве', 'Стоматология «Дом Зубов» в Москве: направления лечения, информация для пациентов, адрес на Симферопольском бульваре, контакты и онлайн-запись.'],
        'services' => ['Услуги стоматологии — Дом Зубов', 'Направления стоматологии «Дом Зубов»: диагностика, лечение зубов, имплантация, протезирование, ортодонтия для взрослых. Информация о процедурах.'],
        'prices' => ['Стоимость лечения — Дом Зубов', 'Стоимость стоматологического лечения по направлениям. Цены на сайте предварительные; объём лечения и итоговую сумму необходимо уточнять в «Доме Зубов».'],
        'contacts' => ['Контакты и адрес в Москве — Дом Зубов', '«Дом Зубов»: Москва, Симферопольский бульвар, 24, корп. 4. Часы работы 09:00–21:00. Телефон, карта проезда и онлайн-запись.'],
        'doctors' => ['Врачи стоматологии — Дом Зубов', 'Команда стоматологии «Дом Зубов»: ортопед, терапевт, хирург-имплантолог и ортодонт. Образование, направления работы, сертификаты и запись на приём.'],
        'reviews' => ['Отзывы пациентов — Дом Зубов', 'Отзывы пациентов о Магомеде Раджабовиче Расулове, предоставленные владельцем сайта: впечатления от консультаций, лечения и общения с врачом.'],
        'o-nas' => ['О клинике и подходе к лечению — Дом Зубов', 'Как устроен приём в «Доме Зубов»: обсуждение жалоб, диагностика, план лечения и ответы на вопросы пациента. Информация о подходе клиники.'],
        'pravila-zapisi' => ['Правила записи на приём — Дом Зубов', 'Как записаться, перенести или отменить приём в «Доме Зубов», какие документы и снимки взять с собой и что сообщить администратору.'],
        'tax-deduction' => ['Документы для налогового вычета — Дом Зубов', 'Как запросить в «Доме Зубов» документы об оплате стоматологического лечения для налогового вычета. Состав комплекта и сроки уточняйте при обращении.'],
        'insurance-companies' => ['Условия оплаты: ДМС и ОМС — Дом Зубов', 'Приём по ДМС и ОМС в «Доме Зубов» не ведётся. Лечение предоставляется на платной основе. Уточните стоимость услуг и порядок записи.'],
        'gift-certificates' => ['Подарочные сертификаты — Дом Зубов', 'Информация о подарочных сертификатах «Дом Зубов». Порядок оформления, применения и действующие условия уточняйте у администратора перед покупкой.'],
        'programma-blagodarnosti' => ['Программа благодарности — Дом Зубов', 'Информация о программе благодарности «Дом Зубов». Действующие правила участия и возможные условия для пациентов уточняйте у администратора.'],
        'program-conditions' => ['Условия программы благодарности — Дом Зубов', 'Условия участия в программе благодарности «Дом Зубов»: что необходимо уточнить перед участием и как связаться с клиникой для получения правил.'],
        'yuridicheskaya-informatsiya' => ['Документы и реквизиты — Дом Зубов', 'Реквизиты ООО «Дом Зубов», статус подготовки лицензии и документов для пациентов, ссылки на нормативные материалы и контролирующие органы.'],
        'privacy-policy' => ['Конфиденциальность и персональные данные — Дом Зубов', 'Информация о конфиденциальности на сайте «Дом Зубов», работе форм и обработке персональных данных. Контакты для вопросов и обращений.'],
        'sitemap' => ['Карта сайта — Дом Зубов', 'Карта сайта «Дом Зубов»: услуги стоматологии, стоимость лечения, сведения о клинике, информация пациентам, документы и контакты.'],
    ];
    foreach(doctor_data()['doctors'] as $doctor)$pages['doctors/'.$doctor['slug']]=[$doctor['name'].' — Дом Зубов',$doctor['profession'].'. '.$doctor['description'].' Образование и запись на приём.'];
    return $pages;
}

function dz_seo_service_descriptions(): array {
    return [
        'diagnostika-skrytogo-kariesa' => 'Диагностика скрытого кариеса: когда нужны дополнительные исследования, как оценивают состояние тканей зуба и что обсуждают после обследования.',
        'esteticheskaya-parodontologiya-rozovaya-estetika' => 'Эстетическая пародонтология: оценка здоровья дёсен, показания к коррекции десневого края, подготовка и этапы лечения.',
        'icon-remineralizing-therapy' => 'Инфильтрация начального кариеса ICON: показания и ограничения метода, подготовка зуба и этапы процедуры при начальных поражениях эмали.',
        'indream' => 'Стоматологическое лечение во сне: медицинские показания, оценка стоматолога и анестезиолога, подготовка к общей анестезии и наблюдение.',
        'inhalation-anesthesia-sevoflurane' => 'Ингаляционная анестезия севофлураном в стоматологии: обследование перед лечением, контроль состояния пациента и подготовка к процедуре.',
        'ispravlenie-prikusa' => 'Исправление прикуса: ортодонтическая диагностика, выбор метода лечения, этапы перемещения зубов и сохранение результата после лечения.',
        'ispravlenie-prikusa-breket' => 'Исправление прикуса брекет-системой: выбор конструкции, установка, контроль у ортодонта и гигиена во время лечения.',
        'ispravlenie-prikusa-with-aligners' => 'Исправление прикуса элайнерами: цифровой план, режим ношения прозрачных капп, контроль лечения и ограничения метода.',
        'keramicheskie-viniry' => 'Керамические виниры: оценка формы и цвета зубов, показания, подготовка, изготовление накладок и уход после установки.',
        'kompyuternaya-tomografiya-zubov-i-polosti-rta' => 'Компьютерная томография зубов и полости рта: когда требуется трёхмерный снимок, подготовка к исследованию и обсуждение результатов с врачом.',
        'lechenie-kariesa' => 'Лечение кариеса: диагностика стадии поражения, очищение и восстановление зуба, подготовка к приёму и профилактика повторного кариеса.',
        'lechenie-kornevyh-kanalov-pod-mikroskopom' => 'Лечение корневых каналов под микроскопом: показания, поиск и очистка каналов, контроль обработки и дальнейшее восстановление зуба.',
        'nitrous-oxide-sedation' => 'Седация закисью азота: как проходит стоматологический приём, кому подходит метод снижения тревоги и что обсудить с врачом заранее.',
        'obuchenie-gigiene-zubov' => 'Обучение домашней гигиене полости рта: оценка налёта, подбор щётки и межзубных средств, отработка техники очищения зубов.',
        'otbelivanie-zubov' => 'Профессиональное отбеливание зубов: обследование перед процедурой, показания, ограничения и уход за зубами после осветления эмали.',
        'personal-hygiene' => 'Подбор средств индивидуальной гигиены: оценка домашнего ухода, выбор зубной щётки, пасты и средств для очищения межзубных промежутков.',
        'professionalnaya-gigiena' => 'Профессиональная гигиена полости рта: удаление зубного налёта и камня, этапы процедуры и рекомендации по домашнему уходу.',
        'professionalnaya-gigiena-dlya-breketov' => 'Профессиональная гигиена при брекетах: очищение участков вокруг замков и дуг, подбор домашних средств и уход во время ортодонтического лечения.',
        'professionalnaya-gigiena-dlya-implantov' => 'Профессиональная гигиена для имплантов: очищение коронок и окружающих тканей, контроль состояния дёсен и подбор домашнего ухода.',
        'prosthetics' => 'Постоянное протезирование «Всё на 4, 6 или 8»: диагностика, выбор числа имплантов, этапы восстановления полного зубного ряда и уход.',
        'protezirovanie-zubov-u-lyudej-v-vozraste' => 'Протезирование зубов у людей старшего возраста: подбор конструкции с учётом здоровья, состояния тканей и удобства ежедневного ухода.',
        'sedation-propofol' => 'Седация пропофолом в стоматологии: оценка показаний, подготовка к лечению, наблюдение анестезиолога и восстановление после процедуры.',
        'semnye-i-nesemnye-konstruktsii-na-implantah' => 'Съёмные и несъёмные конструкции на имплантах: различия в фиксации и уходе, диагностика и выбор протеза по клинической ситуации.',
        'sinus-lifting' => 'Синус-лифтинг: показания к увеличению высоты кости перед имплантацией, подготовка, этапы вмешательства и восстановление.',
        'sohranenie-zhiznesposobnosti-pulpy' => 'Сохранение жизнеспособности пульпы: оценка глубокого поражения зуба, показания к лечению живых тканей и дальнейшее наблюдение.',
        'impacted-tooth-extraction' => 'Удаление ретинированного зуба: обследование положения зуба в кости или десне, показания к операции, подготовка и восстановление.',
        'permanent-tooth-extraction' => 'Удаление постоянного зуба: оценка возможности сохранения, подготовка к процедуре, уход за лункой и обсуждение восстановления зубного ряда.',
        'wisdom-tooth-extraction' => 'Удаление зуба мудрости: диагностика положения третьего моляра, показания к вмешательству, этапы процедуры и рекомендации после удаления.',
        'therapeutic-dentistry' => 'Перелечивание корневых каналов: причины повторного лечения, диагностика, очистка и герметизация каналов, восстановление зуба.',
        'tselnokeramicheskie-koronki' => 'Цельнокерамические коронки и вкладки: восстановление формы зуба без металлического каркаса, выбор оттенка и этапы изготовления.',
        'tsirkonievye-koronki-na-zubah-i-implantah' => 'Циркониевые коронки на зубах и имплантах: показания к протезированию, планирование формы, изготовление и уход за конструкциями.',
        'ustanovka-implanta' => 'Установка зубного импланта: обследование, планирование опоры для коронки или протеза, этапы имплантации и последующее наблюдение.',
        'uvelichenie-obema-kostnoj-i-myagkoj-tkani' => 'Увеличение объёма костной и мягкой ткани: диагностика, показания к восстановлению кости или десны, подготовка и этапы заживления.',
        'zuby-za-odin-den' => 'Зубы за один день: условия установки временной конструкции на имплантах, диагностика, ограничения метода и переход к постоянному протезу.',
    ];
}

function dz_seo_routes(): array {
    return array_merge(array_keys(dz_seo_pages()), array_keys(editorial_services()));
}

function dz_seo_prepare(?string $route, array $page = [], ?array $service = null, ?array $editorial = null): array {
    $config = dz_site_config();
    $base = $config['base_url'];
    $pages = dz_seo_pages();
    $services = editorial_services();
    $doctor=doctor_profile($route);
    $active = $route !== null && (isset($pages[$route]) || isset($services[$route]));
    if (isset($pages[$route ?? ''])) {
        [$title, $description] = $pages[$route];
    } elseif (isset($services[$route ?? ''])) {
        $service = $services[$route];
        $title = $service['title'].' — Дом Зубов';
        $description = dz_seo_service_descriptions()[basename($route)] ?? $service['lead'];
    } else {
        $title = $route === 'search' ? 'Поиск по сайту — Дом Зубов' : 'Страница не найдена — Дом Зубов';
        $description = $route === 'search' ? 'Поиск услуг и информации на сайте стоматологии «Дом Зубов».' : 'Страница не найдена. Перейдите к услугам, контактам или карте сайта «Дом Зубов».';
    }
    $canonical = $active ? $base.($route === 'index' ? '/' : '/'.$route) : null;
    $seo = [
        'title' => $title, 'description' => $description, 'canonical' => $canonical,
        'robots' => $active && $config['allow_indexing'] ? 'index, follow' : 'noindex, nofollow',
        'image' => $base.'/assets/images/reception.webp',
        'image_alt' => 'Иллюстрация для сайта «Дом Зубов», не фотография клиники',
        'schema' => null,
    ];
    if (!$active) return $seo;
    if($doctor&&$doctor['photoAvailable']){$seo['image']=$base.$doctor['photoLocal'];$seo['image_alt']=$doctor['name'];}
    $organizationId = $base.'/#organization';
    $websiteId = $base.'/#website';
    $places = [];
    foreach (company_locations() as $location) {
        $places[] = [
            '@type' => 'Place', '@id' => $base.'/'.$location['slug'].'#place',
            'name' => 'Место приёма «Дом Зубов» '.$location['area'],
            'url' => $base.'/'.$location['slug'],
            'description' => 'Часы работы: '.COMPANY_HOURS.'. '.COMPANY_INSURANCE_NOTICE,
            'address' => ['@type'=>'PostalAddress', 'streetAddress'=>preg_replace('/^Москва,\s*/u', '', $location['address']), 'addressLocality'=>'Москва', 'addressCountry'=>'RU'],
        ];
    }
    $organization = [
        '@type' => 'Organization', '@id' => $organizationId,
        'name' => 'Дом Зубов', 'legalName' => 'ООО «ДОМ ЗУБОВ»', 'url' => $base.'/',
        'telephone' => COMPANY_TEL, 'email' => COMPANY_EMAIL,
        'logo' => $base.'/assets/mark.svg',
        'location' => array_map(fn(array $place): array => ['@id'=>$place['@id']], $places),
    ];
    $label = isset($services[$route]) ? $services[$route]['title'] : preg_replace('/ — Дом Зубов$/u', '', $title);
    $breadcrumbs = [['@type'=>'ListItem', 'position'=>1, 'name'=>'Главная', 'item'=>$base.'/']];
    if (isset($services[$route])) $breadcrumbs[] = ['@type'=>'ListItem', 'position'=>2, 'name'=>'Услуги', 'item'=>$base.'/services'];
    if($doctor)$breadcrumbs[]=['@type'=>'ListItem','position'=>2,'name'=>'Врачи','item'=>$base.'/doctors'];
    if ($route !== 'index') $breadcrumbs[] = ['@type'=>'ListItem', 'position'=>count($breadcrumbs)+1, 'name'=>$label, 'item'=>$canonical];
    $webPage = [
        '@type'=>'WebPage', '@id'=>$canonical.'#webpage', 'url'=>$canonical,
        'name'=>$title, 'description'=>$description, 'inLanguage'=>'ru-RU',
        'isPartOf'=>['@id'=>$websiteId], 'about'=>['@id'=>$organizationId],
        'breadcrumb'=>['@id'=>$canonical.'#breadcrumb'],
    ];
    if (isset($services[$route])) $webPage['mainEntity'] = ['@id'=>$canonical.'#service'];
    if($doctor)$webPage['mainEntity']=['@id'=>$canonical.'#person'];
    $graph = [
        $organization,
        ['@type'=>'WebSite', '@id'=>$websiteId, 'url'=>$base.'/', 'name'=>'Дом Зубов', 'inLanguage'=>'ru-RU', 'publisher'=>['@id'=>$organizationId]],
        $webPage,
        ['@type'=>'BreadcrumbList', '@id'=>$canonical.'#breadcrumb', 'itemListElement'=>$breadcrumbs],
        ...$places,
    ];
    if($route==='index'){
        unset($graph[2]['breadcrumb']);
        $graph=array_values(array_filter($graph,static fn(array $node):bool=>$node['@type']!=='BreadcrumbList'));
    }
    if (isset($services[$route])) $graph[] = [
        '@type'=>'Service', '@id'=>$canonical.'#service', 'name'=>$services[$route]['title'],
        'description'=>$description, 'url'=>$canonical, 'provider'=>['@id'=>$organizationId],
    ];
    if($doctor){$person=['@type'=>'Person','@id'=>$canonical.'#person','name'=>$doctor['name'],'jobTitle'=>$doctor['profession'],'description'=>$doctor['description'],'url'=>$canonical,'worksFor'=>['@id'=>$organizationId]];if($doctor['photoAvailable'])$person['image']=$base.$doctor['photoLocal'];$graph[]=$person;}
    $seo['schema'] = ['@context'=>'https://schema.org', '@graph'=>$graph];
    return $seo;
}

function dz_seo_json(array $value): string {
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
}

function dz_seo_sitemap_xml(): string {
    $base = dz_site_config()['base_url'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    foreach (dz_seo_routes() as $route) {
        $url = $base.($route === 'index' ? '/' : '/'.$route);
        $xml .= '  <url><loc>'.htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8')."</loc></url>\n";
    }
    return $xml."</urlset>\n";
}

function dz_seo_robots(): string {
    $config = dz_site_config();
    if (!$config['allow_indexing']) return "User-agent: *\nDisallow: /\n";
    // Search/error pages must be crawlable for their noindex response to be seen.
    return "User-agent: *\nAllow: /\n\nSitemap: ".$config['base_url']."/sitemap.xml\n";
}
