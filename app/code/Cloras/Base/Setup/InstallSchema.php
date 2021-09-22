<?php

namespace Cloras\Base\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $customers = $installer->getTable('cloras_customers_index');

        if (!$installer->tableExists($customers)) {
            $customerTable = $installer->getConnection()->newTable($customers)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Entity ID'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Customer Id'
            )->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Website Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'state',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'State'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT,
                ],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE,
                ],
                'Updated At'
            )->addIndex(
                $installer->getIdxName($customers, ['customer_id', 'website_id']),
                [
                    'customer_id',
                    'website_id',
                ]
            )->addForeignKey(
                $installer->getFkName($customers, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                Table::ACTION_CASCADE
            )->setComment('Cloras Customers Index');

            $installer->getConnection()->createTable($customerTable);
        }//end if

        $orders = $installer->getTable('cloras_orders_index');

        if (!$installer->tableExists($orders)) {
            $orderTable = $installer->getConnection()->newTable($orders)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Entity ID'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Order Id'
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Customer Id'
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'state',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'State'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT,
                ],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default'  => Table::TIMESTAMP_INIT_UPDATE,
                ],
                'Updated At'
            )->addIndex(
                $installer->getIdxName($orders, ['order_id']),
                ['order_id']
            )->addForeignKey(
                $installer->getFkName($orders, 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Cloras Orders Index');

            $installer->getConnection()->createTable($orderTable);
        }//end if

        $installer->endSetup();
    }//end install()
}//end class
