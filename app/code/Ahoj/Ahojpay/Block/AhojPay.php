<?php

namespace Ahoj\Ahojpay\Block;

require_once(BP."/UniModul/ahoj-pay.php");

use Ahoj\Ahojpay\Block\EshopData;
use Ahoj\Ahojpay\Block\Data;
use Magento\Framework\App\ResourceConnection;

/* metody AhojPay api */
class AhojPay extends \Magento\Framework\View\Element\Template
{
    protected $_registry;

    /**
     * @var \Ahoj\Ahojpay\Block\EshopData
     */
    protected $_eshopData;

    /**
     * @var \Ahoj\Ahojpay\Helper\Data
     */
    protected $_ahojHelper;

    /**
     * @var \Ahoj\Ahojpay\Block\Data
     */
    protected $_dataBlock;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Ahoj\Ahojpay\Block\EshopData $eshopData,
                                \Ahoj\Ahojpay\Block\Data $dataBlock,
                                \Ahoj\Ahojpay\Helper\Data $ahojHelper,
                                \Magento\Framework\Registry $registry
    )
    {
        parent::__construct($context);
        $this->_eshopData = $eshopData;
        $this->_ahojHelper = $ahojHelper;
        $this->_registry = $registry;
        $this->_dataBlock = $dataBlock;
    }

    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /* inicializacia eshopu s ahoj */

    public function getPromotionInfo(){
        try {
            $ahojpay = $this->connection();
            $promotionInfo = $ahojpay->getPromotionInfo();
            return $promotionInfo;
        }  catch (Exception $e) {
            // Error handling
        }
    }

    /* zakladne metody z ahoj api */

    public function connection(){
        $ahojpay = new \Ahoj\AhojPay(array(
            "mode" => $this->_ahojHelper->getMode(),
            "businessPlace" => $this->_ahojHelper->getBusinessPlace(),
            "eshopKey" => $this->_ahojHelper->getActivationKey(),
            "notificationCallbackUrl" => "" . $this->_eshopData->getStoreUrl() ."notifications/ahojpay/",
        ));

        return $ahojpay;
    }

    public function productBannerDetailPage($price) {
        try {
            $ahojpay = $this->connection();
            echo $ahojpay->generateInitJavaScriptHtml();
            echo $ahojpay->generateProductBannerHtml($price);
        } catch (Exception $e) {
            // Error handling
        }
    }

    public function getPromotion($applicationParams){
        $ahojpay = $this->connection();
        $applicationResult = $ahojpay->createApplication($applicationParams);
        $applicationUrl = $applicationResult['applicationUrl'];
    }

    public function paymentDescription($price){
        try {
            $ahojpay = $this->connection();
            echo $ahojpay->generateInitJavaScriptHtml();
            echo $ahojpay->generatePaymentMethodDescriptionHtml($price);
        } catch (Exception $e) { // Error handling
        }
    }

    /* stav ziadosti pre developera - nikde nevyuzivane */
    public function getSt(){
        $ahojpay = $this->connection();
        $applicationState = $ahojpay->getApplicationState("2108000164");
        return $applicationState;
    }

    /* stav ziadosti a zmena stavu objednavky na zaklade stavu z ahoj splatky */
    public function getState(){
        try {
            $ahojpay = $this->connection();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orders = $this->_dataBlock->selectOrders();

            foreach($orders as $row){
                    $contractNumber = $row['callback_url'];
                    $orderId = $row['order_id'];
                    $applicationState = $ahojpay->getApplicationState($contractNumber);
                    $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
                    if ($applicationState == "DRAFT") {
                    } elseif ($applicationState == "SIGNED") {
                        /* podpisana ziadost v ahoj */
                        $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
                        $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $order->save();
                        $this->_dataBlock->deleteOrder($orderId);
                    } elseif ($applicationState == "CANCELLED" || $applicationState == "REJECTED" || $applicationState == "DELETED") {
                        /* podpisana ziadost v ahoj */
                        $orderState = \Magento\Sales\Model\Order::STATE_CANCELED;
                        $order->setState($orderState)->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                        $order->save();
                        $this->_dataBlock->deleteOrder($orderId);
                    } else {
                        /* ak je iny stav, nic sa neudeje */
                    }
                }
        } catch (Exception $e) {
        }
    }

    /* zobrazenie popisu platobnej metody Ahoj */
    public function isAvailable($totalPrice){
        $ahojpay = $this->connection();
        $isPaymentMethodAvailable = $ahojpay->isAvailableForTotalPrice($totalPrice);
        return $isPaymentMethodAvailable;
    }

    /* vytvorenie url pre ahoj api vzhladom na obsah objednavky */
    public function createApplication2($applicationParams){
        $ahojpay = $this->connection();

        $applicationResult = $ahojpay->createApplication($applicationParams);
        $applicationUrl = $applicationResult['applicationUrl'];

        $explode_applicationUrl = explode("?", $applicationUrl);
        $callbackUrl = str_replace("https://eshop.pilot.ahojsplatky.sk", $this->_eshopData->getStoreUrl(), $explode_applicationUrl[0]);
        $contract_number = explode("dp/ziadost/", $callbackUrl);
        $this->_dataBlock->insertOrder(intval($applicationParams['orderNumber']), $contract_number[1], "DRAFT");
        return $applicationUrl;
    }

    public function getMyFilePath()
    {
        $fileId = 'Ahoj_AhojPay::images/ahoj.svg';
        $params = [
            'area' => 'frontend'
        ];
        $asset = $this->assetRepository->createAsset($fileId, $params);
        try {
            return $asset->getSourceFile();
        } catch (\Exception $e) {
            return null;
        }
    }

}
