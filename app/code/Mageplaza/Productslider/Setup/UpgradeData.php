<?php

namespace Mageplaza\Productslider\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;
/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
   /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
    	 $installer = $setup;
        $installer->startSetup();

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
         if (version_compare($context->getVersion(), '2.0.2', '<=')) {
         	$eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'is_featured', [
         		'group' => 'Product Details',
         		 'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE
                
         		]
        );

         }
         $installer->endSetup();
}
}