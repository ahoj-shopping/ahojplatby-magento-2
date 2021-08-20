<?php

namespace Ahoj\Ahojpay\Model;

class CustomConfigProvider extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $ahojpay;

    protected $eshop;

    public function __construct(
        \Ahoj\Ahojpay\Block\AhojPay $ahojpay,
        \Ahoj\Ahojpay\Block\EshopData $eshop

    ) {
        $this->ahojpay = $ahojpay;
        $this->eshop = $eshop;
    }

    /* prenos promotion info hodnotu z ahoj api do checkoutu*/
    public function getConfig() {
        $config = [];
        $config['promotioninfo'] = $this->ahojpay->getPromotionInfo();
        $config['storeUrl'] = $this->eshop->getStoreUrl();
        return $config;
    }

}
