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
 * @package    Bss_MegaMenu
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Megamenu\Setup;

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
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('bss_megamenu_items');

        /* Upgrade module to 1.0.5 */
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $newTableForStore = $installer->getTable('bss_megamenu_stores');

            // Check if the table already exists
            if ($installer->getConnection()->isTableExists($newTableForStore) != true) {
                $table = $installer->getConnection()
                    ->newTable($newTableForStore)
                    ->addColumn(
                        'category_store_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'name',
                        Table::TYPE_TEXT,
                        225,
                        ['nullable' => false, 'default' => ''],
                        'Label'
                    )
                    ->addColumn(
                        'store_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'nullable' => true,
                            'default' => null
                        ],
                        'Create categories for store'
                    )
                    ->addIndex(
                        $installer->getIdxName('bss_megamenu_stores', ['category_store_id']),
                        ['category_store_id']
                    )
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
                $installer->getConnection()->createTable($table);
            }

            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $installer->getConnection();

                $connection->addColumn(
                    $tableName,
                    'category_store_id',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Category Store',
                    ]
                );
            }
        }
        /* Upgrade module to 1.0.4 */
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $installer->getConnection();
                $connection->addColumn(
                    $tableName,
                    'store_id',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Store ID',
                    ]
                );
                $connection->addColumn(
                    $tableName,
                    'custom_css',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Add Custom Css',
                    ]
                );
            }
        }
        $tableMenuContent = $installer->getTable('bss_megamenu_content');
        if ($installer->getConnection()->isTableExists($tableMenuContent) != true) {
            $table = $installer->getConnection()
                ->newTable($tableMenuContent)
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )->addColumn(
                    'content',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Content'
                )
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
