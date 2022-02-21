<?php

namespace Ahoj\Ahojpay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Ahoj\Ahojpay\Block\AhojPay;
use Ahoj\Ahojpay\Block\Rozlozto;

class DisablePaymentMethod implements ObserverInterface
{

    /**
     * @var AhojPay
     */
    protected $_ahojpay;

    /**
     * @var Rozlozto
     */
    protected $_rozlozto;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param AhojPay $ahojPay
     * @param Rozlozto $rozlozto
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        AhojPay $ahojPay,
        Rozlozto $rozlozto
    )
    {
        $this->_logger = $logger;
        $this->_ahojpay = $ahojPay;
        $this->_rozlozto = $rozlozto;
    }

    /**
     * Skrytie metody, ak neplatia podmienky pre zobrazenie platby
     *
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $subtotalWithDiscount = $cart->getQuote()->getSubtotalWithDiscount();
        $checkResult = $observer->getEvent()->getResult();
        $checkResult->setData('is_available', true);

        //
        if ($this->_ahojpay->getPromotionInfo() && $this->_ahojpay->isAvailable($subtotalWithDiscount)) {
            if ($observer->getEvent()->getMethodInstance()->getCode() == "ahojpay") {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', true);
            }
        } else {
            if ($observer->getEvent()->getMethodInstance()->getCode() == "ahojpay") {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }

        //
        if ($this->_ahojpay->getPromotionInfo() && $this->_rozlozto->isAvailable($subtotalWithDiscount)) {
            if ($observer->getEvent()->getMethodInstance()->getCode() == "rozlozto") {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', true);
            }
        } else {
            if ($observer->getEvent()->getMethodInstance()->getCode() == "rozlozto") {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }
    }
}
