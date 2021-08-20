<?php

namespace Ahoj\AhojPay\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /* pridanie column do databazy sales_order */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'ahojpay',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 5000,
                'nullable' => true,
                'comment' => 'AhojPay Url'
            ]
        );

        $installer->endSetup();
    }
}
