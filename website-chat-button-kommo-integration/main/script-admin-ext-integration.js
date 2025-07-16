let kommoErrorAuthFrontendExist = false;
window.onload = function () {
    var oauth_scripts = document.querySelectorAll('[data-js-selector="kommo_oauth"]');

    window.addEventListener('message', kommo_receiveOAuthMessage, false);
    window.addEventListener('message', kommo_receiveNewLocation, false);

    oauth_scripts.forEach(function (oauth_script) {
        let client_id = oauth_script.dataset.clientId,
            state = oauth_script.dataset.state || Math.random().toString(36).substring(2),
            compact = oauth_script.dataset.compact === 'true' || false,
            title = oauth_script.dataset.title || 'Log-in with Kommo',
            mode = oauth_script.dataset.mode || 'popup',
            name = oauth_script.dataset.name || null,
            description = oauth_script.dataset.description || null,
            logo = oauth_script.dataset.logo || null,
            redirect_uri = oauth_script.dataset.redirect_uri || null,
            secrets_uri = oauth_script.dataset.secrets_uri || null,
            scopes = oauth_script.dataset.scopes || null,
            origin = window.location.href || null,
            className = oauth_script.dataset.className || 'amocrm-oauth';

        if ((!client_id || !oauth_script) && !(name && description && redirect_uri && secrets_uri && scopes)) {
            console.error('No client_id or client_secret or script tag or metadata');
            return;
        }

        const button = document.createElement('div');
        const button_html = [
            '<div class="kommo-sign-script">',
            '</div>',
        ];

        if (!compact) {
            button_html.push([
                '<span class="kommo-sign-script-text">' + title + '</span>'
            ])
        }

        button.className = className;
        button.innerHTML = button_html.join('');

        oauth_script.parentNode.insertBefore(button, oauth_script);

        button.onclick = function () {
            let url_array = [];
            try {
                let signCallback = eval(oauth_script.dataset.signCallback);
                if (typeof signCallback === 'function') {
                    url_array = signCallback({
                        state:      state,
                        mode:       mode,
                        origin:     origin,
                        name:       name,
                        description:      description,
                        redirect_uri:     redirect_uri,
                        secrets_uri:      secrets_uri,
                        logo:       logo,
                    });
                }
            } catch (e) {
                // noop
            }

            centerAuthWindow(
                 url_array.join(''),
                '' + oauth_script.dataset.textModaltitle
            );
        };

        var centerAuthWindow = function (url, title) {
            var w = 750;
            var h = 580;
            var dual_screen_left = window.screenLeft !== undefined ? window.screenLeft : screen.left;
            var dual_screen_top = window.screenTop !== undefined ? window.screenTop : screen.top;

            var width = window.innerWidth
                ? window.innerWidth
                : (document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width);

            var height = window.innerHeight
                ? window.innerHeight
                : (document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height);

            var left = ((width / 2) - (w / 2)) + dual_screen_left;
            var top = ((height / 2) - (h / 2)) + dual_screen_top;

            var new_window = window.open(url, title, 'scrollbars, status, resizable, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

            if (window.focus) {
                new_window.focus();
            }
        };

    });
};

function kommo_receiveOAuthMessage(event)
{
    kommoErrorAuthFrontendExist = false;

    const urlParameters = new URLSearchParams(window.location.search);
    if(urlParameters.has('test_auth_frontend_error')) {
        event.data.error = urlParameters.get('test_auth_frontend_error');
    }

    let signActionCurrent = 'sign-in';
    try {
        const urlSplit = event.data.url.split('_');
        signActionCurrent = urlSplit[urlSplit.length - 1];
    } catch (e) {

    }
        signActionCurrent = signActionCurrent === 'sign-up' ? signActionCurrent : 'sign-in'

    var oauth_scripts = document.querySelectorAll('[data-js-selector="kommo_oauth"]');

    oauth_scripts.forEach(function (oauth_script) {
        if(signActionCurrent !== oauth_script.dataset.signAction) {
            return;
        }

        if (oauth_script.dataset.errorCallback && event.data.hasOwnProperty('error')) {
            if(event.data.hasOwnProperty('client_id')) {
                if (event.data.client_id == 0) {
                    return;
                }
            }

            kommoErrorAuthFrontendExist = true;

            let errorStringify = 'Unknown error'
            try {
                errorStringify = JSON.stringify(event.data.error);
            } catch (e) {

            }
                event.data.error = errorStringify;
            try {
                let errorCallback = eval(oauth_script.dataset.errorCallback);
                if (typeof errorCallback === 'function') {
                    errorCallback(event.data);
                }
            } catch (e) {
                kommo_oauth_callback_error_sign_in({error: e});
            }
        }
    });
}

function kommo_receiveNewLocation(event) {
    let windowType = 'window';
    let locationRedirect = event.data.url;

    try {
        if (targetWindowType === 'iframe') {
            windowType = 'iframe'
        }
    } catch (e) {

    }

    if (windowType === 'iframe') {
        locationRedirect = event.currentTarget.location.href;
    }

    if (locationRedirect && !kommoErrorAuthFrontendExist) {
        kommo_oauth_callback_return(event);
        window.location = locationRedirect;
    }
}

