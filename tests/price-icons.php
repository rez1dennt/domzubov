<?php
require __DIR__.'/../app/bootstrap.php';require __DIR__.'/../app/presentation.php';
ob_start();render_original('prices');$html=ob_get_clean();$d=Dom\HTMLDocument::createFromString('<!doctype html><html><body>'.$html.'</body></html>',LIBXML_NOERROR);
if($d->querySelectorAll('.dz-icon-surface .dz-feature-icon')->length<3){fwrite(STDERR,"FAIL: price benefit cards need three native icons\n");exit(1);}echo "PASS: price benefit icons\n";
