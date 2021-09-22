<?php

namespace DCKAP\AkeneoSync\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var SchemaSetupInterface $installer */
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'dckap_akeneo_connector_import_log'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('dckap_akeneo_connector_import_log'))
            ->addColumn(
                'log_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Import ID'
            )
            ->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                13,
                ['nullable' => false],
                'Identifier ID'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Code'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->setComment('Dckap Akeneo Connector Import Log');

        $installer->getConnection()->createTable($table);

        /**
         * Create table 'dckap_akeneo_connector_import_log_step'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('dckap_akeneo_connector_import_log_step'))
            ->addColumn(
                'step_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Import ID'
            )
            ->addColumn(
                'log_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Log ID'
            )
            ->addColumn(
                'identifier',
                Table::TYPE_TEXT,
                13,
                ['nullable' => false],
                'Identifier ID'
            )
            ->addColumn(
                'number',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true, 'default' => '0'],
                'Number'
            )
            ->addColumn(
                'method',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Method'
            )
            ->addColumn(
                'message',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Message'
            )
            ->addColumn(
                'continue',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Continue'
            )
            ->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addForeignKey(
                $installer->getFkName('dckap_akeneo_connector_import_log_step', 'log_id', 'akeneo_connector_import_log', 'log_id'),
                'log_id',
                $installer->getTable('dckap_akeneo_connector_import_log'),
                'log_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Dckap Akeneo Connector Import Log Step');

        $installer->getConnection()->createTable($table);


    }
}
