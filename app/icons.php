<?php
declare(strict_types=1);
function icon(string $name, string $class=''): string {
    $paths=[
        'search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
        'chevron'=>'<path d="m6 9 6 6 6-6"/>',
        'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'close'=>'<path d="m6 6 12 12M6 18 18 6"/>',
        'eye'=>'<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
        'calendar'=>'<rect x="4" y="5" width="16" height="16" rx="3"/><path d="M8 3v4m8-4v4M4 10h16m-12 4h2m4 0h2m-8 3h2"/>',
        'phone'=>'<path d="m7 3 3 5-3 2c2 4 3 5 7 7l2-3 5 3c0 3-2 4-4 4C9 20 4 15 3 7c0-2 1-4 4-4Z"/>',
        'telegram'=>'<path d="m3 11 18-7-4 17-6-6-4 3 1-6 9-5-11 7Z"/>',
        'message'=>'<path d="M20 11a8 8 0 0 1-8 8H5l-3 3v-9a10 10 0 0 1 18-6"/><path d="M7 10h8m-8 4h5"/>',
        'gift'=>'<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v9h14v-9M12 8v13"/><path d="M12 8H8a3 3 0 1 1 3-3l1 3Zm0 0h4a3 3 0 1 0-3-3l-1 3Z"/>',
        'document'=>'<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6M8 13h8m-8 4h5"/>',
        'shield'=>'<path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/><path d="m8 12 3 3 5-6"/>',
        'wallet'=>'<path d="M20 8V5H6a3 3 0 0 0 0 6h15v9H6a3 3 0 0 1-3-3V8"/><path d="M21 14h-5v3h5"/>',
        'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'pin'=>'<path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/>',
    ];
    return '<svg class="dz-icon a11y-keep '.e($class).'" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['arrow']).'</svg>';
}

/** Only local, reviewed social logos may be inlined into the footer. */
function social_icon(string $name): string {
    static $icons = [];
    if (!in_array($name, ['telegram', 'whatsapp'], true)) return '';
    return $icons[$name] ??= (string) file_get_contents(PROJECT_ROOT.'/assets/social-'.$name.'.svg');
}
