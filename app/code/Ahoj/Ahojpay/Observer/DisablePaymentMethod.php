<?php

namespace Ahoj\Ahojpay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Ahoj\Ahojpay\Block\AhojPay;

class DisablePaymentMethod implements ObserverInterface
{

    /**
     * @var \Ahoj\Ahojpay\Block\AhojPay
     */
    protected $_ahojpay;

    public function __construct(\Psr\Log\LoggerInterface $logger, \Ahoj\Ahojpay\Block\AhojPay $ahojPay)
    {
        $this->_logger = $logger;
        $this->_ahojpay = $ahojPay;
    }

    /* skrytie metody, ak neplatia podmienky pre zobrazenie platby */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        $grandTotal = $cart->getQuote()->getGrandTotal();
        if($this->_ahojpay->getPromotionInfo() && $this->_ahojpay->isAvailable($grandTotal)) {
            if($observer->getEvent()->getMethodInstance()->getCode() == "ahojpay")
            {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', true);
            }
        } else {
            if($observer->getEvent()->getMethodInstance()->getCode() == "ahojpay")
            {
                $checkResult = $observer->getEvent()->getResult();
                $checkResult->setData('is_available', false);
            }
        }
    }

}
