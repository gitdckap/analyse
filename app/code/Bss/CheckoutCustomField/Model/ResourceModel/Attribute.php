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
namespace Bss\CheckoutCustomField\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ObjectManager;

class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $resigtry;

    /**
     * @var \Bss\CheckoutCustomField\Model\AttributeOption
     */
    protected $modelOption;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Bss\CheckoutCustomField\Model\AttributeOption $modelOption
     * @param \Magento\Framework\Registry $resigtry
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Bss\CheckoutCustomField\Model\AttributeOption $modelOption,
        \Magento\Framework\Registry $resigtry
    ) {
        parent::__construct($context);
        $this->resigtry = $resigtry;
        $this->modelOption = $modelOption;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_checkout_attribute', 'attribute_id');
    }

    /**
     * @param AbstractModel $object
     * @param $entityTypeId
     * @param string $code
     * @return bool
     */
    public function loadByCode(AbstractModel $object, $entityTypeId, $code)
    {
        $bind = [':attribute_id' => $entityTypeId];
        $select = $this->_getLoadSelect('attribute_code', $code, $object)->where('attribute_id = :attribute_id');
        $data = $this->getConnection()->fetchRow($select, $bind);

        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
            return true;
        }
        return false;
    }

    /**
     * @param AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|void
     */
    protected function _afterSave(AbstractModel $object)
    {
        $attributeId = $object->getId();
        $this->modelOption->getCollection()
            ->addFieldToFilter('attribute_id', ['eq' => $attributeId])
            ->walk('delete');
        if ($options = $this->resigtry->registry('attribute_option')) {
            $defaultOption = $this->resigtry->registry('attribute_option_default');
            foreach ($options['value'] as $key => $option) {
                if (isset($options['delete'][$key]) && $options['delete'][$key] == 1) {
                    continue;
                }
                $default = 0;
                if ($defaultOption) {
                    $default = (int)in_array($key, $defaultOption);
                }
                foreach ($option as $k => $val) {
                    $this->modelOption->setValueId(null);
                    $this->modelOption->setIsDefault($default);
                    $this->modelOption->setAttributeId($attributeId);
                    $this->modelOption->setOptionId(str_replace('option_', "", $key));
                    $this->modelOption->setStoreId($k);
                    $this->modelOption->setValue($val);
                    $this->saveModelOption();
                }
            }
        }
    }

    /**
     * @return \Bss\CheckoutCustomField\Model\AttributeOption
     * @throws \Exception
     */
    private function saveModelOption()
    {
        return $this->modelOption->save();
    }

    /**
     * @param Setup $setup
     * @param array $field
     * @return mixed
     */
    public function queryData($setup, $field)
    {
        $data = [];
        $sql = $setup->getConnection()->select()->from(
            ['main_table' => $setup->getTable('sales_order_grid')],
            $field
        )
            ->where(
                'main_table.increment_id > 0'
            );

        $query = $setup->getConnection()->query($sql);
        while ($row = $query->fetch()) {
            array_push($data, $row);
        }
        return $data;
    }
}
