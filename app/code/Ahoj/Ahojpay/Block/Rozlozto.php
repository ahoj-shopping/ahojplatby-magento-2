<?php

namespace Ahoj\Ahojpay\Block;

require_once(BP . "/UniModul/ahoj-pay.php");

use Ahoj\Ahojpay\Block\EshopData;
use Ahoj\Ahojpay\Block\Data;
use Ahoj\ApiErrorException;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;

/* metody AhojPay api */

class Rozlozto extends Template
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

    /**
     * Kód pre platbu "Ahoj platby „o 30 dní bez navýšenia"
     */
    const PROMOTION_CODE_ODLOZTO = 'DP_DEFER_IT';

    /**
     * Kód pre platbu "v 3 platbách bez navýšenia"
     */
    const PROMOTION_CODE_ROZLOZTO = 'SP_SPLIT_IT';

    public function __construct(Context                       $context,
                                \Ahoj\Ahojpay\Block\EshopData $eshopData,
                                \Ahoj\Ahojpay\Block\Data      $dataBlock,
                                \Ahoj\Ahojpay\Helper\Data     $ahojHelper,
                                Registry                      $registry
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

    /**
     * Inicializacia eshopu s ahoj
     *
     * @return array|void|null
     * @throws ApiErrorException
     */
    public function getPromotionInfo()
    {
        try {
            $ahojpay = $this->connection();
            $promotionInfo = $ahojpay->getPromotionInfo();
            return $promotionInfo;
        } catch (Exception $e) {
            // Error handling
        }
    }

    public function getPaymentMethods($grandTotal)
    {
        try {
            $ahojpay = $this->connection();
            $paymentMethods = $ahojpay->getPaymentMethods($grandTotal);
            return $paymentMethods;
        } catch (ApiErrorException $e) {
            print_r($e->getMessage()); // Error handling
        }
    }

    public function getCalculations($totalPrice)
    {
        try {
            $ahojpay = $this->connection();
            $calculatioInfo = $ahojpay->getCalculations(isset($totalPrice) ? $totalPrice : 0);
            return $calculatioInfo;
        } catch (ApiErrorException $e) {
            print_r($e->getMessage());  // Error handling
            print_r($e->getTrace());    // Error handling
        }
    }

    /* zakladne metody z ahoj api */

    public function connection()
    {
        $ahojpay = new \Ahoj\AhojPay(array(
            "mode" => $this->_ahojHelper->getMode(),
            "businessPlace" => $this->_ahojHelper->getBusinessPlace(),
            "eshopKey" => $this->_ahojHelper->getActivationKey(),
            "notificationCallbackUrl" => "" . $this->_eshopData->getStoreUrl() . "notifications/ahojpay/",
        ));

        return $ahojpay;
    }

    public function productBannerDetailPage($price)
    {
        try {
            $ahojpay = $this->connection();
            echo $ahojpay->generateInitJavaScriptHtml();
            echo $ahojpay->generateProductBannerHtml($price);
        } catch (ApiErrorException $e) {
            // Error handling
        }
    }

    public function getPromotion($applicationParams)
    {
        $ahojpay = $this->connection();
        $applicationResult = $ahojpay->createApplication($applicationParams, self::PROMOTION_CODE_ROZLOZTO);
        $applicationUrl = $applicationResult['applicationUrl'];
    }

    public function paymentDescription($price)
    {
        try {
            $ahojpay = $this->connection();
            echo $ahojpay->generateInitJavaScriptHtml();
            echo $ahojpay->generatePaymentMethodDescriptionHtml($price);
        } catch (ApiErrorException $e) { // Error handling
        }
    }

    /* stav ziadosti pre developera - nikde nevyuzivane */
    public function getSt()
    {
        $ahojpay = $this->connection();
        $applicationState = $ahojpay->getApplicationState("2108000164");
        return $applicationState;
    }

    /**
     * Stav ziadosti a zmena stavu objednavky na zaklade stavu z ahoj splatky
     */
    public function getState()
    {
        try {
            $ahojpay = $this->connection();
            $objectManager = ObjectManager::getInstance();
            $orders = $this->_dataBlock->selectOrders();

            foreach ($orders as $row) {
                $contractNumber = $row['callback_url'];
                $orderId = (int)$row['order_id'];
                $applicationState = $ahojpay->getApplicationState($contractNumber);
                $order = $objectManager->create('\Magento\Sales\Model\Order')->loadByIncrementId($orderId);
                if ($applicationState == "DRAFT") {
                    $orderState = $this->_ahojHelper->getDraftStatus();
                    if (empty($orderState)) {
                        $orderState = Order::STATE_PENDING_PAYMENT;
                    }
                    $order->setState($orderState)->setStatus($orderState);
                    $order->save();
                    //$this->_dataBlock->deleteOrder($orderId);
                } elseif ($applicationState == "SIGNED") {
                    /* podpisana ziadost v ahoj */
                    $orderState = $this->_ahojHelper->getSignedStatus();
                    if (empty($orderState)) {
                        $orderState = Order::STATE_PROCESSING;
                    }
                    $order->setState($orderState)->setStatus($orderState);
                    $order->addStatusToHistory($order->getStatus(), 'Ahoj.shopping platba bola schválená, stav objednávky bol automaticky aktualizovaný na: ' . $orderState);
                    $order->save();
                    $this->_dataBlock->deleteOrder($orderId);
                } elseif ($applicationState == "CANCELLED" || $applicationState == "REJECTED" || $applicationState == "DELETED") {
                    /* podpisana ziadost v ahoj */
                    $orderState = $this->_ahojHelper->getCanceledStatus();
                    if (empty($orderState)) {
                        $orderState = Order::STATE_CANCELED;
                    }
                    $order->setState($orderState)->setStatus($orderState);
                    $order->addStatusToHistory($order->getStatus(), 'Ahoj.shopping platba bola zamietnutá, stav objednávky bol automaticky aktualizovaný na: ' . $orderState);
                    $order->save();
                } else {
                    /* ak je iny stav, nic sa neudeje */
                }

                //exit();
            }
        } catch (Exception $e) {
        }
    }

    /* zobrazenie popisu platobnej metody Ahoj */
    public function isAvailable($totalPrice, $promotionInfo = self::PROMOTION_CODE_ROZLOZTO)
    {
        $ahojpay = $this->connection();
        $isPaymentMethodAvailable = $ahojpay->isAvailableForTotalPrice($totalPrice, $promotionInfo);
        return $isPaymentMethodAvailable;
    }

    /* vytvorenie url pre ahoj api vzhladom na obsah objednavky */
    public function createApplication2($applicationParams, $promotion_code = self::PROMOTION_CODE_ROZLOZTO)
    {
        $ahojpay = $this->connection();
        $applicationResult = $ahojpay->createApplication($applicationParams, $promotion_code);
        $applicationUrl = $applicationResult['applicationUrl'];

        if ($promotion_code == 'SP_SPLIT_IT') {
            $payment_type = 'rozlozto';
        } else {
            $payment_type = 'odlozto';
        }

        $explode_applicationUrl = explode("?", $applicationUrl);
        if ($this->_ahojHelper->getMode() == 'prod') {
            $callbackUrl = str_replace("https://eshop.ahojsplatky.sk", $this->_eshopData->getStoreUrl(), $explode_applicationUrl[0]);
        } else {
            $callbackUrl = str_replace("https://eshop.pilot.ahojsplatky.sk", $this->_eshopData->getStoreUrl(), $explode_applicationUrl[0]);
        }

        $contract_number = explode("dp/ziadost/", $callbackUrl);
        $this->_dataBlock->insertOrder((int)$applicationParams['orderNumber'], $contract_number[1], "DRAFT", $payment_type);
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
        } catch (Exception $e) {
            return null;
        }
    }
}
