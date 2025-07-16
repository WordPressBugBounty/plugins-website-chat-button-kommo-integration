<?php

namespace WebsiteChatButtonKommoIntegration\UseCase;

if (!defined('ABSPATH')) {
    exit();
}

use AmoCRM\OAuth2\Client\Provider\AmoCRMException;
use KommoFlashFunctions;
use WebsiteChatButtonKommoIntegration\Enum\KommoWCBOptionsEnum;

class ExchangeAndSaveTokenUseCase
{
    public function handle(string $referer, string $state, string $code): bool
    {
        $state = explode(KOMMOFLASH_STATE_SEPARATOR, $state);
        $buttonState = $state[0] ?? false;
        $siteUrl = urldecode($state[1]) ?? false;
        $siteTime = (int)$state[2] ?? false;
        $accountSign = (string)$state[3] ?? false;

        KommoFlashFunctions::tokenSaveStateDataCheck(
            $siteUrl,
            $accountSign,
            $siteTime,
        );

        $secrets = KommoFlashFunctions::authSecretGet();

        for ($i = 1; $i <= KOMMOFLASH_INTEGRATION_AUTH_SECRETS_GET_COUNT; $i++) {
            if (!$secrets) {
                sleep(1);
                $secrets = KommoFlashFunctions::authSecretGet();
            }
        }

        try {
            $serverResponse = KommoFlashFunctions::getAccessTokens($secrets, [
                'code' => $code,
                'referer' => $referer,
            ]);
        } catch (AmoCRMException $e) {
            $serverResponse = null;
        }

        KommoFlashFunctions::tokenSaveSecretCheck(
            $secrets,
            $serverResponse,
            $siteUrl,
            $accountSign
        );

        $serverResponse[KommoWCBOptionsEnum::BUTTON_STATE] = $buttonState;
        $serverResponse[KommoWCBOptionsEnum::ACCOUNT_SIGN] = $accountSign;
        $serverResponse[KommoWCBOptionsEnum::ACCOUNT_SIGN_REFERER] = $referer;

        $serverResponse = KommoFlashFunctions::authGetTokenDateExpire($serverResponse);
        $siteResponse = KommoFlashFunctions::authTokenSave($serverResponse);

        return empty($siteResponse['error']);
    }
}
