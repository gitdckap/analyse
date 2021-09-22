<?php
namespace DCKAP\Categorywidget\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (!$eavSetup->getAttributeId(\Magento\Catalog\Model\Product::ENTITY, 'is_feature')) {
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'is_feature',
                [
                    'type' => 'int',
                    'label' => 'Is Feature',
                    'input' => 'text',
                    'sort_order' => 100,
                    'source' => '',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => null,
                    'group' => '',
                    'backend' => ''
                ]
            );
        }
    }
}
