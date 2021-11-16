<?php

namespace Ahoj\Ahojpay\Block;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;

class Data extends Template
{

    /* table data ahoj_ahojpay_order */
    const AHOJPAY_STATUS_TABLE = 'ahoj_ahojpay_order';
    const ORDER = "order_id";
    const CALLBACK_URL = "callback_url";
    const PAYMENT = 'payment';
    const STATUS = 'status';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    )
    {
        $this->resourceConnection = $resourceConnection;
    }

    /* vlozenie callback url do table ahoj_ahojpay_order */
    public function insertOrder($orderId, $callbackUrl, $status, $payment)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName(self::AHOJPAY_STATUS_TABLE);

        $data = [
            self::ORDER => $orderId,
            self::CALLBACK_URL => $callbackUrl,
            self::PAYMENT => $payment,
            self::STATUS => $status,
        ];

        $connection->insert($tableName, $data);
    }

    /* vymazanie zaznam z table ahoj_ahojpay_order */
    public function deleteOrder($orderId)
    {
        $connection = $this->resourceConnection->getConnection();
        $ahojTable = $this->resourceConnection->getTableName(self::AHOJPAY_STATUS_TABLE);
        $sql = "DELETE FROM $ahojTable WHERE order_id = '$orderId'";
        $connection->query($sql);
    }

    /* vybratie vsetkych existujucich zaznamov v table ahoj_ahojpay_order */
    public function selectOrders()
    {
        $connection = $this->resourceConnection->getConnection();
        $ahojTable = $this->resourceConnection->getTableName(self::AHOJPAY_STATUS_TABLE);
        $sql = "SELECT * FROM $ahojTable";
        $result = $connection->query($sql);
        return $result;
    }
}
