<?php

namespace Ahoj\Ahojpay\Logger;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $filePath;

    public function __construct(
        DriverInterface $filesystem,
        DirectoryList $dir
    ) {
        $ds = DIRECTORY_SEPARATOR;
        $this->filePath = $dir->getPath('log') . $ds . 'ahojpay.log';

        parent::__construct($filesystem, $this->filePath);
    }

    public function exists()
    {
        return file_exists($this->filePath);
    }
}
