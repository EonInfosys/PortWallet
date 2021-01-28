/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'portwallet',
            component: 'EonInfosys_PortWallet/js/view/payment/method-renderer/portwallet'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
