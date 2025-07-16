function json(url) {
  return fetch(url).then((res) => res.json());
}

const WOO_CONFIG = {
  COOKIE_EXPIRATION_DAYS: 365,
  WOO_CART_HASH: "woocommerce_cart_hash",
  AMO_LIVECHAT_ID: "amo-livechat-id",
  INTEGRATION_BASE_URL: "https://woocom.kommo.com",
};

function isWooCom() {
  return (
    window.wcSettings !== undefined ||
    window.wc_checkout_params !== undefined ||
    window.wc_order_attribution !== undefined ||
    window.wc_add_to_cart_params !== undefined ||
    window.woocommerce_params !== undefined
  );
}

function getLiveChatId() {
  const userId = Cookies.get(WOO_CONFIG.AMO_LIVECHAT_ID);

  if (userId) {
    return userId;
  }

  const newUserId = crypto.randomUUID();

  Cookies.set(WOO_CONFIG.AMO_LIVECHAT_ID, newUserId, {
    expires: WOO_CONFIG.COOKIE_EXPIRATION_DAYS,
  });

  return newUserId;
}

/**
 * @param {String} userId
 * @param {String|null} cartHash
 */
function setMetaFromWooCom(userId, cartHash) {
  if (window?.wcSettings?.checkoutData?.order_key !== undefined) {
    // Add postfix to cart hash.
    cartHash += `_${wcSettings.checkoutData.order_key}`;

    const data = {
      cart_hash: cartHash,
      user_id: userId,
    };

    jQuery.ajax(`${WOO_CONFIG.INTEGRATION_BASE_URL}/woo/livechat-link`, {
      method: "POST",
      data,
      dataType: "json",
    });
  }

  crm_plugin.setMeta({
    contact: {
      custom_fields: [
        {
          code: "WOO_CART_HASH",
          values: [
            {
              value: cartHash,
            },
          ],
        },
        {
          code: "WOO_USER_ID",
          values: [
            {
              value: userId,
            },
          ],
        },
      ],
    },
  });
}

jQuery(document).ready(function () {
  switch (true) {
    case isWooCom():
      const cartHash = Cookies.get(WOO_CONFIG.WOO_CART_HASH);
      const userId = getLiveChatId();

      setMetaFromWooCom(userId, cartHash);
      break;
  }
});
