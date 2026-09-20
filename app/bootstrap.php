<?php
declare(strict_types=1);

const PROJECT_ROOT = __DIR__ . '/..';
require_once __DIR__.'/brand.php';
require_once __DIR__.'/icons.php';
require_once __DIR__.'/company.php';
require_once __DIR__.'/doctors-data.php';
function routes(): array {
    static $routes;
    return $routes ??= json_decode(file_get_contents(PROJECT_ROOT . '/content/routes.json'), true, 512, JSON_THROW_ON_ERROR);
}
function resolve_route(string $uri): ?string {
    $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');
    if (str_contains($path, "\0") || str_contains($path, '\\') || preg_match('~(?:^|/)\.\.(?:/|$)~', $path)) return null;
    $key = trim($path, '/');
    if (str_ends_with($key, '.html') || str_ends_with($key, '.php')) $key = substr($key, 0, strrpos($key, '.'));
    if ($key === '') $key = 'index';
    $aliases = require __DIR__ . '/redirects.php';
    $key = $aliases[$key] ?? $key;
    if(doctor_profile($key)!==null)return $key;
    if(in_array($key,['sitemap','privacy-policy','chertanovskaya','search'],true))return $key;
    if (isset(routes()[$key])) return $key;
    return isset(routes()[$key . '/index']) ? $key . '/index' : null;
}
function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function asset_version(string $path): string { return (string) filemtime(PROJECT_ROOT . $path); }
function brand_markup(): string {
    return '<span class="dz-logo"><img src="/assets/mark.svg" alt="" width="40" height="54"><span>ДОМ ЗУБОВ<small>СТОМАТОЛОГИЯ</small></span></span>';
}
