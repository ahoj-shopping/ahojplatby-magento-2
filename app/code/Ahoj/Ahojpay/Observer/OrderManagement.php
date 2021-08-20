<?php

namespace Ahoj\Ahojpay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Ahoj\Ahojpay\Block\EshopData;
use Ahoj\Ahojpay\Block\AhojPay;
use Magento\Catalog\Model\Product;

class OrderManagement implements ObserverInterface
{

    protected $logger;

    protected $eshopData;

    protected $ahojPay;

    public function __construct(LoggerInterface $logger,
                                \Ahoj\Ahojpay\Block\EshopData $eshopData,
                                \Ahoj\Ahojpay\Block\AhojPay $ahojPay
    )
    {
        $this->logger = $logger;
        $this->eshopData = $eshopData;
        $this->ahojPay = $ahojPay;
    }

    /* spracovanie objednavky do array pre ahoj api a ulozenie do db*/
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $orderItems = $order->getAllItems();

            $goods = [];
            $applicationParamsGoods = [];
            foreach ($orderItems as $item) {
                if($item->getPriceInclTax() != "") {
                    $this->logger->info($item->getPriceInclTax());
                    $applicationParamsGoods = array(
                        "name" => $item->getName(),
                        "price" => $item->getPriceInclTax(), //$item->getPrice() + ($item->getTaxAmount() / $item->getQtyOrdered()),
                        "id" => $item->getSku(),
                        "count" => $item->getQtyOrdered()
                    );
                    $goods[] = $applicationParamsGoods;
                }
            }

            $explode = explode(" " , $order->getShippingAddress()->getStreet()[0]);
            $street = "";
            for($i = 0; $i < sizeof($explode) - 1; $i++){
                $street = $street . " " . $explode[$i];
            }
            $number = $explode[sizeof($explode)-1];

            $explode_billing = explode(" " , $order->getBillingAddress()->getStreet()[0]);
            $street_billing = "";
            for($i = 0; $i < sizeof($explode_billing) - 1; $i++){
                $street_billing = $street_billing . " " . $explode_billing[$i];
            }
            $number_billing = $explode_billing[sizeof($explode_billing)-1];

            $applicationParams = array(
                "orderNumber" => $this->eshopData->getLastOrderId(),
                "completionUrl" => $this->eshopData->getStoreUrl(),
                "terminationUrl" => $this->eshopData->getStoreUrl(),
                "eshopRegisteredCustomer" => false,
                "customer" => array(
                    "firstName" => htmlspecialchars($order->getBillingAddress()->getFirstname()),
                    "lastName" => htmlspecialchars($order->getBillingAddress()->getLastname()),
                    "contactInfo" => array(
                        "email" => $order->getShippingAddress()->getEmail(),
                        "mobile" => $order->getBillingAddress()->getTelephone(),
                    ),
                    "permanentAddress" => array(
                        "street" => htmlspecialchars($street_billing),
                        "registerNumber" => $number_billing,
                        "referenceNumber" => " ",
                        "city" => $order->getBillingAddress()->getCity(),
                        "zipCode" => $order->getBillingAddress()->getPostCode(),
                    ),
                ),
                "product" => array(
                    "goodsDeliveryTypeText" => "local_pickup",
                    "goodsDeliveryAddress" => array(
                        "name" => htmlspecialchars($order->getShippingAddress()->getFirstname() . " " . $order->getShippingAddress()->getLastname()),
                        "street" => htmlspecialchars($street),
                        "registerNumber" => $number,
                        "referenceNumber" => " ",
                        "city" => $order->getShippingAddress()->getCity(),
                        "zipCode" => $order->getShippingAddress()->getPostCode(),
                        "country" => $order->getShippingAddress()->getCountryId(),
                    ),
                    "goods" => $goods,
                    "goodsDeliveryCosts" => (float)$order->getShippingTaxAmount() + (float)$order->getShippingAmount()
                )
            );
            $this->logger->info(print_r($applicationParams, true));
            $url_ahojpay = $this->ahojPay->createApplication2($applicationParams);
            $order->setahojpay($url_ahojpay);
            $this->logger->info($url_ahojpay);
            $order->save();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

}
