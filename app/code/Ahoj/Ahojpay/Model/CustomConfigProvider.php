<?php

namespace Ahoj\Ahojpay\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CustomConfigProvider implements ConfigProviderInterface
{

    protected $ahojpay;

    protected $rozlozto;

    protected $eshop;

    public function __construct(
        \Ahoj\Ahojpay\Block\AhojPay $ahojpay,
        \Ahoj\Ahojpay\Block\Rozlozto $rozlozto,
        \Ahoj\Ahojpay\Block\EshopData $eshop
    ) {
        $this->ahojpay = $ahojpay;
        $this->rozlozto = $rozlozto;
        $this->eshop = $eshop;
    }

    /**
     * Prenos promotion info hodnotu z ahoj api do checkoutu
     *
     * @return array
     */
    public function getConfig()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $subTotal = $cart->getQuote()->getSubtotal();       // cena objednavky bez dopravy a poplatkov s DPH
        $grandTotal = $cart->getQuote()->getGrandTotal();   // celkova cena objednavky

        $config = [];
        $config['promotioninfo'] = $this->ahojpay->getPromotionInfo();
        $config['storeUrl'] = $this->eshop->getStoreUrl();
        $config['calculation'] = $this->ahojpay->getCalculations($grandTotal);
        $config['ahojPaymentMethods'] = $this->ahojpay->getPaymentMethods($grandTotal);
        return $config;
    }

}
