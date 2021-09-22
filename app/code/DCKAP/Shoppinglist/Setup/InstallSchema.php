<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'customer_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('shopping_list')
        )->addColumn(
            'list_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'List Id'
        )->addColumn(
            'list_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Shoppinglist Name'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer Id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->setComment(
            'Customer Shopping List Name'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'customer_address_entity'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('shopping_list_item')
        )->addColumn(
            'shopping_list_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Shoppinglist Item Id'
        )->addColumn(
            'shopping_list_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Shoppinglist Id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            ['nullable' => false],
            'Product Id'
        )->addColumn(
            'product_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Product Type'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            ['nullable' => false],
            'Parent Id'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Value'
        )->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            255,
            ['nullable' => false, 'default' => 1],
            'Quantity'
        )->addColumn(
            'added_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Added Date'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )
        ->addIndex(
            $installer->getIdxName(
                'shopping_list',
                ['store_id']
            ),
            ['store_id']
        )->setComment(
            'Customer Shopping List Items'
        );
        $installer->getConnection()->createTable($table);

       
        $installer->endSetup();
    }
}
