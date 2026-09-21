<?php
declare(strict_types=1);
function doctor_data(): array {
    static $data;if($data!==null)return $data;
    $doctors=[
      [
        'name'=>'Расулов Магомед Раджабович','slug'=>'rasulov-magomed-radzhabovich','profession'=>'Врач-стоматолог-ортопед','specializations'=>[3],
        'photoLocal'=>'/assets/images/doctor-placeholder.svg','photoAvailable'=>false,'initials'=>'МР',
        'description'=>'Восстановление зубов и ортопедическое лечение. Практикует с 2018 года.',
        'intro'=>'Помогает восстановить форму и функцию зубов. На консультации обсуждает состояние зубного ряда, варианты протезирования и последовательность лечения.',
        'practiceSince'=>2018,'schedule'=>'Ежедневно, 09:00–21:00',
        'education'=>[['year'=>'2018','title'=>'Волгоградский государственный медицинский университет'],['year'=>'2020','title'=>'МОНИКИ им. М. Ф. Владимирского']],
        'approach'=>['Ортопедическое лечение начинается с осмотра и обсуждения задач пациента. План учитывает состояние зубов и дёсен, прикус и возможные способы восстановления.','Дополнительное обучение посвящено ортопедической стоматологии, эстетическим реставрациям и взаимодействию специалистов разных направлений.'],
        'competencies'=>[['title'=>'Восстановление зубов','text'=>'Обсуждение подходящей ортопедической конструкции с учётом клинической ситуации.'],['title'=>'Эстетика улыбки','text'=>'Планирование формы и внешнего вида реставраций.'],['title'=>'Комплексное лечение','text'=>'Согласование ортопедического этапа с другими специалистами.']],
        'services'=>['/services/keramicheskie-viniry','/services/tselnokeramicheskie-koronki','/services/tsirkonievye-koronki-na-zubah-i-implantah','/services/semnye-i-nesemnye-konstruktsii-na-implantah'],
      ],
      [
        'name'=>'Муртазалиева Марет Ахмедовна','slug'=>'murtazalieva-maret-akhmedovna','profession'=>'Врач-стоматолог-терапевт','specializations'=>[1],
        'photoLocal'=>'/assets/images/doctor-placeholder.svg','photoAvailable'=>false,'initials'=>'МА',
        'description'=>'Терапевтическая стоматология. Приём взрослых.',
        'intro'=>'Ведёт терапевтический приём взрослых. Обсуждает жалобы, объясняет результаты осмотра и предлагает план лечения с учётом возраста пациента.',
        'practiceSince'=>null,'schedule'=>'По предварительной записи',
        'education'=>[['year'=>'2020','title'=>'Рязанский государственный медицинский университет им. И. П. Павлова'],['year'=>'','title'=>'Российский университет медицины (РосУниМед)']],
        'approach'=>['На приёме уделяет внимание понятным объяснениям: что происходит с зубом, какие варианты лечения доступны и как подготовиться к следующему визиту.','Работает со взрослыми пациентами. Объём помощи и последовательность процедур определяются после диагностики.'],
        'competencies'=>[['title'=>'Терапевтический приём','text'=>'Диагностика и лечение заболеваний зубов у взрослых.'],['title'=>'Эндодонтическое лечение','text'=>'Дополнительное обучение по лечению корневых каналов и повторному эндодонтическому лечению.']],
        'services'=>['/services/lechenie-kariesa','/services/lechenie-kornevyh-kanalov-pod-mikroskopom'],
      ],
      [
        'name'=>'Бадунц Карен Валериевич','slug'=>'badunts-karen-valerievich','profession'=>'Врач-стоматолог-хирург, имплантолог','specializations'=>[2],
        'photoLocal'=>'/assets/images/doctor-karen-badunts.webp','photoAvailable'=>true,'initials'=>'КБ',
        'description'=>'Сложная имплантация и восстановление зубных рядов.',
        'intro'=>'Занимается имплантацией и хирургической подготовкой к восстановлению зубов. Работает в том числе со случаями дефицита костной ткани и полным отсутствием зубов.',
        'practiceSince'=>null,'schedule'=>'По предварительной записи',
        'education'=>[['year'=>'2015','title'=>'Московский государственный медико-стоматологический университет'],['year'=>'','title'=>'Ординатура по хирургической стоматологии на базе ГКБ им. Ф. И. Иноземцева']],
        'approach'=>['Основой лечения считает тщательное планирование. Использует 3D-диагностику и хирургические шаблоны, чтобы выбрать подходящую тактику вмешательства.','Повышает квалификацию на курсах российских и международных специалистов, в том числе А. Решетникова, Т. Гранди и А. Смоляковой. Обсуждает с пациентом этапы лечения и восстановления.'],
        'competencies'=>[['title'=>'Сложная имплантация','text'=>'Протоколы All-on-4 и All-on-6, транссинусная и птеригоидальная имплантация по показаниям.'],['title'=>'Костная и мягкотканная пластика','text'=>'Синус-лифтинг, восстановление объёма кости и работа с мягкими тканями.'],['title'=>'Цифровое планирование','text'=>'3D-диагностика и хирургические шаблоны для навигации.'],['title'=>'Хирургическая стоматология','text'=>'Удаление зубов, аутотрансплантация и другие вмешательства по клиническим показаниям.']],
        'services'=>['/services/ustanovka-implanta','/services/prosthetics','/services/sinus-lifting','/services/uvelichenie-obema-kostnoj-i-myagkoj-tkani'],
      ],
      [
        'name'=>'Расулова Яна Борисовна','slug'=>'rasulova-yana-borisovna','profession'=>'Врач-ортодонт','specializations'=>[4],
        'photoLocal'=>'/assets/images/doctor-yana-rasulova.webp','photoAvailable'=>true,'initials'=>'ЯР',
        'description'=>'Исправление прикуса у взрослых. Элайнеры и брекет-системы.',
        'intro'=>'Помогает исправить положение зубов и прикус. Работает с элайнерами и брекет-системами, подбирая метод с учётом диагностики и задач пациента.',
        'practiceSince'=>null,'schedule'=>'По предварительной записи',
        'education'=>[['year'=>'','title'=>'Волгоградский государственный медицинский университет'],['year'=>'','title'=>'Клиническая ординатура МОНИКИ им. М. Ф. Владимирского по ортодонтии']],
        'approach'=>['Лечение начинает с диагностики и цифрового планирования. Объясняет различия между элайнерами и брекетами, обсуждает этапы, уход и контрольные визиты.','При необходимости работает совместно с хирургом, ортопедом и терапевтом: учитывает состояние зубов, имеющиеся реставрации и последующее восстановление улыбки.'],
        'competencies'=>[['title'=>'Лечение на элайнерах','text'=>'3D-сканирование, цифровое планирование и контроль перемещения зубов.'],['title'=>'Брекет-системы','text'=>'Выбор конструкции с учётом клинических показаний и задач лечения.'],['title'=>'Лечение взрослых','text'=>'Ортодонтическое лечение с учётом возраста, состояния зубов и реставраций.'],['title'=>'Подготовка к восстановлению зубов','text'=>'Согласование положения зубов перед протезированием, реставрацией и имплантацией.']],
        'services'=>['/services/ispravlenie-prikusa','/services/ispravlenie-prikusa-breket','/services/ispravlenie-prikusa-with-aligners'],
      ],
    ];
    $certFile=PROJECT_ROOT.'/content/doctor-certificates.json';$certificates=is_file($certFile)?json_decode(file_get_contents($certFile),true,512,JSON_THROW_ON_ERROR):[];
    foreach($doctors as &$doctor){$doctor['clinics']=[1];$doctor['url']='/doctors/'.$doctor['slug'];$doctor['certificates']=array_values(array_filter($certificates[$doctor['slug']]??[],static fn(array $certificate):bool=>($certificate['document']??'')!=='/assets/documents/maret-apexogenesis.pdf'));}unset($doctor);
    return $data=['doctors'=>$doctors,'specializations'=>[['id'=>1,'title'=>'Терапия'],['id'=>2,'title'=>'Хирургия и имплантация'],['id'=>3,'title'=>'Ортопедия'],['id'=>4,'title'=>'Ортодонтия']]];
}
function doctor_profile(?string $route): ?array {
    if(!$route||!str_starts_with($route,'doctors/'))return null;
    foreach(doctor_data()['doctors'] as $doctor)if($route==='doctors/'.$doctor['slug'])return $doctor;
    return null;
}
