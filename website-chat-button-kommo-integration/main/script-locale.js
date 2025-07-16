function kommoGetBrowserLocale() {
    let locale = 'en';
    const lang = navigator.language;

    if(lang.length === 2) {
        if(lang.indexOf('es') !== -1) {
            locale = 'es';
        }
        if(lang.indexOf('pt') !== -1) {
            locale = 'pt';
        }
        if(lang.indexOf('ru') !== -1) {
            locale = 'ru';
        }
    }
    if(lang.length > 2) {
        if(lang.indexOf('es-') !== -1) {
            locale = 'es';
        }
        if(lang.indexOf('pt-') !== -1) {
            locale = 'pt';
        }
        if(lang.indexOf('ru-') !== -1) {
            locale = 'ru';
        }
    }

    return locale;
}
    window.KOMMOFLASH_BROWSER_LOCALE = 'en';
try {
    window.KOMMOFLASH_BROWSER_LOCALE = kommoGetBrowserLocale();
} catch (e) {
}
