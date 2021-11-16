<?php

namespace Ahoj\Ahojpay\Model\Adminhtml\Source;

use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Class Processing
 *
 * Schvalena Ahoj platba
 *
 * @package Ahoj\Ahojpay\Model\Adminhtml\Source
 */
class Processing extends Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_PROCESSING,
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->_stateStatuses
            ? $this->_orderConfig->getStateStatuses($this->_stateStatuses)
            : $this->_orderConfig->getStatuses();

        $options = [['value' => '', 'label' => __('-- Use Default --')]];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => $label];
        }
        return $options;
    }
}
