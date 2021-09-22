<?php

namespace Cloras\Base\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
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
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $this->addEAVAttribute($eavSetup, 'text', 'P21 Inventory Master Id', 'inv_mast_uid');

            $this->addEAVAttribute($eavSetup, 'text', 'UOM', 'uom');

            $setup->endSetup();
        }
    }

    private function addEAVAttribute($eavSetup, $inputType, $label, $attributeCode)
    {
        $attribute =  $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeCode,
                [
                'group' => 'General',
                'attribute_set_id' => '4',
                'type' => $inputType,
                'backend' => '',
                'frontend' => '',
                'label' => $label,
                'input' => $inputType,
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => true,
                'apply_to' => '',
                'attribute_set' => 'Default'
                ]
            );
        }
    }
}
