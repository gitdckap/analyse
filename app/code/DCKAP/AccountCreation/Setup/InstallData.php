<?php

namespace Dckap\AccountCreation\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

class InstallData implements InstallDataInterface
{
    const CUSTOMER_ACCOUNT_ACTIVE = 'account_is_active';
    const CUSTOMER_COMPANY = 'customer_company';
    const CUSTOMER_TYPE = 'is_b2b';

    private $eavSetupFactory;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        Config $eavConfig,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /**
         * @var $attributeSet AttributeSet
         */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::CUSTOMER_ACCOUNT_ACTIVE,
            [
                'type' => 'int',
                'label' => 'Account is Active',
                'input' => 'select',
                "source" => "Dckap\AccountCreation\Model\Config\Source\CustomerYesNoOptions::class",
                'required' => false,
                'default' => '0',
                'visible' => true,
                'user_defined' => false,
                'sort_order' => 215,
                'position' => 215,
                'system' => false,
            ]
        );
        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::CUSTOMER_COMPANY,
            [
                'type' => 'varchar',
                'label' => 'Company',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'position' => 999,
                'system' => 0,
            ]
        );

        $customerSetup->addAttribute(
            Customer::ENTITY,
            self::CUSTOMER_TYPE,
            [
                'type' => 'int',
                'label' => 'Is B2B',
                'input' => 'select',
                "source" => "Dckap\AccountCreation\Model\Config\Source\CustomerYesNoOptions::class",
                'required' => false,
                'default' => '0',
                'visible' => true,
                'user_defined' => false,
                'sort_order' => 215,
                'position' => 215,
                'system' => false,
            ]
        );

        $account_is_active = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::CUSTOMER_ACCOUNT_ACTIVE
        )
            ->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer', 'customer_account_create', 'customer_account_edit'],
                    'is_user_defined' => 1
                ]
            );
        $account_is_active->save();

        $company = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::CUSTOMER_COMPANY
        )
            ->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer', 'customer_account_create', 'customer_account_edit'],
                    'is_user_defined' => 1
                ]
            );
        $company->save();

        $is_b2b = $customerSetup->getEavConfig()->getAttribute(
            Customer::ENTITY,
            self::CUSTOMER_TYPE
        )
            ->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer', 'customer_account_create', 'customer_account_edit'],
                    'is_user_defined' => 1
                ]
            );
        $is_b2b->save();

        $setup->endSetup();
    }
}
