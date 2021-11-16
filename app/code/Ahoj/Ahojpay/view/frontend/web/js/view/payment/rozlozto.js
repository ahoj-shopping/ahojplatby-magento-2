define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'rozlozto',
                component: 'Ahoj_Ahojpay/js/view/payment/method-renderer/rozlozto-method'
            },
        );
        return Component.extend({});
    }
);
