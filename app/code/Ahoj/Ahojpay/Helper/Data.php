<?php

namespace Ahoj\Ahojpay\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const USING_AHOJPAY_LOGGER = 'payment/ahojpay/logger';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry           $registry
    )
    {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->_registry = $registry;
    }

    /* ziskanie dat z admina */

    public function getMode()
    {
        if ($this->getAhojpayConfig('payment/ahojpay/isTest') == 1) {
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

    public function getActivationKey()
    {
        return $this->getAhojpayConfig('payment/ahojpay/activationKey');
    }

    public function getBusinessPlace()
    {
        return $this->getAhojpayConfig('payment/ahojpay/businessPlace');
    }

    /**
     * Nastavenie pre Spracováva sa Ahoj platba
     * $applicationState == "DRAFT"
     *
     * @return mixed
     */
    public function getDraftStatus()
    {
        return $this->getAhojpayConfig('payment/ahojpay/order_status_pending');
    }

    /**
     * Nastavenie pre Spracováva sa Ahoj platba
     * $applicationState == "SIGNED"
     *
     * @return mixed
     */
    public function getSignedStatus()
    {
        return $this->getAhojpayConfig('payment/ahojpay/order_status_processing');
    }

    /**
     * Nastavenie pre Spracováva sa Ahoj platba
     * $applicationState == "CANCELLED" || "REJECTED" || "DELETED"
     *
     * @return mixed
     */
    public function getCanceledStatus()
    {
        return $this->getAhojpayConfig('payment/ahojpay/order_status_canceled');
    }

    /**
     * Nastavenie pre aktivovanie Logovania
     * @return mixed
     */
    public function isLoggerEnabled()
    {
        return $this->getAhojpayConfig(self::USING_AHOJPAY_LOGGER);
    }
}
