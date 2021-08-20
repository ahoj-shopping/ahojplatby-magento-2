<?php

namespace Ahoj\Ahojpay\Cron;

class Ahoj
{

    /* info o beziacom crone */
    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        return $this;
    }
}