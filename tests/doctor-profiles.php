<?php
require __DIR__.'/../app/bootstrap.php';require_once __DIR__.'/../app/doctors-data.php';
function doctor_check(bool $ok,string $label):void{if(!$ok){fwrite(STDERR,"FAIL: $label\n");exit(1);}}
$doctors=doctor_data()['doctors'];doctor_check(count($doctors)===4,'Four named doctors');
foreach($doctors as $doctor){doctor_check(!str_contains($doctor['name'],'Специалист Дома'),'Real name');doctor_check(doctor_profile('doctors/'.$doctor['slug'])!==null,'Profile lookup');doctor_check(is_file(PROJECT_ROOT.$doctor['photoLocal']),'Portrait or honest placeholder exists');doctor_check(count($doctor['education'])>0,'Education provided');}
doctor_check($doctors[0]['practiceSince']===2018,'Practice since 2018');doctor_check($doctors[0]['schedule']==='Ежедневно, 09:00–21:00','Confirmed schedule');
foreach(array_slice($doctors,1) as $doctor)doctor_check($doctor['schedule']==='По предварительной записи','No invented schedule');
doctor_check(doctor_profile('doctors/not-a-real-doctor')===null,'No arbitrary profile');
echo "PASS: four real profiles, education, schedules and assets\n";
