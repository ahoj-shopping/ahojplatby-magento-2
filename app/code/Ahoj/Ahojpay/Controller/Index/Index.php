<?php

namespace Ahoj\Ahojpay\Controller\Index;

//require_once(BP. "/UniModul/ahoj-pay.php");

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Framework\Controller\Result\Redirect;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $uniModulName = "AhojPay";

    protected $_checkoutSession;

    protected $_orderFactory;


    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;

    }

    /* action po observeri - ziskanie url pre ahoj api pri presmerovani */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderDatamodel = $objectManager->get('Magento\Sales\Model\Order')->getCollection()->getLastItem();
        $orderId = $orderDatamodel->getId();
        $order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderId);
        $response['action'] = $order['ahojpay'];
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }

    /* posledne id objednavky */
    public function getRealOrderId()
    {
        $lastorderId = $this->_checkoutSession->getLastOrderId();
        return $lastorderId;
    }

    /* data o objednavke na zaklade posledneho id*/
    public function getOrder()
    {
        if ($this->_checkoutSession->getLastRealOrderId()) {
            $order = $this->_orderFactory->create()->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
            return $order;
        }
        return false;
    }
}
