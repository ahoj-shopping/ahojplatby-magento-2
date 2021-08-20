<?php

namespace Ahoj\Ahojpay\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    /* vytvorenie tabulky ahoj_ahojpay_order*/
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('ahoj_ahojpay_order')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('ahoj_ahojpay_order')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Order ID'
                )
                ->addColumn(
                    'callback_url',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'CallBack URL'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    [],
                    'Status of payment'
                )
                ->setComment('AhojPay Table');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}