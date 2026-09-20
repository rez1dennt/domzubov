<?php
/** Curated inline icons. Only this local, fixed SVG catalog is rendered. */
function dz_semantic_icon_catalog(): array {
    static $catalog;
    if ($catalog !== null) return $catalog;
    $shapes = [
        'heart' => ['Забота', '<path d="M20.5 4.5a5 5 0 0 0-7.1 0L12 6l-1.4-1.5a5 5 0 0 0-7.1 7L12 20l8.5-8.5a5 5 0 0 0 0-7Z"/>'],
        'shield' => ['Безопасность', '<path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6Z"/><path d="m8 12 3 3 5-6"/>'],
        'technology' => ['Технологии', '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v4m6-4v4M9 18v4m6-4v4M2 9h4m-4 6h4m12-6h4m-4 6h4"/><rect x="10" y="10" width="4" height="4" rx="1"/>'],
        'target' => ['Точный план', '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><path d="m12 12 9-9m-5 0h5v5"/>'],
        'sparkles' => ['Эстетика', '<path d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5ZM20 2v4m-2-2h4M3 18v4m-2-2h4"/>'],
        'pin' => ['Адрес', '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>'],
        'calendar' => ['Запись на приём', '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-14 4h3m4 0h3m-10 4h3"/>'],
        'phone' => ['Телефон', '<path d="m7 3 3 5-3 3a15 15 0 0 0 6 6l3-3 5 3-1 4C10 22 2 14 3 4Z"/>'],
        'family' => ['Взрослым и детям', '<circle cx="8" cy="7" r="3"/><circle cx="17" cy="10" r="2.5"/><path d="M2 21v-4a6 6 0 0 1 12 0v4m0-5a5 5 0 0 1 8 4v1"/>'],
        'plan' => ['План лечения', '<rect x="5" y="4" width="14" height="18" rx="2"/><rect x="9" y="2" width="6" height="4" rx="1"/><path d="m8 11 1 1 2-2m2 1h3m-8 6 1 1 2-2m2 1h3"/>'],
        'tooth' => ['Здоровье зубов', '<path d="M12 5C7-1 1 4 4 11c2 4 1 10 4 10 2 0 1-7 4-7s2 7 4 7c3 0 2-6 4-10 3-7-3-12-8-6Z"/>'],
        'search' => ['Диагностика', '<circle cx="10" cy="10" r="7"/><path d="m15 15 6 6M7 10h6m-3-3v6"/>'],
        'microscope' => ['Микроскоп', '<path d="m8 3 4 2-4 9-4-2ZM10 8a7 7 0 0 1 4 13M3 21h18M3 16h8m-4 0v5m4-19 3 2"/>'],
        'scan' => ['Томография', '<path d="M3 8V3h5m8 0h5v5M3 16v5h5m8 0h5v-5M3 12h18"/><path d="M9 6c-4 0-3 5-2 7 1 3 1 5 2 5s1-4 3-4 2 4 3 4 1-2 2-5c1-2 2-7-2-7l-3 1Z"/>'],
        'clock' => ['Время лечения', '<circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 3"/>'],
        'dialogue' => ['Обсуждение с врачом', '<path d="M21 11a8 8 0 0 1-8 8H8l-5 3v-7a8 8 0 0 1 10-12 8 8 0 0 1 8 8Z"/><path d="M7 9h10M7 13h7"/>'],
        'layers' => ['Восстановление тканей', '<path d="m12 3 10 5-10 5L2 8Zm-9 9 9 5 9-5M3 17l9 5 9-5"/>'],
        'crown' => ['Коронка', '<path d="m3 6 5 5 4-8 4 8 5-5-2 13H5Zm2 9h14M5 22h14"/>'],
        'implant' => ['Имплантация', '<path d="M7 3h10l2 5H5ZM9 8v11l3 3 3-3V8M8 11h8m-8 4h8m-7 4h6"/>'],
        'aligners' => ['Элайнеры', '<path d="M3 7v6a9 9 0 0 0 18 0V7l-4-3v9a5 5 0 0 1-10 0V4ZM7 10H3m18 0h-4M9 17l-2 3m8-3 2 3"/>'],
        'braces' => ['Брекеты', '<path d="M3 6c6-3 12-3 18 0v12c-6 3-12 3-18 0Z"/><path d="M2 12h20M8 5v15M16 5v15"/><rect x="5" y="10" width="4" height="4" rx="1"/><rect x="15" y="10" width="4" height="4" rx="1"/>'],
        'brush' => ['Гигиена', '<path d="m5 20 10-10m-2-7 8 8-4 4-8-8Z"/><path d="m14 4-4 4m7-1-4 4m7-1-4 4M3 22l3-3"/>'],
        'drop' => ['Чистота', '<path d="M12 2S4 11 4 15a8 8 0 0 0 16 0c0-4-8-13-8-13Z"/><path d="M8 15a4 4 0 0 0 4 4"/>'],
        'leaf' => ['Бережное лечение', '<path d="M21 3C8 1 1 8 5 16s18 5 16-13ZM3 22 16 9m-7 7v-5m0 5h5"/>'],
        'smile' => ['Улыбка', '<circle cx="12" cy="12" r="9"/><path d="M7 14a5 5 0 0 0 10 0M8 8h.01M16 8h.01"/>'],
        'child' => ['Детская стоматология', '<circle cx="12" cy="13" r="8"/><path d="M12 5c-4 0-4-4-1-4 3 0 4 3 1 4M8 11h.01M16 11h.01M8 15a5 5 0 0 0 8 0"/>'],
        'moon' => ['Спокойствие', '<path d="M20 15A9 9 0 0 1 9 3a9 9 0 1 0 11 12Z"/><path d="M17 3v4m-2-2h4"/>'],
        'pulse' => ['Контроль состояния', '<path d="M2 12h4l3-8 6 16 3-8h4"/>'],
        'air' => ['Дыхание', '<path d="M3 8h13a3 3 0 1 0-3-3M2 12h17a3 3 0 1 1-3 3M4 17h5a2 2 0 1 1-2 2"/>'],
        'bone' => ['Костная ткань', '<path d="M7 4a3 3 0 1 0-5 3 3 3 0 0 0 5 4l6 6a3 3 0 0 0 4 5 3 3 0 1 0 4-5 3 3 0 0 0-4-5L11 6a3 3 0 0 0-4-2Z"/>'],
        'growth' => ['Восстановление', '<path d="M12 22V10M12 15C3 16 2 10 3 5c7-1 10 4 9 10Zm0-5c0-6 4-8 9-8 1 6-3 10-9 8Z"/>'],
        'balance' => ['Правильный прикус', '<path d="M12 3v18M6 21h12M3 7h18M6 7l-4 8h8Zm12 0-4 8h8Z"/>'],
        'ruler' => ['Точность', '<path d="m3 16 13-13 5 5L8 21ZM12 7l2 2m-5 1 2 2m-5 1 2 2"/>'],
        'restore' => ['Повторное лечение', '<path d="M3 11a9 9 0 1 1 2 7M3 4v7h7"/><path d="M12 7v5l3 2"/>'],
        'temperature' => ['Чувствительность', '<path d="M10 14V5a2 2 0 0 1 4 0v9a4 4 0 1 1-4 0ZM12 9v8m5-11h3m-3 4h2"/>'],
        'fracture' => ['Сколы и повреждения', '<path d="M9 3C4 1 2 6 4 11c2 4 1 10 4 10 2 0 1-7 4-7s2 7 4 7c3 0 2-6 4-10 2-5 0-9-4-8M13 2l-3 5 4 2-3 5"/>'],
        'enamel' => ['Состояние эмали', '<path d="M12 5C7-1 1 4 4 11c2 4 1 10 4 10 2 0 1-7 4-7s2 7 4 7c3 0 2-6 4-10 3-7-3-12-8-6Z"/><circle cx="8" cy="8" r="1.5"/><path d="m15 7 2 2m-2 0 2-2"/>'],
        'wallet' => ['Стоимость', '<path d="M3 7V5a2 2 0 0 1 2-2h13v4M3 7h17v14H5a2 2 0 0 1-2-2Zm17 5h-5v5h5m-3-2.5h.01"/>'],
        'check' => ['Результат', '<circle cx="12" cy="12" r="9"/><path d="m7 12 3 3 7-7"/>'],
        'book' => ['Рекомендации', '<path d="M12 5C8 2 5 2 2 3v16c4-1 7 0 10 2 3-2 6-3 10-2V3c-3-1-6-1-10 2Zm0 0v16M5 7l4 1m-4 3 4 1m6-4 4-1m-4 5 4-1"/>'],
    ];
    $catalog = [];
    foreach ($shapes as $key => [$label, $shape]) {
        $catalog[$key] = ['label' => $label, 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" data-dz-icon="'.$key.'">'.$shape.'</svg>'];
    }
    return $catalog;
}

function dz_semantic_icon(string $key): string {
    $catalog = dz_semantic_icon_catalog();
    return isset($catalog[$key]) ? $catalog[$key]['svg'] : '';
}
