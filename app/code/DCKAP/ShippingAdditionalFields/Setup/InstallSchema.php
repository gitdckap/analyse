<?php

namespace Dckap\ShippingAdditionalFields\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 *
 * @package Dckap\ShippingAdditionalFields\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /* While module install, creates columns in quote_address and sales_order_address table */

        $eavTable1 = $installer->getTable('quote');
        $eavTable2 = $installer->getTable('sales_order');

        $columns = [
            'ddi_delivery_contact_email' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Email Notification'
            ],

            'ddi_delivery_contact_no' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Mobile (SMS) Notification'
            ]

        ];

        $connection = $installer->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($eavTable1, $name, $definition);
            $connection->addColumn($eavTable2, $name, $definition);
        }
        $installer->endSetup();
    }
}
