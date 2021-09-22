<?php

namespace Dckap\Attachment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $installer->run('CREATE TABLE `dckap_product_pdf_attachment_section` (
  `id` int(11) UNSIGNED NOT NULL,
  `section_name` varchar(215) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1');
            $installer->run('ALTER TABLE `dckap_product_pdf_attachment_section`
  ADD PRIMARY KEY (`id`)');
            $installer->run('ALTER TABLE `dckap_product_pdf_attachment_section`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
            $installer->run('ALTER TABLE `dckap_product_pdf_attachment_section` ADD `is_active` INT(1) NOT NULL DEFAULT \'1\' AFTER `section_name`');
            $installer->run('CREATE TABLE `dckap_product_pdf_attachment` (
  `id` int(11) UNSIGNED NOT NULL,
  `sku` varchar(215) NOT NULL,
  `section_id` varchar(1024) DEFAULT NULL,
  `attachment` varchar(1024) DEFAULT NULL,
  `file_type` varchar(1024) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1');
            $installer->run('ALTER TABLE `dckap_product_pdf_attachment`
  ADD PRIMARY KEY (`id`)');
            $installer->run('ALTER TABLE `dckap_product_pdf_attachment`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
        }
        $installer->endSetup();
    }
}
