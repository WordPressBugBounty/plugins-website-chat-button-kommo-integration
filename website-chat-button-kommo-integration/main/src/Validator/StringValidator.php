<?php

namespace WebsiteChatButtonKommoIntegration\Validator;

if (!defined('ABSPATH')) {
    exit();
}

class StringValidator
{
    /**
     * @param $value
     * @return bool
     */
    public static function isString($value): bool
    {
        return is_string($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isNotEmptyString($value): bool
    {
        return StringValidator::isString($value) && !empty($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isUuid4($value): bool
    {
        return (bool)preg_match('/[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}/', $value);
    }
}
