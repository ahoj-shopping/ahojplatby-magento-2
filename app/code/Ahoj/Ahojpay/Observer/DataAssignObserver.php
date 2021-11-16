<?php

namespace Ahoj\Ahojpay\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Psr\Log\LoggerInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{
    protected $rozlozto;

    protected $eshop;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    public function __construct(
        \Ahoj\Ahojpay\Block\Rozlozto $rozlozto,
        \Ahoj\Ahojpay\Block\EshopData $eshop,
        LoggerInterface $log,
        DataObjectFactory $dataObjectFactory,
        ScopeConfigInterface $config
    ) {
        $this->rozlozto = $rozlozto;
        $this->eshop = $eshop;
        $this->log = $log;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);

        if (false === strpos($method->getCode(), 'rozlozto')) {
            return;
        }

        $data = $this->readDataArgument($observer);
        $paymentInfo = $this->readPaymentModelArgument($observer);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $grandTotal = $cart->getQuote()->getGrandTotal();
        $calculation = $this->rozlozto->getCalculations($grandTotal);
        $paymentInfo->setAdditionalInformation('calculation', $calculation);
    }
}
