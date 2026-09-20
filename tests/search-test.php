<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/search.php';

function search_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function search_test_top(string $query): array
{
    $results = dz_search($query, 8);
    search_test_assert($results !== [], "query '{$query}' returns results");
    return $results[0];
}

$services = array_filter(dz_search_index(), static fn(array $item): bool => $item['type'] === 'Услуга');
search_test_assert(count($services) === 40, 'index contains exactly 40 current services');

$pain = dz_search('болит зуб', 8);
search_test_assert(
    count(array_filter($pain, static fn(array $item): bool => str_contains($item['url'], 'karies') || str_contains($item['url'], 'kanal'))) > 0,
    'natural tooth-pain query finds treatment'
);

$child = dz_search('стоматолог для ребенка', 8);
search_test_assert(str_contains($child[0]['url'] ?? '', 'services/'), 'child query returns a service');
search_test_assert(
    count(array_filter($child, static fn(array $item): bool => preg_match('/dete|child|adaptation/u', $item['url']) === 1)) > 0,
    'child query finds paediatric services'
);

$braces = search_test_top('кривые зубы');
search_test_assert(
    preg_match('/prikus|breket|aligner/u', $braces['url']) === 1,
    'crooked-teeth intent ranks orthodontics first'
);

$hygiene = dz_search('чистка налета', 8);
search_test_assert(
    count(array_filter($hygiene, static fn(array $item): bool => preg_match('/gigien|hygiene/u', $item['url']) === 1)) > 0,
    'plaque query finds hygiene'
);

$implant = dz_search('нет зуба', 8);
search_test_assert(
    count(array_filter($implant, static fn(array $item): bool => preg_match('/implant|prosthet|protezir/u', $item['url']) === 1)) > 0,
    'missing-tooth query finds implantation or prosthetics'
);

$address = search_test_top('адрес на симферопольском бульваре');
search_test_assert($address['url'] === '/contacts', 'address query links to contacts');
search_test_assert(str_contains($address['description'], 'Симферопольский'), 'address result uses published address');

$price = dz_search('цена консультации ортодонта', 8);
search_test_assert(
    count(array_filter($price, static fn(array $item): bool => $item['url'] === '/prices')) > 0,
    'price query links to price catalogue'
);

$person = search_test_top('Расулов Магомед Раджабович');
search_test_assert($person['url'] === '/doctors/rasulov-magomed-radzhabovich', 'doctor name links to profile');

$typo = dz_search('имплантцаия', 8);
search_test_assert(
    count(array_filter($typo, static fn(array $item): bool => preg_match('/implant|prosthet/u', $item['url']) === 1)) > 0,
    'one-edit typo still finds implantation'
);

$payload = dz_search_api_payload('брекеты');
search_test_assert(search_test_top('подарочные сертификаты')['url']==='/gift-certificates','gift certificates indexed');
search_test_assert(search_test_top('налоговый вычет')['url']==='/tax-deduction','tax page indexed');
search_test_assert(search_test_top('конфиденциальность')['url']==='/privacy-policy','privacy page indexed');
search_test_assert(strlen(dz_search_api_payload(str_repeat('я',1000))['q'])===400,'query capped at 200 Unicode characters');
search_test_assert(array_keys($payload) === ['q', 'results'], 'API payload has stable q/results shape');
search_test_assert(count($payload['results']) <= 8, 'API defaults to eight results');
foreach ($payload['results'] as $result) {
    search_test_assert(array_keys($result) === ['url', 'title', 'description', 'type'], 'public result has no private index fields');
    search_test_assert(str_starts_with($result['url'], '/'), 'every result has an internal clickable URL');
}

echo "PASS: search index, intents, typo tolerance and API schema\n";
