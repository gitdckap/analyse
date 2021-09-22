<?php

namespace Cloras\DDI\Setup;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param Config $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavConfig            = $eavConfig;
        $this->_eavSetupFactory     = $eavSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    /**
     * Upgrades DB for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->startSetup();

            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute('customer_address', 'ddi_ship_number', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'DDI Ship Number',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'system'=> false,
                'group'=> 'General',
                'global' => true,
                'visible_on_front' => true,
            ]);

            $customAttribute = $this->eavConfig->getAttribute('customer_address', 'ddi_ship_number');

            $customAttribute->setData('used_in_forms', ['adminhtml_customer_address','customer_address_edit','customer_register_address']);
            $customAttribute->save();

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $setup->startSetup();

            $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute('customer_address', 'erp_account_number', [
                'type' => 'varchar',
                'input' => 'text',
                'label' => 'ERP Account Number',
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'system'=> false,
                'group'=> 'General',
                'global' => true,
                'visible_on_front' => true,
            ]);

            $customAttribute = $this->eavConfig->getAttribute('customer_address', 'erp_account_number');

            $customAttribute->setData('used_in_forms', ['adminhtml_customer_address','customer_address_edit','customer_register_address']);
            $customAttribute->save();

            $setup->endSetup();
        }
    }
}
