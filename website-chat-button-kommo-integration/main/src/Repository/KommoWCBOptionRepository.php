<?php

declare(strict_types=1);

namespace WebsiteChatButtonKommoIntegration\Repository;

if (!defined('ABSPATH')) {
    exit();
};

class KommoWCBOptionRepository
{
    private const PREFIX = 'kommo_wcb_';

    /**
     * @param string $optionName
     * @param mixed $optionValue
     * @return bool
     */
    public static function update(string $optionName, $optionValue): bool
    {
        return update_option(self::PREFIX . $optionName, $optionValue);
    }

    /**
     * @param array|string $whereItems
     * @return mixed
     */
    public static function select($whereItems)
    {
        if (is_array($whereItems)) {
            $result = [];

            foreach ($whereItems as $whereItem) {
                $result[$whereItem] = get_option(self::PREFIX . $whereItem);
            }

            return $result;
        }

        return get_option(self::PREFIX . $whereItems);
    }

    /**
     * @param string $optionName
     * @return bool
     */
    public static function delete(string $optionName): bool
    {
        return delete_option(self::PREFIX . $optionName);
    }
}
