<?php

namespace Ahoj\Ahojpay\Controller\Calculation;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Controller\Result\Redirect;

use Magento\Framework\App\ObjectManager;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $rozlozto;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Ahoj\Ahojpay\Block\Rozlozto $rozlozto
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->rozlozto = $rozlozto;
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        $grandTotal = $cart->getQuote()->getGrandTotal();   // celkova cena objednavky
        $response['calculationInfo'] = $this->rozlozto->getCalculations($grandTotal);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }
}
