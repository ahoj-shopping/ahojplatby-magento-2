define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ],
    function (
        $,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators,
        quote,
        redirectOnSuccessAction,
        url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ahoj_Ahojpay/payment/rozlozto',
                myNumber: '',
                calculationInfo: {}
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'myNumber',
                        'calculationInfo'
                    ]);
                return this;
            },
            getInstructions: function () {
                let totals = quote.getTotals();
                let promotionInfo = checkoutConfig.promotioninfo;
                let calculationInfo = checkoutConfig.calculation;
                ahojpay.init(promotionInfo[1]);
                $.post('/Calculation/Calculation/index', 'json')
                    .done(function (response) {
                        let calculationInfo = response.calculationInfo;
                        ahojpay.paymentMethodDescription(totals, '#product-banner-wrapper-rozlozto', 'SP_SPLIT_IT', calculationInfo[1]);
                    })
            },
            getTitle: function () {
                let paymentMethods = checkoutConfig.ahojPaymentMethods;
                return paymentMethods[1].description;
            },
            getData: function () {
                let data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'calculation': this.calculationInfo
                    }
                };
                return data;
            },
            placeOrder: function (data, event) {
                if (event) {
                    event.preventDefault();
                }
                let self = this,
                    placeOrder,
                    emailValidationResult = customer.isLoggedIn(),
                    loginFormSelector = 'form[data-role=email-with-possible-login]';
                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }
                if (emailValidationResult && this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);
                    $.when(placeOrder).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));
                    return true;
                }

                return false;
            },
            selectPaymentMethod: function () {
                let data = this.getData();
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                let totals = quote.getTotals();

                $.post('/Calculation/Calculation/index', 'json')
                    .done(function (response) {
                        let calculationInfo = response.calculationInfo;
                        ahojpay.paymentMethodDescription(totals, '#product-banner-wrapper-rozlozto', 'SP_SPLIT_IT', calculationInfo[1]);
                    })
                return true;
            },
            afterPlaceOrder: function () {
                let custom_controller_url = url.build('Ahojpay/index/index');

                $.post(custom_controller_url, 'json')
                    .done(function (response) {
                        let urlahoj = response.action;
                        ahojpay.openApplication(urlahoj);
                    })
            },
            controller: function (response) {
                let final_form = form({
                    data: {
                        action: response.action
                    }
                });
                return final_form;
            }

        });
    }
);

