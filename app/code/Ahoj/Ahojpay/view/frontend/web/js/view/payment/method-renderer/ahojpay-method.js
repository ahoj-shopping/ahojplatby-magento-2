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
				template: 'Ahoj_Ahojpay/payment/ahojpay',
				myNumber: ''
			},
			initObservable: function () {
				this._super()
					.observe([
						'myNumber'
					]);
				return this;
			},
			getInstructions: function(){
                var totals = quote.getTotals();
                var promotionInfo = checkoutConfig.promotioninfo;
                ahojpay.init(checkoutConfig.promotioninfo);
                ahojpay.paymentMethodDescription(totals, '#product-banner-wrapper-ahojplatby', 'DP_DEFER_IT');
			},
            getTitle: function () {
                let paymentMethods = checkoutConfig.ahojPaymentMethods;
                return paymentMethods[0].name;
            },
			placeOrder: function (data, event) {
				if (event) {
					event.preventDefault();
				}
				var self = this,
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
			selectPaymentMethod: function() {
				selectPaymentMethodAction(this.getData());
				checkoutData.setSelectedPaymentMethod(this.item.method);
				return true;
			},
			afterPlaceOrder: function () {
				var custom_controller_url = url.build('Ahojpay/index/index');

				$.post(custom_controller_url, 'json')
					.done(function (response) {
						var urlahoj = response.action;
						ahojpay.openApplication(urlahoj);
					})
			},
			controller : function (response) {
				var final_form = form({
					data: {
						action: response.action
					}
				});
				return final_form;
			}

		});
	}
);

