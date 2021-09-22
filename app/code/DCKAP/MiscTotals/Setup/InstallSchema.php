<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $quoteAddressTable = 'quote_address';
        $quoteTable = 'quote';
        $orderTable = 'sales_order';
        $invoiceTable = 'sales_invoice';
        $creditmemoTable = 'sales_creditmemo';

        //Setup adult_signature_fee column for quote, quote_address and order
        //Quote address tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteAddressTable),
                'adult_signature_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Adult Signature Fee'
                ]
            );

        //Quote tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($quoteTable),
                'adult_signature_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Adult Signature Fee'
                ]
            );

        //Order tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($orderTable),
                'adult_signature_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Adult Signature Fee'
                ]
            );

        //Invoice tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($invoiceTable),
                'adult_signature_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Adult Signature Fee'
                ]
            );

        //Credit memo tables
        $setup->getConnection()
            ->addColumn(
                $setup->getTable($creditmemoTable),
                'adult_signature_fee',
                [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'default' => 0.00,
                    'nullable' => true,
                    'comment' => 'Adult Signature Fee'
                ]
            );

        $setup->endSetup();
    }
}
