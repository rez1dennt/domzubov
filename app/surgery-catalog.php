<?php
declare(strict_types=1);

function dz_surgery_catalog(): array {
    static $catalog;
    return $catalog ??= json_decode(file_get_contents(__DIR__.'/../content/editorial/surgery-catalog.json'), true, 512, JSON_THROW_ON_ERROR);
}

function dz_surgery_catalog_markup(): string {
    $catalog = dz_surgery_catalog();
    $html = '<section id="surgical-services" class="section-box dz-surgery-catalog" aria-labelledby="surgery-title"><h2 id="surgery-title">'.e($catalog['title']).'</h2><div class="dz-surgery-groups">';
    foreach ($catalog['groups'] as $group) {
        $html .= '<div class="dz-surgery-group"><h3>'.e($group['title']).'</h3><ul>';
        foreach ($group['items'] as $item) {
            $link = $catalog['links'][$item] ?? null;
            $html .= '<li>'.($link ? '<a href="'.e($link).'">'.e($item).'</a>' : e($item)).'</li>';
        }
        $html .= '</ul></div>';
    }
    return $html.'</div><p class="dz-surgery-note">'.e($catalog['note']).'</p></section>';
}
