<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CheckoutCustomField\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Data upgrade script
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $attribute;

    /**
     * @var \Bss\CheckoutCustomField\Model\ResourceModel\Attribute
     */
    protected $attributeResource;

    /**
     * UpgradeData constructor.
     * @param \Bss\CheckoutCustomField\Model\Attribute $attribute
     * @param \Bss\CheckoutCustomField\Model\ResourceModel\Attribute $attributeResource
     */
    public function __construct(
        \Bss\CheckoutCustomField\Model\Attribute $attribute,
        \Bss\CheckoutCustomField\Model\ResourceModel\Attribute $attributeResource
    ) {
        $this->attribute = $attribute;
        $this->attributeResource = $attributeResource;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $data = [];
            $attributes = $this->attribute->getCollection();
            foreach ($attributes as $attribute) {
                $data[] = $attribute->getAttributeCode();
                $setup->getConnection()->addColumn(
                    $setup->getTable('bss_checkout_attribute_order_grid_view'),
                    $attribute->getAttributeCode(),
                    'text'
                );
            }
            if (!empty($data)) {
                $insertValue = $this->selectData($setup, $data);
                $setup->getConnection()
                    ->insertMultiple(
                        $setup->getTable('bss_checkout_attribute_order_grid_view'),
                        $insertValue
                    );
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param array $data
     * @return array|mixed
     */
    private function selectData($setup, $data)
    {
        $field = $data;
        $field['incrementId'] = 'increment_id';
        $field['store_id'] = 'store_id';
        $insertData = $this->attributeResource->queryData($setup, $field);
        return $insertData;
    }
}
