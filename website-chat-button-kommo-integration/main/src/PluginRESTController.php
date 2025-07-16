<?php

namespace WebsiteChatButtonKommoIntegration;

if (!defined('ABSPATH')) {
    exit();
}

use KommoFlashFunctions;
use WebsiteChatButtonKommoIntegration\Enum\KommoWCBOptionsEnum;
use WebsiteChatButtonKommoIntegration\UseCase\ExchangeAndSaveTokenUseCase;
use WebsiteChatButtonKommoIntegration\UseCase\SaveSecretsUseCase;
use WebsiteChatButtonKommoIntegration\Validator\StringValidator;
use WP_Http;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class PluginRESTController extends WP_REST_Controller
{
    public const API_NAMESPACE = KOMMOFLASH_PLUGIN_PAGE_ID . '/v';
    public const API_VERSION = '1';

    public function register_routes()
    {
        register_rest_route(
            PluginRESTController::API_NAMESPACE . PluginRESTController::API_VERSION,
            '/secrets',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [PluginRESTController::class, 'secrets'],
                'permission_callback' => [self::class, 'permissionCallback'],
                'show_in_index' => false,
                'args' => [
                    'client_id' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isUuid4'],
                    ],
                    'client_secret' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isNotEmptyString'],
                    ],
                    'state' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isNotEmptyString'],
                    ],
                ],
            ],
        );
        register_rest_route(
            PluginRESTController::API_NAMESPACE . PluginRESTController::API_VERSION,
            '/redirect',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [PluginRESTController::class, 'redirect'],
                'permission_callback' => [self::class, 'permissionCallback'],
                'show_in_index' => false,
                'args' => [
                    'code' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isNotEmptyString'],
                    ],
                    'state' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isNotEmptyString'],
                    ],
                    'referer' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isNotEmptyString'],
                    ],
                    'platform' => [
                        'required' => true,
                        'type' => 'number',
                        'enum' => ['2', 2],
                    ],
                    'client_id' => [
                        'required' => true,
                        'validate_callback' => [StringValidator::class, 'isUuid4'],
                    ],
                ],
            ],
        );
    }

    public static function secrets(WP_REST_Request $request): WP_REST_Response
    {
        $jsonParams = $request->get_json_params();
        $state = esc_sql(sanitize_text_field((string)($jsonParams['state'] ?? '')));
        $stateFromDb = urldecode(KommoFlashFunctions::getOptionValue(KommoWCBOptionsEnum::BUTTON_STATE));

        if (!in_array(
            $state,
            [$stateFromDb . '_sign-in', $stateFromDb . '_sign-up'],
            true
        )) {
            return self::makeInvalidWPRESTResponse('state');
        }

        $clientId = esc_sql(sanitize_text_field($jsonParams['client_id']));
        $clientSecret = esc_sql(sanitize_text_field($jsonParams['client_secret']));

        $useCase = new SaveSecretsUseCase();
        $useCase->handle($clientId, $clientSecret);

        return new WP_REST_Response(['status' => 'ok'], WP_Http::OK);
    }

    public static function redirect(WP_REST_Request $request): WP_REST_Response
    {
        $getParams = $request->get_params();

        $state = esc_sql(sanitize_text_field((string)($getParams['state'] ?? '')));
        $stateFromDb = urldecode(KommoFlashFunctions::getOptionValue(KommoWCBOptionsEnum::BUTTON_STATE));

        if (!in_array(
            $state,
            [$stateFromDb . '_sign-in', $stateFromDb . '_sign-up'],
            true
        )) {
            return self::makeInvalidWPRESTResponse('state');
        }

        $referer = esc_sql(sanitize_text_field((string)($getParams['referer'] ?? '')));

        if (!preg_match('/[a-zA-Z0-9\-_]*\.kommo\.com/', $referer)) {
            return self::makeInvalidWPRESTResponse('referer');
        }

        $clientId = esc_sql(sanitize_text_field((string)($getParams['client_id'] ?? '')));
        $clientIdFromDb = (string)(KommoFlashFunctions::authSecretGet()['client_id'] ?? '');

        if ($clientId !== $clientIdFromDb) {
            return self::makeInvalidWPRESTResponse('client_id');
        }

        $code = esc_sql(sanitize_text_field($getParams['code']));
        $useCase = new ExchangeAndSaveTokenUseCase();
        $useCaseIsSuccess = $useCase->handle($referer, $state, $code);

        if ($useCaseIsSuccess) {
            wp_safe_redirect(admin_url('admin.php?page=' . KOMMOFLASH_PLUGIN_PAGE_ID));
        } else {
            wp_safe_redirect(admin_url('admin.php?page=' . KOMMOFLASH_PLUGIN_PAGE_ID . '&auth_error=save-tokens'));
        }

        exit();
    }

    /**
     * @return bool
     */
    public static function permissionCallback(): bool
    {
        return true;
    }

    /**
     * @param $invalidParam
     * @return WP_REST_Response
     */
    private static function makeInvalidWPRESTResponse($invalidParam): WP_REST_Response
    {
        return new WP_REST_Response([
            'code' => 'rest_invalid_param',
            'message' => sprintf(
                /* translators: %s is replaced by the name of the invalid parameter */
                __('Invalid parameter(s): %s', 'website-chat-button-kommo-integration'),
                $invalidParam
            ),
            'data' => [
                'status' => WP_Http::BAD_REQUEST,
                'params' => [
                    'state' => __('Invalid parameter.', 'website-chat-button-kommo-integration'),
                ],
            ],
        ], WP_Http::BAD_REQUEST);
    }
}
