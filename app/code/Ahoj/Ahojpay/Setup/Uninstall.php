<?php
namespace Ahoj\Ahojpay\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /* dropnutie tabulky ahoj_ahojpay_order pri odinstalacii modulu */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable('ahoj_ahojpay_order'));

        $installer->endSetup();
    }
}