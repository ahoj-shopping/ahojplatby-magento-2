<?php

namespace Ahoj\Ahojpay\Block;

class EshopData extends \Magento\Framework\View\Element\Template
{

    protected $_storeManager;

    protected $_context;

    private $checkoutSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context    $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session            $checkoutSession,
        array                                      $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }

    /**
     * URL store
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Ziskanie poslednej vytvorenej objednavky - after_order_place, resp. po ulozeni vytvorenej objednavky
     *
     * @return mixed|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLastOrderId()
    {
        $lastorderId = $this->checkoutSession->getQuote()->reserveOrderId();
        $reservedOrderId = $this->checkoutSession->getQuote()->getReservedOrderId();
        return $reservedOrderId;
    }
}
