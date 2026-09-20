<?php
declare(strict_types=1);

/** The public app is read-only. Mailto forms do not have a server submission endpoint. */
function dz_security_error(int $status,string $message): never {
    http_response_code($status);
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['error'=>$message],JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP);
    exit;
}

function dz_security_boot(): void {
    static $done=false;if($done)return;$done=true;
    ini_set('display_errors','0');ini_set('log_errors','1');header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; script-src-attr 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-src https://yandex.ru https://yandex.com; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'");
    if(($_SERVER['HTTPS']??'')==='on')header('Strict-Transport-Security: max-age=31536000');
    if(!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)){
        header('Allow: GET, HEAD');dz_security_error(405,'Этот способ запроса не поддерживается. Для записи используйте онлайн-запись или телефон.');
    }
    if(strlen($_SERVER['REQUEST_URI']??'')>4096)dz_security_error(414,'Слишком длинный адрес запроса.');
    $configFile=__DIR__.'/site-config.php';if(is_file($configFile))require_once $configFile;
    $allowed=dz_allowed_hosts();
    if(!in_array(strtolower($_SERVER['HTTP_HOST']??''),$allowed,true))dz_security_error(421,'Адрес сайта не настроен.');
    set_exception_handler(static function(Throwable $error):void{
        error_log('Dom Zubov application failure: '.get_class($error));
        dz_security_error(500,'Не удалось обработать запрос. Повторите позже или свяжитесь с клиникой.');
    });
}

/** Vercel overwrites x-real-ip at its edge; other hosts use the actual connection. */
function dz_client_address(): string {
    $remote=(string)($_SERVER['REMOTE_ADDR']??'unknown');
    if (getenv('VERCEL')==='1') {
        $platformIp=$_SERVER['HTTP_X_REAL_IP']??'';
        if (is_string($platformIp) && filter_var($platformIp,FILTER_VALIDATE_IP)!==false) return $platformIp;
    }
    return $remote;
}

/** Fixed-size, expiring counters; no raw IPs, queries or patient details are stored. */
function dz_rate_limit(string $bucket,int $limit,int $window,?int $now=null,?string $directory=null,?string $identity=null): int {
    $now??=time();
    $directory??=sys_get_temp_dir().'/domzubov-security-'.substr(hash('sha256',__DIR__),0,16);
    if(!is_dir($directory)&&!@mkdir($directory,0700,true)&&!is_dir($directory))throw new RuntimeException('Rate limit storage unavailable');
    $secretPath=$directory.'/secret';
    $secretHandle=@fopen($secretPath,'c+b');if(!$secretHandle)throw new RuntimeException('Rate limit key unavailable');
    try{
        if(!flock($secretHandle,LOCK_EX))throw new RuntimeException('Rate limit key lock unavailable');
        $secret=stream_get_contents($secretHandle,128);
        if(strlen($secret)!==64){$secret=bin2hex(random_bytes(32));rewind($secretHandle);ftruncate($secretHandle,0);if(fwrite($secretHandle,$secret)!==64)throw new RuntimeException('Rate limit key write failed');fflush($secretHandle);}
    }finally{flock($secretHandle,LOCK_UN);fclose($secretHandle);}
    $identity??=dz_client_address();
    $key=hash_hmac('sha256',$bucket.'|'.$identity,$secret);
    $handle=@fopen($directory.'/limits.json','c+b');if(!$handle)throw new RuntimeException('Rate limit storage unavailable');
    try{
        if(!flock($handle,LOCK_EX))throw new RuntimeException('Rate limit lock unavailable');
        $raw=stream_get_contents($handle,2097153);if(strlen($raw)>2097152)throw new RuntimeException('Rate limit capacity exceeded');
        $entries=$raw===''?[]:json_decode($raw,true,512,JSON_THROW_ON_ERROR);
        if(!is_array($entries))throw new RuntimeException('Invalid rate limit storage');
        $entries=array_filter($entries,static fn($entry)=>is_array($entry)&&($entry['until']??0)>$now);
        if(!isset($entries[$key])&&count($entries)>=10000)return $window;
        $entry=$entries[$key]??['count'=>0,'until'=>$now+$window];
        if($entry['count']>=$limit)return max(1,$entry['until']-$now);
        $entry['count']++;$entries[$key]=$entry;$encoded=json_encode($entries,JSON_THROW_ON_ERROR);
        rewind($handle);if(!ftruncate($handle,0)||fwrite($handle,$encoded)!==strlen($encoded))throw new RuntimeException('Rate limit write failed');fflush($handle);
        return 0;
    }finally{flock($handle,LOCK_UN);fclose($handle);}
}

function dz_guard_search(bool $api=true): void {
    header('Cache-Control: no-store');header('X-Robots-Tag: noindex, nofollow');
    if($api&&($_SERVER['HTTP_SEC_FETCH_SITE']??'')==='cross-site')dz_security_error(403,'Поиск доступен со страниц этого сайта.');
    $query=$_GET['q']??'';
    if(!is_string($query)||strlen($query)>800||!preg_match('//u',$query)||preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',$query))dz_security_error(400,'Некорректный поисковый запрос.');
    if(count(preg_split('//u',$query,-1,PREG_SPLIT_NO_EMPTY)?:[])>200)dz_security_error(400,'Поисковый запрос должен содержать не более 200 символов.');
    try{
        foreach([['search-burst',30,10],['search-minute',120,60]] as [$bucket,$limit,$window]){
            $retry=dz_rate_limit($bucket,$limit,$window);
            if($retry){header('Retry-After: '.$retry);dz_security_error(429,'Слишком много запросов. Подождите немного и повторите поиск.');}
        }
    }catch(Throwable){header('Retry-After: 30');dz_security_error(503,'Поиск временно недоступен. Попробуйте позже.');}
}

/** The local PHP router must serve allowlisted assets itself to retain security headers. */
function dz_serve_asset(string $file): never {
    $types=['css'=>'text/css; charset=UTF-8','js'=>'text/javascript; charset=UTF-8','svg'=>'image/svg+xml','webp'=>'image/webp','woff2'=>'font/woff2','pdf'=>'application/pdf'];
    $extension=strtolower(pathinfo($file,PATHINFO_EXTENSION));
    if(!isset($types[$extension]))dz_security_error(404,'Файл не найден.');
    $modified=filemtime($file);$size=filesize($file);$etag='"'.dechex($modified).'-'.dechex($size).'"';
    header('Content-Type: '.$types[$extension]);header('ETag: '.$etag);header('Last-Modified: '.gmdate('D, d M Y H:i:s',$modified).' GMT');
    header('Cache-Control: public, max-age='.(isset($_GET['v'])&&is_string($_GET['v'])&&$_GET['v']===(string)$modified?'31536000, immutable':'86400'));
    if($extension==='pdf')header('X-Robots-Tag: noindex');
    if(($_SERVER['HTTP_IF_NONE_MATCH']??'')===$etag){http_response_code(304);exit;}
    header('Content-Length: '.(string)$size);
    if(($_SERVER['REQUEST_METHOD']??'GET')!=='HEAD')readfile($file);
    exit;
}
