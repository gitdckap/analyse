<?php

namespace Cloras\Base\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $integration = $installer->getTable('cloras_integration_entity');

        if (!$installer->tableExists($integration)) {
            $integrationTable = $installer->getConnection()->newTable($integration)->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
                ],
                'Integration ID'
            )->addColumn(
                'batch_id',
                Table::TYPE_TEXT,
                300,
                ['nullable' => false],
                'Batch Id'
            )->addColumn(
                'base_url',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Base URL'
            )->addColumn(
                'api_path',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'API Path'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                300,
                ['nullable' => false],
                'api type'
            )->addColumn(
                'token',
                Table::TYPE_TEXT,
                300,
                ['nullable' => false],
                'token'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                6,
                ['nullable' => false],
                'status'
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
                $installer->getIdxName($integration, ['entity_id']),
                ['entity_id']
            )->setComment('Cloras integration');

            $installer->getConnection()->createTable($integrationTable);
        }//end if

        $integration_eav = $installer->getTable('cloras_integration_eav_attribute');

        if (!$installer->tableExists($integration_eav)) {
            $integrationEavTable = $installer->getConnection()->newTable($integration_eav)->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true,
                ],
                'Integration ID'
            )->addColumn(
                'integration_id',
                Table::TYPE_INTEGER,
                null,
                [
                'unsigned' => true,
                'nullable' => false,
                ],
                'Integration Id'
            )->addColumn(
                'attribute',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Attribute Name'
            )->addColumn(
                'value',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Attribute value'
            )->addIndex(
                $installer->getIdxName($integration_eav, ['integration_id']),
                ['integration_id']
            )->addForeignKey(
                $installer->getFkName($integration_eav, 'integration_id', 'cloras_integration_entity', 'entity_id'),
                'integration_id',
                $installer->getTable('cloras_integration_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Cloras integration Eav attribute');

            $installer->getConnection()->createTable($integrationEavTable);
        }//end if

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $setup->getTable('sales_invoice'),
                'p21_invoice_id',
                [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'P21 Invoice Id',
                ]
            );

            $installer->getConnection()->addColumn(
                $setup->getTable('sales_invoice_grid'),
                'p21_invoice_id',
                [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => true,
                'comment'  => 'P21 Invoice Id',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('cloras_integration_entity'),
                'cloras_url',
                [
                'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length'   => 255,
                'nullable' => false,
                'comment'  => 'Cloras base URL',
                'after'    => 'base_url',
                ]
            );
        }//end if


        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $cloras_products = $installer->getTable('cloras_products_index');

            if (!$installer->tableExists($cloras_products)) {
                $clorasProductsTable = $installer->getConnection()->newTable($cloras_products)->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                    ],
                    'Integration ID'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                    'unsigned' => true,
                    'nullable' => false,
                    ],
                    'Product Id'
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
                    $installer->getIdxName($cloras_products, ['product_id']),
                    [
                    'product_id'
                    ]
                )->setComment('Cloras Products Index');

                $installer->getConnection()->createTable($clorasProductsTable);
            }
        }
        
        $installer->endSetup();
    }//end upgrade()
}//end class
