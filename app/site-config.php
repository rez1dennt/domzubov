<?php
declare(strict_types=1);

/** Only server configuration can define the public origin; never request headers. */
function dz_site_config(): array {
    $fallback = 'http://127.0.0.1:8174';
    $raw = getenv('DZ_SITE_URL');
    $origin = $raw === false ? '' : $raw;
    $parts = $origin !== '' && !preg_match('/[\s\\\\\x00-\x1f\x7f]/', $origin) ? parse_url($origin) : false;
    $valid = is_array($parts)
        && in_array($parts['scheme'] ?? '', ['http', 'https'], true)
        && isset($parts['host'])
        && !isset($parts['user']) && !isset($parts['pass'])
        && !isset($parts['query']) && !isset($parts['fragment'])
        && (!isset($parts['path']) || $parts['path'] === '/')
        && (!isset($parts['port']) || ($parts['port'] >= 1 && $parts['port'] <= 65535));
    $host = $valid ? strtolower($parts['host']) : '';
    $bareHost = trim($host, '[]');
    $valid = $valid && (filter_var($bareHost, FILTER_VALIDATE_IP) !== false
        || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false);
    if ($valid) {
        if(($parts['scheme']==='https'&&($parts['port']??null)===443)||($parts['scheme']==='http'&&($parts['port']??null)===80))unset($parts['port']);
        $origin = $parts['scheme'].'://'.$host.(isset($parts['port']) ? ':'.$parts['port'] : '');
    } else {
        $origin = $fallback;
        $bareHost = '127.0.0.1';
    }
    $indexHost = rtrim($bareHost, '.');
    // Publishing requires a real DNS name, not a local alias or IP spelling.
    $local = !str_contains($indexHost, '.')
        || preg_match('/^[0-9.]+$/', $indexHost)
        || filter_var($indexHost, FILTER_VALIDATE_IP) !== false
        || preg_match('/(?:^|\.)(?:localhost|local|localdomain|internal|test|invalid|example)$/i', $indexHost)
        || preg_match('/(?:^|\.)example\.(?:com|net|org)$/i', $indexHost);
    return [
        'base_url' => $origin,
        'allow_indexing' => $valid && !$local && getenv('DZ_ALLOW_INDEXING') === '1',
    ];
}
