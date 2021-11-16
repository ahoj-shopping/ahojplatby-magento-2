<?php
namespace Ahoj\Ahojpay\Helper;

use Magento\Framework\Filesystem\DirectoryList;
use Ahoj\Ahojpay\Logger\WebhooksLogger as AhojpayLogger;
use Ahoj\Ahojpay\Helper\Data;

class Logger
{
    /**
     * Directory list interface
     * used to programmatically retrieve paths within magento app install
     * @var DirectoryList
     */
    protected $_dir;

    /**
     * Path to the log file
     * @var string
     */
    protected $_logPath;

    /**
     * Ahojpay logger object
     * @var AhojpayLogger
     */
    protected $_ahojpayLogger;

    /**
     * is the logger enabled?
     * @var boolean
     */
    protected $_loggerEnabled;

    public function __construct(
        DirectoryList $dir,
        AhojpayLogger $_ahojpayLogger,
        Data $ahojpayConfig,
        $logPath = null
    )
    {
        $this->_dir = $dir;
        $this->_ahojpayLogger = $_ahojpayLogger;
        $this->_loggerEnabled = $ahojpayConfig->isLoggerEnabled();
        $this->_logPath = (!empty($logPath)) ? $logPath : $this->_dir->getPath('log') . '/ahojpay.log';
    }

    /**
     * Getter method for the logfile's path
     * @return string
     */
    public function getPath()
    {
        return $this->_logPath;
    }

    /**
     * Method to log the provided message
     * @param string $message
     */
    public function log($message)
    {
        if ($this->_loggerEnabled) {
            $this->_ahojpayLogger->info($message);
        }
    }
}
