<?php

if (!defined('ABSPATH')) {
    exit();
};

$Facebook = 'https://www.facebook.com/KommoCRM/';
$Linkedin = 'https://www.linkedin.com/company/kommo/';
$Instagram = 'https://www.instagram.com/kommocrm/';
$YouTube = 'https://www.youtube.com/user/amoCRM/';
$Telegram = 'https://t.me/kommoglobal/';
$appstore = 'https://apps.apple.com/us/app/kommo/id6443626329';
$google_play = 'https://play.google.com/store/search?q=kommo&c=apps&hl=en&gl=US';
if (isset($_SESSION['kommoflash_locale']['locale'])){
    switch ($_SESSION['kommoflash_locale']['locale']) {
        case 'pt':
            $Facebook = 'https://www.facebook.com/KommoBrasil/';
            $Linkedin = 'https://www.linkedin.com/company/kommobr/';
            $Instagram = 'https://www.instagram.com/kommobrasil/';
            $YouTube = 'https://www.youtube.com/channel/UCdc-vEO2bODlhdLHcmI3GfQ/';
            $Telegram = 'https://t.me/kommobrasil/';
            $appstore = 'https://apps.apple.com/br/app/kommo/id6443626329';
            $google_play = 'https://play.google.com/store/search?q=kommo&c=apps&hl=br&gl=US';
            break;
        case 'es':
            $Facebook = 'https://www.facebook.com/kommoES/';
            $Linkedin = 'https://www.linkedin.com/company/kommo-en-espanol/';
            $Instagram = 'https://www.instagram.com/kommolatam/';
            $YouTube = 'https://www.youtube.com/c/amoCRMenEspa%C3%B1ol/';
            $Telegram = 'https://t.me/kommolatam/';
            $appstore = 'https://apps.apple.com/es/app/kommo/id6443626329';
            $google_play = 'https://play.google.com/store/search?q=kommo&c=apps&hl=es&gl=US';
            break;
        case 'ru':
            $appstore = 'https://apps.apple.com/ru/app/kommo/id6443626329';
            $google_play = 'https://play.google.com/store/search?q=kommo&c=apps&hl=ru&gl=US';
            break;
        case 'id':
            $appstore = 'https://apps.apple.com/id/app/kommo/id6443626329';
            $google_play = 'https://play.google.com/store/search?q=kommo&c=apps&hl=id&gl=ID';
            break;
        case 'tr':
            $appstore = 'https://apps.apple.com/tr/app/kommo/id6443626329';
            $google_play = 'https://play.google.com/store/search?q=kommo&c=apps&hl=tr&gl=TR';
            break;
    }
}
// insert required links to `''`
return [
  	'social' => [
      	'Facebook'   => $Facebook,
      	'Linkedin'   => $Linkedin,
      	'Instagram'  => $Instagram,
      	'YouTube'    => $YouTube,
      	'Telegram'   => $Telegram,
  	],
  	'app' => [
      	'appstore'      => $appstore,
      	'google_play'   => $google_play,
  	],
];
