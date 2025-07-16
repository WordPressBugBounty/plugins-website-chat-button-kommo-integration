<?php

declare(strict_types=1);

namespace WebsiteChatButtonKommoIntegration\Enum;

if (!defined('ABSPATH')) {
    exit();
};

class KommoWCBOptionsEnum
{
    public const BUTTON_STATE = 'button_state';
    public const ACCESS_TOKEN = 'access_token';
    public const ACCESS_TOKEN_DATE_EXPIRED = 'access_token_date_expired';
    public const REFRESH_TOKEN = 'refresh_token';
    public const ACCOUNT_INFO = 'account_info';
    public const ACCOUNT_SIGN = 'account_sign';
    public const ACCOUNT_SIGN_REFERER = 'account_sign_referer';
    public const ACCOUNT_SIGN_SECRETS = 'account_sign_secrets';
    public const ACCOUNT_SIGN_INIT = 'account_sign_init';
    public const ACCOUNT_INIT = 'account_init';
    public const ACCOUNT_INIT_SOURCE = 'account_init_source';
    public const ACCOUNT_PIPELINE_ID_FIRST = 'account_pipeline_id_first';
    public const CHAT_BUTTON_SCRIPT = 'chat_button_script';
    public const CHAT_BUTTON_DATA = 'chat_button_data';
    public const CHAT_BUTTON_SWITCH = 'chat_button_switch';
    public const CHAT_INBOX_INIT = 'chat_inbox_init';
    public const TRIAL_DATE_START = 'trial_date_start';

    /**
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::BUTTON_STATE => '',
            self::ACCESS_TOKEN => '',
            self::ACCESS_TOKEN_DATE_EXPIRED => '',
            self::REFRESH_TOKEN => '',
            self::ACCOUNT_INFO => '',
            self::ACCOUNT_SIGN => '',
            self::ACCOUNT_SIGN_REFERER => '',
            self::ACCOUNT_SIGN_SECRETS => '',
            self::ACCOUNT_SIGN_INIT => '0',
            self::ACCOUNT_INIT => '0',
            self::ACCOUNT_INIT_SOURCE => '0',
            self::ACCOUNT_PIPELINE_ID_FIRST => '',
            self::CHAT_BUTTON_SCRIPT => '',
            self::CHAT_BUTTON_DATA => '',
            self::CHAT_BUTTON_SWITCH => '0',
            self::CHAT_INBOX_INIT => '0',
            self::TRIAL_DATE_START => '100',
        ];
    }
}
