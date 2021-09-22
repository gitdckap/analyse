<?php
/**
 * *
 *
 * @author    DCKAP Team
 * @copyright Copyright (c) 2018 DCKAP (https://www.dckap.com)
 * @package   Dckap_Mdsshipping
 * /
 *
 * *
 *  Copyright © 2018 DCKAP. All rights reserved.
 * /
 */

namespace Dckap\ShippingAdditionalFields\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer = $setup;

            $installer->startSetup();

            /* While module install, creates columns in quote_address and sales_order_address table */

            $eavTable1 = $installer->getTable('quote');
            $eavTable2 = $installer->getTable('sales_order');

            $columns = [
                'ddi_pref_warehouse' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => false,
                    'comment' => 'Preferred Warehouse',
                ]
            ];

            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($eavTable1, $name, $definition);
                $connection->addColumn($eavTable2, $name, $definition);
            }
        }
       

        $setup->endSetup();
    }
}
