<?php

namespace Dckap\Checkout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $quote = 'quote';
            $orderTable = 'sales_order';

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quote),
                    'ddi_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'DDI Order Id'
                    ]
                );
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'ddi_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'DDI Order Id'
                    ]
                );
        }

        $setup->endSetup();
    }
}
