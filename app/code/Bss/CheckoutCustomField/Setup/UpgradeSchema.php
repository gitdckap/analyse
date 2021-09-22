<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CheckoutCustomField\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('bss_checkout_attribute');
        
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $connection = $setup->getConnection();

            $connection->changeColumn(
                $tableName,
                'frontend_label',
                'frontend_label',
                ['type' => Table::TYPE_TEXT, '64k', 'nullable' => false, 'default' => ''],
                'Frontend Label'
            );
        }
        $tableName = $setup->getTable('bss_checkout_attribute_order_grid_view');

        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection(
            )->newTable($setup->getTable('bss_checkout_attribute_order_grid_view'))->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Attribute Id'
            )->addColumn(
                'incrementId',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                [],
                'Increment Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true],
                'Store Id'
            )->addIndex(
                $setup->getIdxName(
                    'bss_checkout_attribute_order_grid_view',
                    ['incrementId', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['incrementId', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    'bss_checkout_attribute_order_grid_view',
                    'incrementId',
                    'sales_order_grid',
                    'increment_id'
                ),
                'incrementId',
                $setup->getTable('sales_order_grid'),
                'increment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName('bss_checkout_attribute_order_grid_view', 'store_id', 'sales_order_grid', 'store_id'),
                'store_id',
                $setup->getTable('sales_order_grid'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Sales Flat Order Grid Custom Field'
            );
            
            $setup->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.1.7', '<=')) {
            $setup->getConnection()->dropForeignKey(
                $setup->getTable('bss_checkout_attribute_order_grid_view'),
                $setup->getFkName(
                    'bss_checkout_attribute_order_grid_view',
                    'store_id',
                    'sales_order_grid',
                    'store_id'
                )
            );
        }
        $setup->endSetup();
    }
}
