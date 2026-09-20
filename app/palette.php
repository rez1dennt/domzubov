<?php
declare(strict_types=1);
/** Map cold source UI colours to the one brand palette; photos are untouched. */
function palette_for(int $r,int $g,int $b): ?array {
    if($b <= $r+10 || $b <= $g+4) return null;
    if(max($r,$g,$b)-min($r,$g,$b)<40) return max($r,$g,$b)>130 ? ['muted','113 110 101'] : ['ink','32 32 29'];
    if($r>160 && $g>180) return ['surface-muted','238 236 229'];
    if($r>115 && $g>170) return ['gold-soft','234 217 185'];
    if($b<145) return ['ink','32 32 29'];
    return ['gold','211 171 98'];
}
function theme_colors(string $text,bool $variables=true): string {
    $text=preg_replace_callback('/#[0-9a-f]{3,8}\b/i',function($m)use($variables){
        $hex=substr($m[0],1);if(!in_array(strlen($hex),[3,4,6,8],true))return $m[0];
        if(strlen($hex)<5)$hex=implode('',array_map(fn($v)=>$v.$v,str_split($hex)));
        $mapped=palette_for(hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2)));
        if(!$mapped)return $m[0];
        $alpha=strlen($hex)===8?hexdec(substr($hex,6,2))/255:1;
        $rgb=$variables?'var(--'.$mapped[0].'-rgb)':$mapped[1];
        return 'rgb('.$rgb.($alpha<1?' / '.round($alpha,4):'').')';
    },$text);
    return preg_replace_callback('/rgba?\(\s*(\d{1,3})[ ,]+(\d{1,3})[ ,]+(\d{1,3})(\s*[,\/]\s*)?/i',function($m)use($variables){
        $mapped=palette_for((int)$m[1],(int)$m[2],(int)$m[3]);if(!$mapped)return $m[0];
        return 'rgb('.($variables?'var(--'.$mapped[0].'-rgb)':$mapped[1]).(isset($m[4])?' / ':'');
    },$text);
}
