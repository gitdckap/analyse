<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Database Schema Installer
 *
 * @package Cayan\Payment\Setup
 * @author Igor Miura
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create database tables used by the module
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $table = $setup->getConnection()->newTable($setup->getTable('cayan_codes'))
            ->addColumn(
                'code_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Code Id'
            )->addColumn(
                'code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Code'
            )->addColumn(
                'balance',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Balance'
            )->setComment(
                'Cayan GiftCard Codes'
            );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable($setup->getTable('cayan_codes_history'))
            ->addColumn(
                'history_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'History Id'
            )->addColumn(
                'code_id_fk',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Code ID'
            )->addColumn(
                'order_id_fk',
                Table::TYPE_INTEGER,
                255,
                ['nullable' => false, 'unsigned' => true],
                'Order ID'
            )->addColumn(
                'transaction_code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Cayan transaction token'
            )->addColumn(
                'balance_used',
                Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Balance Used'
            )->setComment(
                'Cayan GiftCard Code Usage History'
            );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'cayan_codes_history',
                'code_id_fk',
                $setup->getTable('cayan_codes'),
                'code_id'
            ),
            $setup->getTable('cayan_codes_history'),
            'code_id_fk',
            $setup->getTable('cayan_codes'),
            'code_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'cayan_codes_history',
                'order_id_fk',
                'sales_order',
                'entity_id'
            ),
            $setup->getTable('cayan_codes_history'),
            'order_id_fk',
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $table = $setup->getConnection()->newTable($setup->getTable('cayan_codes_in_quote'))
            ->addColumn(
                'inquote_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'History Id'
            )->addColumn(
                'code_id_fk',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Code ID'
            )->addColumn(
                'quote_id_fk',
                Table::TYPE_INTEGER,
                255,
                ['nullable' => false, 'unsigned' => true],
                'Quote ID'
            )->setComment(
                'Cayan GiftCard Code Usage in Quotes.'
            );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'cayan_codes_in_quote',
                'code_id_fk',
                $setup->getTable('cayan_codes'),
                'code_id'
            ),
            $setup->getTable('cayan_codes_in_quote'),
            'code_id_fk',
            $setup->getTable('cayan_codes'),
            'code_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'cayan_codes_in_quote',
                'quote_id_fk',
                'quote',
                'entity_id'
            ),
            $setup->getTable('cayan_codes_in_quote'),
            'quote_id_fk',
            $setup->getTable('quote'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->query('ALTER TABLE `'.$setup->getTable('cayan_codes_history').
            '` CHANGE `balance_used` `balance_used` DECIMAL(12,4) NOT NULL DEFAULT \'0.0000\' 
            COMMENT \'Balance Used\';');

        $setup->endSetup();
    }
}
