<?php
require __DIR__.'/../app/bootstrap.php';require __DIR__.'/../app/search.php';
$expected=['Магомед Расулов'=>'rasulov-magomed-radzhabovich','расул'=>'rasulov-magomed-radzhabovich','Марет'=>'murtazalieva-maret-akhmedovna','Муртазалиева'=>'murtazalieva-maret-akhmedovna','Карен'=>'badunts-karen-valerievich','Бадунц'=>'badunts-karen-valerievich','бадунс'=>'badunts-karen-valerievich','Яна'=>'rasulova-yana-borisovna','Расулова Яна'=>'rasulova-yana-borisovna','ортодонт'=>'rasulova-yana-borisovna','ортопед'=>'rasulov-magomed-radzhabovich','имплантолог'=>'badunts-karen-valerievich','терапевт'=>'murtazalieva-maret-akhmedovna'];
foreach($expected as $query=>$slug){$result=dz_search($query,8);if(($result[0]['url']??'')!=='/doctors/'.$slug){fwrite(STDERR,'FAIL: '.$query.' -> '.($result[0]['url']??'none')."\n");exit(1);}}
echo "PASS: 13 name, partial name, typo and adult specialization searches\n";
