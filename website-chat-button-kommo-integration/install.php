<?php

use WebsiteChatButtonKommoIntegration\Enum\KommoWCBOptionsEnum;
use WebsiteChatButtonKommoIntegration\Helpers\Manager\DatabaseConnectionManager;
use WebsiteChatButtonKommoIntegration\Repository\KommoWCBOptionRepository;

if (!defined('ABSPATH')) {
	exit();
};

require_once __DIR__ . '/main/config.php';
require_once __DIR__ . '/main/KommoFlashFunctions.php';

global $kommoflashDbVersion;
$kommoflashDbVersion = '1.0';

function kommoflash_uninstall()
{
    foreach (KommoWCBOptionsEnum::all() as $optionName => $optionValue) {
        KommoWCBOptionRepository::delete($optionName);
    }
}

function kommoflash_deactivation()
{
    KommoFlashFunctions::siteLogout();
}

function kommoflash_activation()
{
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    global $kommoflashDbVersion;

    foreach (KommoWCBOptionsEnum::all() as $optionName => $optionValue) {
        KommoWCBOptionRepository::update($optionName, $optionValue);
    }

    add_option('kommoflash_db_version', $kommoflashDbVersion);
    add_option('kommoflash_toggle_public_widget', 0, '', false);
}

function kommoflash_activated_plugin($plugin)
{
    if (KOMMOFLASH_PLUGIN_PAGE_ACTIVATION_ID !== $plugin) {
        return;
    }

    wp_safe_redirect(admin_url(KOMMOFLASH_INTEGRATION_SITE_PLUGIN_PATH_ADMIN_WP));
    exit;
}

function kommo_wcb_upgrader_process_complete($upgrader_object, $options = null)
{
    $dbConnection = DatabaseConnectionManager::getConnection();

    if ($dbConnection === null) {
        return;
    }

    $kommoflashExists = (bool) $dbConnection->query(
        $dbConnection->prepare(
            'SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_name = %s;',
            $dbConnection->prefix . KOMMOFLASH_DB_TABLE
        )
    );

    if (!$kommoflashExists) {
        return;
    }

    $result = $dbConnection->get_results(
        $dbConnection->prepare(
            'SELECT option_name, option_value FROM %i;',
            $dbConnection->prefix . KOMMOFLASH_DB_TABLE
        ),
        ARRAY_A
    );

    foreach ($result as $option) {
        if (isset($option['option_name']) && isset($option['option_value'])) {
            KommoWCBOptionRepository::update($option['option_name'], $option['option_value']);
        }
    }

    $dbConnection->query(
        $dbConnection->prepare(
            'DROP TABLE IF EXISTS %i', $dbConnection->prefix . KOMMOFLASH_DB_TABLE
        )
    );
}
