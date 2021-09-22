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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Database Schema Upgrader
 *
 * @package Cayan\Payment\Setup
 * @author Igor Miura
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade the database schema
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cayan_codes'),
                'pin',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 4,
                    'nullable' => false,
                    'comment' => 'PIN'
                ]
            );
        }

        $setup->endSetup();
    }
}
