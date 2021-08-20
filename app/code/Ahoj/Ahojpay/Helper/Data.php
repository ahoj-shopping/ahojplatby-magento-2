<?php

namespace Ahoj\Ahojpay\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->_registry = $registry;
    }

    /* ziskanie dat z admina */

    public function getMode(){
        if($this->getAhojpayConfig('payment/ahojpay/isTest') == 1){
            return "test";
        }
        return "prod";
    }

    public function getAhojpayConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getActivationKey(){
        return $this->getAhojpayConfig('payment/ahojpay/activationKey');
    }

    public function getBusinessPlace(){
        return $this->getAhojpayConfig('payment/ahojpay/businessPlace');
    }



}
