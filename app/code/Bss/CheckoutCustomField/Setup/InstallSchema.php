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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getConnection(
        )->newTable($setup->getTable('bss_checkout_attribute'))->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Id'
        );
        $table = $this->addColumnTableOne($table);
        $table = $this->addColumnTableTwo($table);

        $setup->getConnection()->createTable($table);

        $table = $this->returnTable($setup);

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn($setup->getTable('quote'), 'bss_customfield', 'text');
        $setup->getConnection()->addColumn($setup->getTable('sales_order'), 'bss_customfield', 'text');
        
        $setup->endSetup();
    }

    /**
     * @param mixed $table
     * @return mixed
     */
    private function addColumnTableTwo($table)
    {
        return $table->addColumn(
            'default_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Default Value'
        )
        ->addColumn(
            'visible_frontend',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Visible On Frontend'
        )
        ->addColumn(
            'visible_backend',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Visible On Backend'
        )
        ->addColumn(
            'show_in_shipping',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Show In Shipping'
        )
        ->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort Order'
        )
        ->addColumn(
            'show_gird',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Show Admin Gird'
        )
        ->addColumn(
            'show_in_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Show In Order'
        )
        ->addColumn(
            'show_in_pdf',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Show In Pdf'
        )
        ->addColumn(
            'show_in_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Show In Email'
        )
        ->setComment(
            'Order Attributes Table'
        );
    }

    /**
     * @param mixed $table
     * @return mixed
     */
    private function addColumnTableOne($table)
    {
        return $table->addColumn(
            'attribute_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Attribute Code'
        )
        ->addColumn(
            'backend_label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Backend Label'
        )
        ->addColumn(
            'frontend_label',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Frontend Label'
        )->addColumn(
            'frontend_class',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Frontend Class'
        )->addColumn(
            'frontend_input',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Frontend Input'
        )
        ->addColumn(
            'is_required',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Defines Is Required'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Store Id'
        )->addColumn(
            'is_user_defined',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Defines Is User Defined'
        );
    }

    /**
     * @param mixed $setup
     * @return mixed
     */
    private function returnTable($setup)
    {
        return $setup->getConnection(
        )->newTable($setup->getTable('bss_checkout_attribute_option'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value Id'
            )->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute Id'
            )->addColumn(
                'option_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Option Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store Id'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Value'
            )->addColumn(
                'is_default',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Default'
            )->addIndex(
                $setup->getIdxName('bss_checkout_attribute_option', ['attribute_id']),
                ['attribute_id']
            )->addForeignKey(
                $setup->getFkName(
                    'bss_checkout_attribute_option',
                    'attribute_id',
                    'bss_checkout_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $setup->getTable('bss_checkout_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addIndex(
                $setup->getIdxName('bss_checkout_attribute_option', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $setup->getFkName('bss_checkout_attribute_option', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment(
                'Order Attributes Table'
            );
    }
}
