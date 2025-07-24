<?php

/**
 * Plugin Name: Website Chat Button: Kommo integration
 * Plugin URI: https://wordpress.org/plugins/website-chat-button-kommo-integration/
 * Description: Let your customers contact you directly from your website with a chat button, conveniently manage all interactions through Kommo.
 * Version: 1.3.1
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: Kommo
 * Author URI: https://www.kommo.com/
 * License: GPLv2 or later
 * Text Domain: website-chat-button-kommo-integration
 * Domain Path: /main/lang
 */

if (!defined('ABSPATH')) {
    exit;
}

$_SESSION['kommoflash'] = [
    'trial_expired'         => 0,
    'nonce' => null,
    'data' => [
        'modal_chat_button_select' => [],
    ],
    'errors' => [],
    'notifications' => [],
    'account' => [
        'account_sign_init' => 0,
    ],
];
$_SESSION['kommoflash_locale']['locale'] = explode('_', get_locale())[0] ?? 'en';

require_once __DIR__ . '/main/KommoFlashFunctions.php';

KommoFlashFunctions::includeConfig(__DIR__ . '/main');
KommoFlashFunctions::includeLibs(__DIR__ . '/main');

use WebsiteChatButtonKommoIntegration\Enum\KommoWCBOptionsEnum;
use WebsiteChatButtonKommoIntegration\PluginRESTController;

function kommoflash_admin_enqueue_scripts()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('kommo-button-script', plugins_url('main/script-admin-ext-integration.js', __FILE__), [], KOMMOFLASH_PLUGIN_VERSION, true);

    $dbData = KommoFlashFunctions::getOptionsValue([
        'access_token',
        KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT,
    ]);

    $nonce = wp_create_nonce('kommoflash-ajax-nonce');
    wp_localize_script('kommoflash-script-admin-js', 'kommo_admin_data',
        [
            'plugin_is_active' => is_plugin_active(KOMMOFLASH_PLUGIN_PAGE_ACTIVATION_ID),
            'plugin_is_signed' => !empty($dbData['access_token'])
                && !empty($dbData[KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT]),
            'plugin_deactivate_confirm' => [
                'lang' => __('Are you sure you want to deactivate', 'website-chat-button-kommo-integration') . ' ' . __('Website Chat Button: Kommo integration', 'website-chat-button-kommo-integration') . '?',
                'page_id' => KOMMOFLASH_PLUGIN_PAGE_ID
            ],
            'plugin_inbox_counter' => [
                'count_init' => 0,
                'count_limit' => KOMMOFLASH_INBOX_LIMIT_MESSAGE,
            ],
            'nonce' => $nonce,
        ]
    );

    if (isset($_SESSION['kommoflash'])) {
        $_SESSION['kommoflash']['nonce'] = $nonce;
    }
}

function kommoflash_enqueue_scripts()
{
    wp_enqueue_script('kommoflash-script-js', plugins_url('script.js', __FILE__), [], KOMMOFLASH_PLUGIN_VERSION, true);
}

function kommoflash_init()
{
    $path = dirname(plugin_basename(__FILE__)) . "/main/lang";
    load_plugin_textdomain(KOMMOFLASH_LOCALE_TEXT_DOMAIN, false, $path);
    KommoFlashFunctions::setErrors();
}

function kommoflash_admin_menu()
{
    wp_enqueue_script('kommoflash-script-admin-js', plugins_url('script-admin.js', __FILE__), [], KOMMOFLASH_PLUGIN_VERSION, true);

    add_menu_page(
        KOMMOFLASH_TEXT_PLUGIN_TITLE_ADMIN,
        KOMMOFLASH_TEXT_PLUGIN_MENU_ADMIN,
        'manage_options',
        KOMMOFLASH_PLUGIN_PAGE_ID,
        'kommoflash_plugin_page_content',
        plugins_url('logo.svg', __FILE__),
        KOMMOFLASH_PLUGIN_MENU_POSITION
    );
}

function kommoflash_plugin_page_content()
{
    $functions = new KommoFlashFunctions();
    $functions->apiInit();

    $accountInfo = $functions->apiGetAccountInfo();
    $accountSign = KommoFlashFunctions::getAccountSign();

    $data = KommoFlashFunctions::getOptionsValue([
        KommoWCBOptionsEnum::TRIAL_DATE_START,
        KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT,
        KommoWCBOptionsEnum::CHAT_BUTTON_DATA,
        KommoWCBOptionsEnum::ACCOUNT_SIGN_REFERER,
    ]);

    $_SESSION['kommoflash']['account_links'] = $functions->getAccountLinks();

    $page = isset($accountInfo['id']) ? KOMMOFLASH_PATH_PAGE_HOME : KOMMOFLASH_PATH_PAGE_AUTH;

    if ($page === KOMMOFLASH_PATH_PAGE_HOME) {
        $_SESSION['kommoflash'][KommoWCBOptionsEnum::ACCOUNT_INFO] = $accountInfo;
        $_SESSION['kommoflash'][KommoWCBOptionsEnum::ACCOUNT_SIGN] = $accountSign;
        $_SESSION['kommoflash'][KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT] =
            (int) $data[KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT];
        $_SESSION['kommoflash']['chat_button_data_exist'] = !empty(
            $data[KommoWCBOptionsEnum::CHAT_BUTTON_DATA]
        ) ? 1 : 0;

        wp_register_script(
            'kommoflash_home_page_script',
            plugins_url('main/plugin_page/home_page/js/script.js', __FILE__),
            [],
            KOMMOFLASH_PLUGIN_VERSION,
            true,
        );
        wp_localize_script(
            'kommoflash_home_page_script',
            'kommoflash_home_page_data',
            [
                'account' => [
                    'is_trial_expired' => esc_attr(sanitize_text_field($_SESSION['kommoflash']['trial_expired'] ?? '0')),
                    'links' => [
                        'subscribe' => esc_html(sanitize_url($_SESSION['kommoflash']['account_links']['account']['subscribe'] ?? '')),
                    ],
                ],
                'lang' => [
                    'The Kommo server is not responding' => esc_html__(
                        'The Kommo server is not responding',
                        'website-chat-button-kommo-integration',
                    ),
                    'We are aware of the issue and are working to fix it' => esc_html__(
                        'We’re aware of the issue and are working to fix it',
                        'website-chat-button-kommo-integration',
                    ),
                    'Try again' => esc_html__('Try again', 'website-chat-button-kommo-integration'),
                    'Days left in your trial. ' => esc_html__(
                        'Days left in your trial. ',
                        'website-chat-button-kommo-integration',
                    ),
                    'Subscribe' => esc_html__('Subscribe', 'website-chat-button-kommo-integration'),
                    'Try your' => esc_html__('Try your', 'website-chat-button-kommo-integration'),
                    'website chat button' => esc_html__('website chat button', 'website-chat-button-kommo-integration'),
                    'to see how leads flow directly to your inbox.' => esc_html__(
                        'to see how leads flow directly to your inbox.',
                        'website-chat-button-kommo-integration',
                    ),
                    'You are about to log out' => esc_html__(
                        'You’re about to log out',
                        'website-chat-button-kommo-integration',
                    ),
                    'When you log back in, a chat button will be automatically created.' => esc_html__(
                        'When you log back in, a chat button will be automatically created.',
                        'website-chat-button-kommo-integration',
                    ),
                    'You do not have any chat buttons yet.' => esc_html__(
                        'You don’t have any chat buttons yet.',
                        'website-chat-button-kommo-integration',
                    ),
                    'Log out of your Kommo account, then log back in to automatically generate a new chat button.' => esc_html__(
                        'Log out of your Kommo account, then log back in to automatically generate a new chat button.',
                        'website-chat-button-kommo-integration',
                    ),
                    'You have no unread messages' => esc_html__(
                        'You have no unread messages',
                        'website-chat-button-kommo-integration',
                    ),
                ],
                'site' => [
                    'url' => esc_html(sanitize_url(get_site_url())),
                ],
                'chat_button_data_exist' => esc_attr(
                    sanitize_text_field($_SESSION['kommoflash']['chat_button_data_exist'] ?? '')
                ),
                'constants' => [
                    'trial_show_modal_frame' => esc_attr(KOMMOFLASH_TRIAL_SHOW_MODAL_FRAME),
                    'trial_count_days_edit_bg' => esc_attr(KOMMOFLASH_TRIAL_COUNT_DAYS_EDIT_BG),
                    'inbox_limit_message' => esc_attr(KOMMOFLASH_INBOX_LIMIT_MESSAGE),
                    'chat_button_switch_false' => esc_attr(KOMMOFLASH_CHAT_BUTTON_SWITCH_FALSE),
                    'chat_button_switch_true' => esc_attr(KOMMOFLASH_CHAT_BUTTON_SWITCH_TRUE),
                    'chat_button_need_button' => esc_attr(KOMMOFLASH_CHAT_BUTTON_NEED_BUTTON),
                ],
            ],
        );
        wp_enqueue_script('kommoflash_home_page_script');

        require_once KOMMOFLASH_PLUGIN_PATH . $page;
    } else {
        $isTrialExpired = $functions->getTrialExpired();
        $_SESSION['kommoflash']['trial_expired'] = !empty($isTrialExpired) ? $isTrialExpired : 0;
        $_SESSION['kommoflash']['account'][KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT] = !empty(
            $data[KommoWCBOptionsEnum::ACCOUNT_SIGN_REFERER]
        ) ? 1 : 0;

        KommoFlashFunctions::siteLogout();

        wp_register_script(
            'kommoflash_plugin_page_script',
            plugins_url('main/plugin_page/js/script.js', __FILE__),
            [],
            KOMMOFLASH_PLUGIN_VERSION,
            true,
        );
        wp_localize_script(
            'kommoflash_plugin_page_script',
            'kommoflash_plugin_page_data',
            [
                'account' => [
                    'is_trial_expired' => esc_attr(sanitize_text_field($_SESSION['kommoflash']['trial_expired'] ?? '')),
                    KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT => esc_attr(
                        sanitize_text_field(
                            $_SESSION['kommoflash']['account'][KommoWCBOptionsEnum::ACCOUNT_SIGN_INIT] ?? ''
                        ),
                    ),
                ],
                'constants' => [
                    'kommo_url' => esc_attr(KOMMOFLASH_KOMMO_URL),
                ],
            ],
        );
        wp_enqueue_script('kommoflash_plugin_page_script');

        require_once KOMMOFLASH_PLUGIN_PATH . $page;
    }
}

function kommoflash_ajax_dashboard_action()
{
    $response = [];

    $functions = new KommoFlashFunctions();
    $functions->apiInit();

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['trial_get_value'])
    ) {
        $value = $functions->getAccountTrial();
        if (is_numeric($value)) {
            $response = [
                'status' => WP_Http::OK,
                'message' => '',
                'data' => $value
            ];
        } else {
            $response = [
                'status' => WP_Http::INTERNAL_SERVER_ERROR,
                'message' => 'Value is not numeric',
                'data' => 0
            ];
        }
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['inbox_get_count'])
    ) {
        $inbox = $functions->getAccountInbox();
        if(empty($inbox['error'])) {
            $response = [
                'status'    => WP_Http::OK,
                'message'   => 'Inbox fetch success',
                'data'      => $inbox['data']
            ];
        } else {
            $response = [
                'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                'message'   => $inbox['error'],
                'data'      => $inbox['data']
            ];
        }
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['auth_error_frontend_log'])
    ) {
        $message = 'Frontend auth error, action: ' . sanitize_text_field(
            wp_unslash($_POST['data']['action'] ?? 'unknown')
        );

        $response = [
            'status' => WP_Http::INTERNAL_SERVER_ERROR,
            'message' => $message,
            'data' => null,
        ];
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['dashboard_main_menu_inbox_counter_error_log'])
    ) {
        $message = 'Dashboard get inbox count error, action: ' . sanitize_text_field(
            wp_unslash($_POST['data']['action'] ?? 'unknown')
        );

        $response = [
            'status' => WP_Http::INTERNAL_SERVER_ERROR,
            'message' => $message,
            'data' => null,
        ];
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['logout'])
    ) {
        $logout = KommoFlashFunctions::siteLogout();
        if(empty($logout['error'])) {
            $response = [
                'status'    => WP_Http::OK,
                'message'   => 'Logout success completed',
                'data'      => $logout['data']
            ];
        } else {
            $response = [
                'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                'message'   => $logout['error'],
                'data'      => []
            ];
        }
    }
    if (
        isset($_GET['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_GET['button_update_db'])
    ) {
        $buttonState = $functions->toggleButtonVisible((int)sanitize_text_field(wp_unslash($_GET['state'] ?? '0')));
        if (empty($buttonState)) {
            $response = [
                'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                'message'   => 'Error update button state',
                'data'      => null
            ];
        } else {
            $response = [
                'status'    => WP_Http::OK,
                'message'   => '',
                'data'      => true
            ];
        }
    }

    if (
        isset($_GET['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_GET['button_get_db'])
    ) {
        $buttonState = $functions->getButtonVisible();
        if (empty($buttonState)) {
            $response = [
                'status'    => WP_Http::OK,
                'message'   => '',
                'data' => false
            ];
        } else if ($buttonState == KOMMOFLASH_CHAT_BUTTON_SWITCH_TRUE) {
            $response = [
                'status'    => WP_Http::OK,
                'message'   => '',
                'data' => true
            ];
        } else {
            $response = [
                'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                'message'   => 'Error fetch button state',
                'data'      => null
            ];
        }
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['chat_button_select'])
    ) {
        $sourceId = (int)sanitize_text_field(wp_unslash($_POST['source_id'] ?? '0'));
        $functions->apiAddOnlineChatToSource($sourceId);
        $functions->apiBindSiteToSource($sourceId);
        $source = $functions->apiGetSourceInfoCurrent($sourceId);

        if(empty($source['error'])) {
            $response = [
                'status'    => WP_Http::OK,
                'message'   => 'get source data success',
                'data'      => $source['data']
            ];
        } else {
            $response = [
                'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                'message'   => $source['error'],
                'data'      => []
            ];
        }
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['chat_button_create'])
    ) {
        $sourceCreated = $functions->apiCreateSourceInfo(['action' => 'chat_button_create_click']);
        $isSourceValid = $functions->checkSource($sourceCreated);
        if ($isSourceValid) {
            $source = $functions->apiGetSourceInfoCurrent($sourceCreated['data']['source_id']);
            if(empty($source['error'])) {
                $response = [
                    'status'    => WP_Http::OK,
                    'message'   => 'get source data success',
                    'data'      => $source['data']
                ];
            } else {
                $response = [
                    'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                    'message'   => $source['error'],
                    'data'      => []
                ];
            }
        } else {
            $response = [
                'status'    => WP_Http::INTERNAL_SERVER_ERROR,
                'message'   => 'Error after Create button',
                'data'      => []
            ];
        }
    }

    if (
        isset($_POST['nonce'])
        && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'kommoflash-ajax-nonce')
        && isset($_POST['get_buttons_data'])
    ) {
        $result = $functions->getButtonsData();
        $response = [
            'status'    => WP_Http::INTERNAL_SERVER_ERROR,
            'message'   => '',
            'data'      => $result
        ];
    }

    echo wp_json_encode([
        'status' => $response['status'], 'message' => $response['message'], 'data' => $response['data']
    ]);
    die;
}

function kommoflash_load_textdomain_mofile_filter_es($mofile, $domain)
{
    if ($domain !== KOMMOFLASH_LOCALE_TEXT_DOMAIN) {
        return $mofile;
    }

    $locale = get_locale();
    $info = pathinfo($mofile);

    if (in_array($locale, KOMMOFLASH_LOCALE_LIST_ES)) {
        $mofile = $info['dirname'] . '/' . KOMMOFLASH_LOCALE_TEXT_DOMAIN . '-es_ES' . '.mo';
    }
    if (in_array($locale, KOMMOFLASH_LOCALE_LIST_PT)) {
        $mofile = $info['dirname'] . '/' . KOMMOFLASH_LOCALE_TEXT_DOMAIN . '-pt_PT' . '.mo';
    }

    return $mofile;
}

function kommoflash_footer()
{
    $functions = new KommoFlashFunctions();
    $state = $functions->getAccountButtonSwitch();

    if ($state['data'] == KOMMOFLASH_CHAT_BUTTON_SWITCH_TRUE) {
        $script = KommoFlashFunctions::getOptionValue(KommoWCBOptionsEnum::CHAT_BUTTON_SCRIPT);
        if (empty($script)) {
            return;
        }
        $scriptData = KommoFlashFunctions::getScriptWithLocale(($script));
        wp_add_inline_script('kommoflash-script-js', $scriptData['locale']);
        wp_add_inline_script('kommoflash-script-js', $scriptData['main']);
    }
}

add_action('wp_footer', 'kommoflash_footer');
add_action('admin_menu', 'kommoflash_admin_menu');

add_action('init', 'kommoflash_init');

add_action('admin_enqueue_scripts', 'kommoflash_admin_enqueue_scripts');
add_action('wp_enqueue_scripts', 'kommoflash_enqueue_scripts', 99);

add_action('wp_ajax_kommo_dashboard_action', 'kommoflash_ajax_dashboard_action');
add_action('activated_plugin', 'kommoflash_activated_plugin');

add_action( 'rest_api_init', function () {
    $controller = new PluginRESTController();
    $controller->register_routes();
});

add_filter('load_textdomain_mofile', 'kommoflash_load_textdomain_mofile_filter_es', 99, 2);

register_activation_hook(__FILE__, 'kommoflash_activation');
register_deactivation_hook(__FILE__, 'kommoflash_deactivation');
register_uninstall_hook(__FILE__, 'kommoflash_uninstall');

add_action('upgrader_process_complete', 'kommo_wcb_upgrader_process_complete');
