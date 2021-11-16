<?php

namespace Ahoj\Ahojpay\Model\Payment;

require_once(BP."/UniModul/ahoj-pay.php");

class Rozlozto extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'rozlozto';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'rozlozto';
}
