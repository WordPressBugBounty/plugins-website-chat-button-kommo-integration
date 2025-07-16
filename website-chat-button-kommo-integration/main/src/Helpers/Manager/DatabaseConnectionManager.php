<?php

declare(strict_types=1);

namespace WebsiteChatButtonKommoIntegration\Helpers\Manager;

use wpdb;

if (!defined('ABSPATH')) {
    exit();
};

class DatabaseConnectionManager
{
    /**
     * @return wpdb|null
     */
    public static function getConnection(): ?wpdb
    {
        global $wpdb;

        if ($wpdb->check_connection()) {
            return $wpdb;
        }

        return null;
    }
}
