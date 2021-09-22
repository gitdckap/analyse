<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_BannerSlider
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Dckap\Theme\Setup;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Mageplaza\BannerSlider\Model\Config\Source\Template;
use Psr\Log\LoggerInterface;

/**
 * Class InstallSchema
 * package Mageplaza\BannerSlider\Setup
 */

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Template
     */
    protected $template;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * InstallSchema constructor.
     *
     * @param Template $template
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        Template $template,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->logger     = $logger;
        $this->template   = $template;
        $this->fileSystem = $filesystem;
    }

    /**
     * install tables
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getTable('mageplaza_bannerslider_banner');

        $columns = [
            'slider' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => false,
                'comment' => 'Slider Image'
            ],
            'heading' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => false,
                'comment' => 'Heading'
            ],
            'button_name' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => false,
                'comment' => 'Button Name'
            ],
            'button_url' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'default' => false,
                'comment' => 'Button URL'
            ]
        ];

        $connection = $setup->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($table, $name, $definition);
        }

        $setup->endSetup();
    }
}
