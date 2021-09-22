<?php

namespace Cloras\Base\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Cloras\Base\Api\IntegrationInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    const P21_CUSTOMER_ID = 'cloras_p21_customer_id';

    const P21_CONTACT_ID = 'cloras_p21_contact_id';

    const P21_SHIPTO_ID = 'cloras_p21_shipto_id';

    /**
     * Customer setup factory.
     *
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * Init.
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory  $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        IntegrationInterface $integrationInterface
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory  = $attributeSetFactory;
        $this->integrationInterface = $integrationInterface;
    }//end __construct()

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /*
         * @var \Magento\Customer\Setup\CustomerSetup
         */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $setup->startSetup();

        $attributesInfo = [
            self::P21_CUSTOMER_ID => [
                'label'        => 'P21 Customer ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true,
            ],
            self::P21_CONTACT_ID  => [
                'label'        => 'P21 Contact ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true,
            ],
            self::P21_SHIPTO_ID   => [
                'label'        => 'P21 ShipTo ID',
                'type'         => 'varchar',
                'input'        => 'text',
                'visible'      => true,
                'required'     => false,
                'system'       => 0,
                'user_defined' => true,
            ],
        ];

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /*
         * @var AttributeSet
         */
        $attributeSet     = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        foreach ($attributesInfo as $attributeCode => $attributeParams) {
            $customerSetup->addAttribute(Customer::ENTITY, $attributeCode, $attributeParams);
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
            $attribute->addData(
                [
                    'attribute_set_id'   => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms'      => ['adminhtml_customer'],
                ]
            );
            $this->attributeSave($attribute);
        }

        //create integration
        $this->integrationInterface->createNewIntegration();

        $setup->endSetup();
    }//end install()

    private function attributeSave($attribute)
    {
        $attribute->save();
    }
}//end class
