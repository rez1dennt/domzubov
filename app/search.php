<?php
declare(strict_types=1);

function dz_search_query(mixed $value):string {
    if(!is_string($value))return '';
    $characters=preg_split('//u',trim($value),201,PREG_SPLIT_NO_EMPTY);
    return $characters===false?'':implode('',array_slice($characters,0,200));
}
function dz_search_body_text(array $data):string {
    $parts=[];array_walk_recursive($data,static function($value)use(&$parts){if(is_string($value)&&!str_contains($value,'services/'))$parts[]=$value;});
    return implode(' ',$parts);
}

/** @return string lowercase Russian text with punctuation folded to spaces */
function dz_search_normalize(string $value): string
{
    static $upper = null;
    if ($upper === null) {
        $upper = array_combine(
            preg_split('//u', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯABCDEFGHIJKLMNOPQRSTUVWXYZ', -1, PREG_SPLIT_NO_EMPTY),
            preg_split('//u', 'абвгдеежзийклмнопрстуфхцчшщъыьэюяabcdefghijklmnopqrstuvwxyz', -1, PREG_SPLIT_NO_EMPTY)
        );
    }
    $value = strtr($value, $upper ?: []);
    $value = (string) preg_replace('/[^a-zа-я0-9]+/u', ' ', $value);
    return trim((string) preg_replace('/\s+/u', ' ', $value));
}

function dz_search_stem(string $token): string
{
    if (preg_match('/^\d+$/', $token) || strlen($token) < 8) {
        return $token;
    }
    return (string) preg_replace(
        '/(?:иями|ями|ами|ого|ему|ому|ыми|ими|ение|ения|ений|ский|ская|ское|ские|ого|ая|яя|ое|ее|ые|ие|ов|ев|ам|ям|ах|ях|ом|ем|ой|ей|ы|и|а|я|у|ю|е|о)$/u',
        '',
        $token
    );
}

/** @return list<string> */
function dz_search_tokens(string $value): array
{
    $normalized = dz_search_normalize($value);
    if ($normalized === '') {
        return [];
    }
    $tokens = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $result = [];
    foreach ($tokens as $token) {
        if (strlen($token) < 3 && !preg_match('/^\d+$/', $token)) {
            continue;
        }
        $result[] = dz_search_stem($token);
    }
    return array_values(array_unique($result));
}

/** @return list<string> */
function dz_search_chars(string $value): array
{
    return preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
}

function dz_search_one_edit(string $left, string $right): bool
{
    $a = dz_search_chars($left);
    $b = dz_search_chars($right);
    $aCount = count($a);
    $bCount = count($b);
    if (abs($aCount - $bCount) > 1 || max($aCount, $bCount) < 5) {
        return false;
    }
    $i = $j = $edits = 0;
    while ($i < $aCount && $j < $bCount) {
        if ($a[$i] === $b[$j]) {
            $i++;
            $j++;
            continue;
        }
        if (++$edits > 1) {
            return false;
        }
        if ($aCount > $bCount) {
            $i++;
        } elseif ($bCount > $aCount) {
            $j++;
        } else {
            $i++;
            $j++;
        }
    }
    return $edits + (($i < $aCount || $j < $bCount) ? 1 : 0) <= 1;
}

/** @return list<string> */
function dz_search_intent_terms(string $query): array
{
    $query = dz_search_normalize($query);
    $rules = [
        '/болит зуб|зубная боль|ноет зуб/u' => ['кариес', 'пульпит', 'лечение каналов'],
        '/ребен|детск|молочн/u' => ['дети', 'детский', 'молочные зубы'],
        '/кривые зубы|выровнять зубы|прикус/u' => ['ортодонтия', 'брекеты', 'элайнеры', 'исправление прикуса'],
        '/брекет/u' => ['брекеты', 'исправление прикуса'],
        '/чистк|налет|налёт|камень/u' => ['профессиональная гигиена', 'гигиена зубов'],
        '/нет зуба|нет зубов|потерял зуб|имплант/u' => ['имплантация', 'имплант', 'протезирование'],
        '/удалить|удаление/u' => ['удаление зуба', 'хирургия'],
        '/адрес|как добраться|чертановск|таганск|гончарн/u' => ['контакты', 'адрес клиники'],
        '/цен|стоимост|сколько стоит|прайс/u' => ['цены', 'стоимость услуг'],
        '/боюсь|страх|во сне|наркоз|седац/u' => ['лечение во сне', 'седация', 'анестезия'],
        '/страхов|дмс|омс|полис/u' => ['ДМС', 'ОМС', 'условия оплаты'],
        '/подготов.*прием|первый прием/u' => ['правила записи', 'перед визитом'],
    ];
    $terms = [];
    foreach ($rules as $pattern => $additions) {
        if (preg_match($pattern, $query)) {
            array_push($terms, ...$additions);
        }
    }
    return $terms;
}

/**
 * @return list<array{url:string,title:string,description:string,type:string,search:string,titleSearch:string,tokens:list<string>}>
 */
function dz_search_index(): array
{
    static $index = null;
    if ($index !== null) {
        return $index;
    }

    $items = [];
    $aliases = [];
    $menuFile = PROJECT_ROOT . '/content/service-menu.json';
    if (is_file($menuFile)) {
        $menu = json_decode(file_get_contents($menuFile), true, 512, JSON_THROW_ON_ERROR);
        foreach ($menu['categories'] ?? [] as $category) {
            foreach ($category['items'] ?? [] as $item) {
                $url = $item['link'] ?? '';
                if ($url !== '') {
                    $aliases[$url][] = ($item['name'] ?? '') . ' ' . ($category['name'] ?? '');
                }
            }
        }
    }

    foreach (editorial_services() as $route => $service) {
        $url = '/' . ltrim($route, '/');
        $items[] = [
            'url' => $url,
            'title' => (string) ($service['title'] ?? routes()[$route]['title'] ?? 'Стоматологическая услуга'),
            'description' => (string) ($service['lead'] ?? routes()[$route]['description'] ?? ''),
            'type' => 'Услуга',
            'keywords' => implode(' ', $aliases[$url] ?? []) . ' ' . dz_search_body_text(array_intersect_key($service,array_flip(['intro','indications','steps','preparation','faq']))),
        ];
    }

    $information = [
        ['/contacts', 'Контакты и адрес клиники', 'Москва, Симферопольский бульвар, 24, корп. 4. Часы работы 09:00–21:00. Телефон ' . COMPANY_PHONE . '.', 'Контакты', 'адрес как добраться симферопольский бульвар график часы работы'],
        ['/prices', 'Цены на стоматологические услуги', 'Актуальный каталог стоимости консультаций, диагностики и лечения.', 'Информация', 'цена цены стоимость прайс сколько стоит'],
        ['/pravila-zapisi', 'Запись на приём', 'Правила записи, переноса и отмены визита. Телефон ' . COMPANY_PHONE . '.', 'Информация', 'записаться запись прием приём телефон'],
        ['/doctors', 'Врачи «Дома Зубов»', 'Направления работы специалистов клиники.', 'Информация', 'врач стоматолог команда специалисты'],
        ['/reviews', 'Отзывы пациентов', 'Отзывы пациентов о лечении у Магомеда Раджабовича Расулова.', 'Информация', 'отзывы мнение пациенты'],
        ['/yuridicheskaya-informatsiya', 'Реквизиты и юридическая информация', 'ООО «ДОМ ЗУБОВ», ИНН 9727136018. Контакты организации и банковские реквизиты.', 'Информация', 'руководитель генеральный директор инн кпп огрн документы банк юридический'],
    ];
    foreach (company_locations() as $location) {
        $information[] = ['/contacts', $location['name'] . ' — ' . $location['area'], $location['address'], 'Адрес', $location['slug'] . ' ' . $location['query']];
    }
    foreach ($information as [$url, $title, $description, $type, $keywords]) {
        $items[] = compact('url', 'title', 'description', 'type', 'keywords');
    }
    foreach(doctor_data()['doctors'] as $doctor){
        $items[]=['url'=>$doctor['url'],'title'=>$doctor['name'],'description'=>$doctor['profession'].'. '.$doctor['description'],'type'=>'Врач','keywords'=>$doctor['profession'].' '.$doctor['schedule'].' '.dz_search_body_text($doctor['education']).' '.dz_search_body_text($doctor['competencies']).' '.($doctor['slug']==='rasulov-magomed-radzhabovich'?'расул ':'')];
    }
    foreach(editorial_pages() as $route=>$page){
        $url='/'.ltrim($route,'/');
        $items[]=['url'=>$url,'title'=>$page['title'],'description'=>$page['lead'],'type'=>'Информация','keywords'=>dz_search_body_text($page['sections']??[])];
    }
    $items[]=['url'=>'/privacy-policy','title'=>'Конфиденциальность и персональные данные','description'=>'Как используются данные при обращении в клинику и онлайн-записи.','type'=>'Документы','keywords'=>'политика конфиденциальность персональные данные согласие обработка'];
    $items[]=['url'=>'/yuridicheskaya-informatsiya#license','title'=>'Лицензия на медицинскую деятельность','description'=>'Сведения о лицензировании ООО «Дом Зубов» и официальный реестр Росздравнадзора.','type'=>'Документы','keywords'=>'лицензия лицензии выписка реестр разрешение'];
    $items[]=['url'=>'/yuridicheskaya-informatsiya#regulations','title'=>'Нормативные документы для пациентов','description'=>'Правила платных медицинских услуг и другие официальные документы для скачивания.','type'=>'Документы','keywords'=>'закон постановление правила платные медицинские услуги документы скачать 659 118н'];
    $items[]=['url'=>'/sitemap','title'=>'Карта сайта','description'=>'Все услуги и основные разделы сайта «Дом Зубов».','type'=>'Информация','keywords'=>'карта сайта все страницы навигация'];
    $items[]=['url'=>'/services','title'=>'Все стоматологические услуги','description'=>'Все направления лечения, профилактики и восстановления зубов.','type'=>'Каталог','keywords'=>'все услуги каталог направления список лечение'];

    $priceFile = PROJECT_ROOT . '/content/price-catalog.json';
    if (is_file($priceFile)) {
        $catalog = json_decode(file_get_contents($priceFile), true, 512, JSON_THROW_ON_ERROR);
        foreach ($catalog['groups'] ?? [] as $group) {
            foreach ($group['items'] ?? [] as $price) {
                if (empty($price['name']) || empty($price['price'])) {
                    continue;
                }
                $items[] = [
                    'url' => '/prices',
                    'title' => (string) $price['name'],
                    'description' => 'Стоимость: ' . $price['price'] . '. Раздел «' . ($group['title'] ?? 'Цены') . '».',
                    'type' => 'Цена',
                    'keywords' => 'цена стоимость прайс ' . ($group['title'] ?? ''),
                ];
            }
        }
    }

    $index = [];
    foreach ($items as $item) {
        $search = dz_search_normalize($item['title'] . ' ' . $item['description'] . ' ' . ($item['keywords'] ?? ''));
        $item['search'] = $search;
        $item['titleSearch'] = dz_search_normalize($item['title']);
        $item['tokens'] = dz_search_tokens($search);
        unset($item['keywords']);
        $index[] = $item;
    }
    return $index;
}

/** @return list<array{url:string,title:string,description:string,type:string}> */
function dz_search(string $query, int $limit = 8): array
{
    $query=dz_search_query($query);
    $normalized = dz_search_normalize($query);
    if ($normalized === '') {
        return [];
    }
    $queryTokens = dz_search_tokens($normalized . ' ' . implode(' ', dz_search_intent_terms($normalized)));
    $scored = [];
    foreach (dz_search_index() as $position => $item) {
        $score = 0;
        if ($item['titleSearch'] === $normalized) {
            $score += 240;
        } elseif (str_starts_with($item['titleSearch'], $normalized)) {
            $score += 130;
        } elseif (str_contains($item['titleSearch'], $normalized)) {
            $score += 95;
        } elseif (str_contains($item['search'], $normalized)) {
            $score += 60;
        }
        $matched = 0;
        foreach ($queryTokens as $queryToken) {
            $best = 0;
            foreach ($item['tokens'] as $token) {
                if ($token === $queryToken) {
                    $best = str_contains($item['titleSearch'], $queryToken) ? 70 : 3;
                    break;
                }
                if (str_starts_with($token, $queryToken) || str_starts_with($queryToken, $token)) {
                    $best = max($best, str_contains($item['titleSearch'], $token) ? 45 : 2);
                } elseif (dz_search_one_edit($queryToken, $token)) {
                    $best = max($best, str_contains($item['titleSearch'], $token) ? 30 : 1);
                }
            }
            if ($best > 0) {
                $matched++;
                $score += $best;
            }
        }
        if ($matched === count($queryTokens) && $matched > 1) {
            $score += 35;
        }
        if ($item['type'] === 'Услуга') {
            $score += 4;
        }
        if($item['type']==='Врач'){
            $doctor=doctor_profile(ltrim($item['url'],'/'));
            $roles=[3=>'/ортопед/u',4=>'/ортодонт/u',2=>'/хирург|имплантолог/u',1=>'/терапевт/u',6=>'/детск.*стоматолог|стоматолог.*дет/u'];
            foreach($roles as $specialty=>$pattern)if(in_array($specialty,$doctor['specializations']??[],true)&&preg_match($pattern,$normalized)){$score+=250;$matched=max(1,$matched);}
        }
        if(!preg_match('/дет|ребен|малыш|молоч/u',$normalized)&&preg_match('/дет|ребен|молоч/u',$item['titleSearch']))$score-=55;
        if(preg_match('/болит зуб|зубная боль|ноет зуб/u',$normalized)&&$item['url']==='/services/lechenie-kariesa')$score+=100;
        if(preg_match('/страхов|дмс|омс|полис/u',$normalized)&&$item['url']==='/insurance-companies')$score+=180;
        if(preg_match('/подготов.*прием|первый прием/u',$normalized)&&$item['url']==='/pravila-zapisi')$score+=180;
        if ($score > 8 && $matched > 0) {
            $public = $item;
            unset($public['search'], $public['titleSearch'], $public['tokens']);
            $scored[] = ['score' => $score, 'position' => $position, 'item' => $public];
        }
    }
    usort($scored, static fn(array $a, array $b): int => [$b['score'], $a['position']] <=> [$a['score'], $b['position']]);
    $results=[];$seen=[];foreach($scored as $row){$url=$row['item']['url'];if(isset($seen[$url]))continue;$seen[$url]=true;$results[]=$row['item'];if(count($results)>=max(1,min($limit,50)))break;}return $results;
}

/** @return array{q:string,results:list<array{url:string,title:string,description:string,type:string}>} */
function dz_search_api_payload(string $query, int $limit = 8): array
{
    $query = dz_search_query($query);
    return ['q' => $query, 'results' => dz_search($query, $limit)];
}
