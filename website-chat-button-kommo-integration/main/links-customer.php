<?php

if (!defined('ABSPATH')) {
	exit();
};

$supportCenter = KOMMOFLASH_KOMMO_URL . '/support/kb/';
$supportChat = KOMMOFLASH_KOMMO_URL . '/support/';
$demo = KOMMOFLASH_KOMMO_URL . '/get-a-demo/';
$partners = KOMMOFLASH_KOMMO_URL . '/partners/#';

if (isset($_SESSION['kommoflash_locale']['locale'])) {
    if (strtolower(esc_url_raw($_SESSION['kommoflash_locale']['locale'])) === 'pt') {
        $supportCenter = KOMMOFLASH_KOMMO_URL . '/br/recursos/';
        $supportChat = KOMMOFLASH_KOMMO_URL . '/br/entre-em-contato/';
        $demo = KOMMOFLASH_KOMMO_URL . '/br/agende-uma-demo/';
        $partners = KOMMOFLASH_KOMMO_URL . '/br/agende-uma-demo/';
    }
    if (strtolower(esc_url_raw($_SESSION['kommoflash_locale']['locale'])) === 'es') {
        $supportCenter = KOMMOFLASH_KOMMO_URL . '/es/recursos/';
        $supportChat = KOMMOFLASH_KOMMO_URL . '/es/contactanos/';
        $demo = KOMMOFLASH_KOMMO_URL . '/es/obten-una-demo/';
        $partners = KOMMOFLASH_KOMMO_URL . '/es/socios/encontrar-socio/';
    }
}

return [
    'account' => [
        'subscribe' => 'https://CUSTOMER_SUBDOMAIN/settings/pay/',
        'inbox' => 'https://CUSTOMER_SUBDOMAIN/chats/',
        'button_settings' => 'https://CUSTOMER_SUBDOMAIN/settings/pipeline/leads/PIPELINE_ID_FIRST?edit_source=BUTTON_ID_CURRENT',
        'salesbot_settings' => 'https://CUSTOMER_SUBDOMAIN/settings/communications/',
    ],
    'promo' => KOMMOFLASH_KOMMO_URL,
    'extra' => [
        'help_center' => $supportCenter,
        'chat_with_support' => $supportChat,
        'book_1_1' => $demo,
        'hire_expert_partner' => $partners,
    ],
];
