<?php

namespace WebsiteChatButtonKommoIntegration\UseCase;

use WebsiteChatButtonKommoIntegration\Enum\KommoWCBOptionsEnum;
use WebsiteChatButtonKommoIntegration\Repository\KommoWCBOptionRepository;

if (!defined('ABSPATH')) {
    exit();
}

class SaveSecretsUseCase
{
    public function handle(string $clientId, string $clientSecret): bool
    {
        $data = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        return KommoWCBOptionRepository::update(
            KommoWCBOptionsEnum::ACCOUNT_SIGN_SECRETS,
            wp_json_encode($data) ?: ''
        );
    }
}
