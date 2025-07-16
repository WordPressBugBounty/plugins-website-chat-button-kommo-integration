jQuery(document).ready(function () {
    const functions = {
        confirmDeactivate: function () {
            if(window.location.href.indexOf('wp-admin/plugins.php') === -1) {
                return;
            }

            let link    = null;
            let page_id = null;

            const pluginListLinks = jQuery('#the-list a');

            try {
                page_id = window.kommo_admin_data.plugin_deactivate_confirm.page_id;
            } catch (e) {

            }

            if (!page_id) {
                return;
            }
            if (pluginListLinks.length === 0) {
                return;
            }

            pluginListLinks.each(function(el, item) {
                try {
                    if(jQuery(item).attr('href').indexOf('deactivate&plugin=' + page_id) !== -1)
                    {
                        link = jQuery(item);
                    }
                } catch (e) {

                }
            })

            if (!link) {
                return;
            }

            link.click(function (e) {
                e.preventDefault();

                let textConfirm = 'Are you sure you want to deactivate plugin?'
                try {
                    textConfirm = window.kommo_admin_data.plugin_deactivate_confirm.lang;
                } catch (e) {

                }

                const isDeactivate = confirm(textConfirm);
                if(!isDeactivate) {
                    return;
                }

                const href = jQuery(e.target).attr('href');
                window.location.href = href;
            })
        },
        inboxGetCount: function () {
            try {
                if (!window.kommo_admin_data.plugin_is_active || !window.kommo_admin_data.plugin_is_signed) {
                    return;
                }
            } catch (e) {
                return;
            }

            let page_id = null;
            let KOMMOFLASH_INBOX_LIMIT_MESSAGE = 99;
            try {
                page_id                   = window.kommo_admin_data.plugin_deactivate_confirm.page_id;
                KOMMOFLASH_INBOX_LIMIT_MESSAGE = window.kommo_admin_data.plugin_inbox_counter.count_limit;
            } catch (e) {

            }

            if (!page_id) {
                return;
            }
            if (window.location.href.indexOf(page_id) !== -1) {  // if not plugin pages
                return;
            }

            const ACTION = 'inbox_get_count';
            const data = {
                'action': 'kommo_dashboard_action',
                'inbox_get_count': true,
                'nonce': kommo_admin_data.nonce,
            };
            const dataErrorLog = {
                action: 'kommo_dashboard_action',
                dashboard_main_menu_inbox_counter_error_log: true,
                'nonce': kommo_admin_data.nonce,
                data: {
                    action: ACTION,
                    error: null,
                }
            };
            jQuery.ajax({
                url: ajaxurl + '',
                method:   'post',
                dataType: 'json',
                data: data,
                success: function(response) {
                    try {
                        let action        = response.data.action;
                        let count         = response.data.count;
                        const count_inbox = count > KOMMOFLASH_INBOX_LIMIT_MESSAGE ? KOMMOFLASH_INBOX_LIMIT_MESSAGE : count;

                        if (action === 'have-unread-messages') {
                            jQuery(`.toplevel_page_${page_id} > .wp-menu-name`)
                                .append(` <span class="menu-counter site-health-counter count-3"><span class="count">${count_inbox}</span></span>`);
                        }

                    } catch (e) {
                        dataErrorLog.data.error = e;
                        jQuery.post(ajaxurl, dataErrorLog, function(response) {

                        });
                    }
                },
                error: function(data){
                    dataErrorLog.data.error = data;
                    jQuery.post(ajaxurl, dataErrorLog, function(response) {

                    });
                }
            });
        }
    };

    functions.confirmDeactivate();
    functions.inboxGetCount();
});
