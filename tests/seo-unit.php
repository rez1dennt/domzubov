<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';
function seo_check(bool $condition, string $message): void {
    if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); }
}
seo_check(is_file(__DIR__.'/../app/seo.php'), 'SEO module exists');
require_once __DIR__.'/../app/seo.php';

$oldUrl = getenv('DZ_SITE_URL');
$oldIndexing = getenv('DZ_ALLOW_INDEXING');
try {
    putenv('DZ_SITE_URL'); putenv('DZ_ALLOW_INDEXING');
    $_SERVER['HTTP_HOST'] = 'attacker.invalid';
    $_SERVER['HTTP_X_FORWARDED_HOST'] = 'attacker.invalid';
    $_SERVER['REQUEST_URI'] = '/?q=%22%3E%3Cscript%3E';
    seo_check(dz_site_config()['base_url'] === 'http://127.0.0.1:8174', 'Host headers cannot set canonical origin');
    seo_check(!dz_site_config()['allow_indexing'], 'Indexing defaults off');
    putenv('DZ_ALLOW_INDEXING=1');
    foreach (['http://localhost:8174', 'http://localhost.', 'http://127.0.0.1', 'http://127.5.6.7', 'http://127.1', 'http://2130706433', 'http://[::1]', 'http://[::ffff:127.0.0.1]', 'http://192.168.1.2', 'https://clinic.local', 'https://clinic.test'] as $local) {
        putenv('DZ_SITE_URL='.$local);
        seo_check(!dz_site_config()['allow_indexing'], 'Local origins remain non-indexable: '.$local);
    }
    foreach (['https://clinic.ru/path', 'https://user:password@clinic.ru', 'https://clinic.ru?q=1', 'https://clinic.ru#x', 'javascript:alert(1)', '//clinic.ru', "https://clinic.ru\r\nX-Foo:bar", 'https://clinic.ru:99999', 'https://clinic.ru\\evil'] as $invalid) {
        putenv('DZ_SITE_URL='.$invalid);
        seo_check(dz_site_config()['base_url'] === 'http://127.0.0.1:8174' && !dz_site_config()['allow_indexing'], 'Malformed origin fails closed: '.json_encode($invalid));
    }
    putenv('DZ_SITE_URL=https://clinic-seo-check.ru');
    putenv('DZ_SITE_URL=https://clinic-seo-check.ru:443');
    seo_check(dz_site_config()['base_url']==='https://clinic-seo-check.ru','Normalize HTTPS default port');
    putenv('DZ_SITE_URL=http://clinic-seo-check.ru:80');
    seo_check(dz_site_config()['base_url']==='http://clinic-seo-check.ru','Normalize HTTP default port');
    putenv('DZ_SITE_URL=https://clinic-seo-check.ru');
    seo_check(dz_site_config()['allow_indexing'], 'Explicit public origin plus opt-in permits indexing');
    $routes = dz_seo_routes();
    seo_check(count($routes) === 60 && count(editorial_services()) === 40, '60 current canonical pages, including 40 services');
    $titles = $descriptions = $canonicals = [];
    foreach ($routes as $route) {
        $seo = dz_seo_prepare($route);
        seo_check($seo['robots'] === 'index, follow', 'Canonical page indexable after opt-in: '.$route);
        seo_check(!isset($titles[$seo['title']]), 'Unique title: '.$route);
        seo_check(!isset($descriptions[$seo['description']]), 'Unique description: '.$route);
        seo_check(!isset($canonicals[$seo['canonical']]), 'Unique canonical: '.$route);
        seo_check(preg_match_all('/./us', $seo['title']) <= 90 && preg_match_all('/./us', $seo['description']) <= 200, 'Concise metadata: '.$route);
        seo_check(!str_contains($seo['canonical'], '?') && !str_contains($seo['canonical'], 'attacker'), 'Canonical excludes query and untrusted host');
        $titles[$seo['title']] = $descriptions[$seo['description']] = $canonicals[$seo['canonical']] = true;
        $graph = $seo['schema']['@graph'];
        $types = array_column($graph, '@type');
        foreach (['Organization', 'WebSite', 'WebPage'] as $type) seo_check(in_array($type, $types, true), $type.' present on '.$route);
        if($route!=='index'){
            seo_check(in_array('BreadcrumbList',$types,true),'Breadcrumb on inner page');
            $breadcrumb=$graph[array_search('BreadcrumbList',$types,true)];
            seo_check(count($breadcrumb['itemListElement'])>=2,'At least two breadcrumb items');
        }
        $org = $graph[array_search('Organization', $types, true)];
        seo_check($org['telephone'] === COMPANY_TEL && $org['email'] === COMPANY_EMAIL, 'Own contact data only');
        seo_check(count(array_filter($types, fn($type) => $type === 'Place')) === 1, 'One real location place');
        if (str_starts_with($route, 'services/')) {
            seo_check(in_array('Service', $types, true), 'Service schema present');
            $service = $graph[array_search('Service', $types, true)];
            seo_check($service['provider']['@id'] === $org['@id'], 'Service provider resolves to own organization');
        }
        $json = dz_seo_json($seo['schema']);
        seo_check(json_decode($json, true, 512, JSON_THROW_ON_ERROR) === $seo['schema'], 'JSON-LD round trip');
        foreach (['AggregateRating','Review','Offer','Physician','Dentist','openingHours','priceRange','hasCredential'] as $forbidden) seo_check(!str_contains($json, '"'.$forbidden.'"'), 'No unsupported schema claim: '.$forbidden);
    }
    foreach ([null, 'search', 'not-found', 'doctors/fake', 'dinamo', 'akciya-besplatnaya-konsultatsiya-kt'] as $excluded) {
        $seo = dz_seo_prepare($excluded);
        seo_check($seo['robots'] === 'noindex, nofollow' && $seo['canonical'] === null && $seo['schema'] === null, 'Excluded route cannot claim canonical/indexing/schema');
    }
    $xml = simplexml_load_string(dz_seo_sitemap_xml());
    seo_check($xml !== false && count($xml->url) === count($routes), 'Valid complete canonical sitemap');
    foreach ($xml->url as $url) seo_check(isset($canonicals[(string)$url->loc]), 'Sitemap contains only canonical pages');
    seo_check(str_contains(dz_seo_robots(), 'Sitemap: https://clinic-seo-check.ru/sitemap.xml'), 'Production robots advertises configured sitemap');
    seo_check(!str_contains(dz_seo_robots(), 'Disallow: /search'), 'Search remains crawlable for noindex');
    $payload = '</script><script>alert("x")</script>&';
    seo_check(!str_contains(dz_seo_json(['name'=>$payload]), '<'), 'JSON-LD escapes script delimiters');
    $seo = dz_seo_prepare('index');
    $seo['title'] = $seo['description'] = $payload;
    ob_start(); require __DIR__.'/../templates/seo-head.php'; $head = ob_get_clean();
    seo_check(!str_contains($head, '<script>alert(') && str_contains($head, '&lt;/script&gt;'), 'HTML metadata escaped');
    seo_check(substr_count($head, 'rel="canonical"') === 1, 'One canonical rendered');
    seo_check(str_contains($head, 'property="og:url"') && str_contains($head, 'name="twitter:card"'), 'Social metadata rendered');
    putenv('DZ_ALLOW_INDEXING=0');
    seo_check(dz_seo_prepare('index')['robots'] === 'noindex, nofollow', 'Staging noindex restored');
    seo_check(str_contains(dz_seo_robots(), 'Disallow: /') && !str_contains(dz_seo_robots(), 'Sitemap:'), 'Staging blocks crawl and advertises no sitemap');
    echo 'PASS: SEO metadata for '.count($routes).' pages; origins, indexing, schema, sitemap, robots and output escaping.'.PHP_EOL;
} finally {
    $oldUrl === false ? putenv('DZ_SITE_URL') : putenv('DZ_SITE_URL='.$oldUrl);
    $oldIndexing === false ? putenv('DZ_ALLOW_INDEXING') : putenv('DZ_ALLOW_INDEXING='.$oldIndexing);
}
