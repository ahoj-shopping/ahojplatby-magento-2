<?php

namespace Ahoj\Ahojpay\Model\Payment;

require_once(BP."/UniModul/ahoj-pay.php");

class Ahojpay extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = "ahojpay";

}
