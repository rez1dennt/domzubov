<?php
declare(strict_types=1);
require __DIR__.'/../app/security.php';
$directory=sys_get_temp_dir().'/dz-rate-test-'.bin2hex(random_bytes(8));
function rate_check(bool $value,string $message):void{if(!$value)throw new RuntimeException($message);}
try{
 rate_check(dz_rate_limit('search',2,10,1000,$directory,'client-one')===0,'First request');
 rate_check(dz_rate_limit('search',2,10,1001,$directory,'client-one')===0,'Second request');
 rate_check(dz_rate_limit('search',2,10,1002,$directory,'client-one')===8,'Limit returns retry delay');
 rate_check(dz_rate_limit('search',2,10,1002,$directory,'client-two')===0,'Separate client');
 rate_check(dz_rate_limit('search',2,10,1010,$directory,'client-one')===0,'Expired window resets');
 $raw=file_get_contents($directory.'/limits.json');rate_check(!str_contains($raw,'client-one'),'Identity is not stored as plaintext');
 file_put_contents($directory.'/limits.json','broken');$rejected=false;try{dz_rate_limit('search',2,10,1020,$directory,'client-one');}catch(Throwable){$rejected=true;}
 rate_check($rejected,'Corrupt store fails closed');
 echo "PASS: rate limit, retry delay, expiry, isolation and corrupt storage\n";
}finally{foreach(['secret','limits.json'] as $file)if(is_file($directory.'/'.$file))unlink($directory.'/'.$file);if(is_dir($directory))rmdir($directory);}
