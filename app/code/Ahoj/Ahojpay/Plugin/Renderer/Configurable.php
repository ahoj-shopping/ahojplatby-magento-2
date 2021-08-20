<?php
namespace Ahoj\Ahojpay\Plugin\Renderer;

use \Ahoj\AhojPay\Block\AhojPay;

class Configurable
{
    protected $ahojPay;

    public function __construct(
        \Ahoj\Ahojpay\Block\AhojPay $ahojPay
    ){
        $this->ahojPay = $ahojPay;
    }

    /* zobrazit banner per variant na zaklade ceny */
    public function afterGetJsonConfig(\Magento\Swatches\Block\Product\Renderer\Configurable $subject, $result) {

        $jsonResult = json_decode($result, true);
        $jsonResult['skus'] = []; $jsonResult['prices'] = [];

        foreach ($subject->getAllowProducts() as $simpleProduct) {
            $jsonResult['skus'][$simpleProduct->getId()] = $simpleProduct->getSku();
            $jsonResult['prices'][$simpleProduct->getId()] = $simpleProduct->getPrice();
        }
        $result = json_encode($jsonResult);
        return $result;
    }
}
