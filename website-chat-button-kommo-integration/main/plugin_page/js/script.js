let ajaxGetUrlTemplate = ajaxurl;
let ajaxPostUrlTemplate = ajaxurl;
try {
    ajaxGetUrlTemplate = kommo_admin_data.hasOwnProperty('ajax_url_get') ? kommo_admin_data.ajax_url_get : ajaxurl;
    ajaxPostUrlTemplate = kommo_admin_data.hasOwnProperty('ajax_url_post') ? kommo_admin_data.ajax_url_post : ajaxurl;
} catch (e) {

}

function kommo_oauth_callback_return() {
    window.KOMMO_FLASH.modals.preloader();
}

function kommo_oauth_callback_error(eventData, action) {
    const data = {
        action: 'kommo_dashboard_action',
        auth_error_frontend_log: true,
        'nonce': kommo_admin_data.nonce,
        data: {
            action: action,
            error: eventData.error,
        }
    };
    jQuery.post(ajaxPostUrlTemplate, data, function (response) {

    });
}

function kommo_oauth_callback_error_sign_in(eventData) {
    window.KOMMO_FLASH.modals.error.login.show();
    kommo_oauth_callback_error(eventData, 'sign-in');

}

function kommo_oauth_callback_error_sign_up(eventData) {
    window.KOMMO_FLASH.modals.error.registration.show();
    kommo_oauth_callback_error(eventData, 'sign-up');
}

function kommo_oauth_callback_get_url_sign_in(url) {
    return [
        kommoflash_plugin_page_data.constants.kommo_url, '/oauth/',
        '?state=', url.state,
        '&mode=', url.mode,
        '&origin=', url.origin,
        '&name=', url.name,
        '&description=', url.description,
        '&redirect_uri=', url.redirect_uri,
        '&secrets_uri=', url.secrets_uri,
        '&logo=', url.logo,
        '&scopes[]=', 'crm',
        '&scopes[]=', 'notifications',
    ];
}

function kommo_oauth_callback_get_url_sign_up(url) {
    return [
        kommoflash_plugin_page_data.constants.kommo_url, '/oauth/',
        '?sign_up=', 'yes',
        '&state=', url.state,
        '&mode=', url.mode,
        '&origin=', url.origin,
        '&name=', url.name,
        '&description=', url.description,
        '&redirect_uri=', url.redirect_uri,
        '&secrets_uri=', url.secrets_uri,
        '&logo=', url.logo,
        '&scopes[]=', 'crm',
        '&scopes[]=', 'notifications',
    ];
}

window.KOMMO_FLASH = {
    modals: {
        preloader: function () {
            jQuery("body").css('overflow-y', 'hidden');
            jQuery("html, body").scrollTop(0);
            jQuery(".active").removeClass("active");
            jQuery(".overlay").addClass("active");
            jQuery(".preloader-redirect").addClass("active");
        },
        error: {
            registration: {
                show: function () {
                    jQuery("body").css('overflow-y', 'hidden');
                    jQuery("html, body").scrollTop(0);
                    jQuery(".active").removeClass("active");
                    jQuery(".overlay").addClass("active");
                    jQuery(".error-registration").addClass("active");
                }
            },
            login: {
                show: function () {
                    jQuery("body").css('overflow-y', 'hidden');
                    jQuery("html, body").scrollTop(0);
                    jQuery(".active").removeClass("active");
                    jQuery(".overlay").addClass("active");
                    jQuery(".error-login").addClass("active");
                }
            },
            trial_is_over:{
                show: function(){
                    jQuery("body").css('overflow-y','hidden');
                    jQuery("html, body").scrollTop(0);
                    jQuery(".active").removeClass("active");
                    jQuery(".overlay").addClass("active");
                    jQuery(".trial-is-over").addClass("active");
                }
            },
            close: function () {
                jQuery(".active").removeClass("active");
                jQuery("body").css('overflow-y', 'unset');
            }
        }
    }
}

jQuery(document).ready(function () {
    jQuery(".overlay #close-overlay").click(function () {
        jQuery(".active").removeClass("active");
        jQuery("body").css('overflow-y', 'unset');
    });

    jQuery(".overlay").click(function (e) {
        if (jQuery(e.target).hasClass("overlay")) {
            if (!jQuery('.preloader-redirect.active').length) {
                jQuery(".active").removeClass("active");
                jQuery("body").css('overflow-y', 'unset');
            }
        }
    });

    const urlParameters = new URLSearchParams(window.location.search);
    if (urlParameters.has('auth_error')) {
        if (urlParameters.has('account_sign_type')) {
            if (urlParameters.get('account_sign_type') === 'sign-up') {
                window.KOMMO_FLASH.modals.error.registration.show();
            } else {
                window.KOMMO_FLASH.modals.error.login.show();
            }
        } else {
            window.KOMMO_FLASH.modals.error.login.show();
        }
    }

    const trialExpired = parseInt(kommoflash_plugin_page_data.account.is_trial_expired);
    const signInit = parseInt(kommoflash_plugin_page_data.account.account_sign_init);

    if (trialExpired && signInit) {
        window.KOMMO_FLASH.modals.error.trial_is_over.show();
    }
});
