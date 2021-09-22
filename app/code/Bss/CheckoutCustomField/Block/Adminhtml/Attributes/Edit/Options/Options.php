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
namespace Bss\CheckoutCustomField\Block\Adminhtml\Attributes\Edit\Options;

class Options extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Bss\CheckoutCustomField\Model\AttributeOption
     */
    protected $modelOption;

    /**
     * @var string
     */
    protected $_template = 'Bss_CheckoutCustomField::attributes/options.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\CheckoutCustomField\Model\AttributeOption $modelOption
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\CheckoutCustomField\Model\AttributeOption $modelOption,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->modelOption = $modelOption;
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return array
     */
    public function getStores()
    {
        if (!$this->hasStores()) {
            $this->setData('stores', $this->_storeManager->getStores(true));
        }
        return $this->_getData('stores');
    }

    /**
     * Returns stores sorted by Sort Order
     *
     * @return array
     */
    public function getStoresSortedBySortOrder()
    {
        $stores = $this->getStores();
        if (is_array($stores)) {
            usort($stores, function ($storeA, $storeB) {
                if ($storeA->getSortOrder() == $storeB->getSortOrder()) {
                    return $storeA->getId() < $storeB->getId() ? -1 : 1;
                }
                return ($storeA->getSortOrder() < $storeB->getSortOrder()) ? -1 : 1;
            });
        }
        return $stores;
    }

    /**
     * Retrieve attribute option values if attribute input type select or multiselect
     *
     * @return array
     */
    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = [];

            $attribute = $this->getAttributeObject();
            $type = $attribute->getFrontendInput();
            $valueType = ['select', 'multiselect', 'dropdown'];
            $values = $this->returnValues($type, $valueType, $attribute, $values);
            $this->setData('option_values', $values);
        }
        if (!is_array($values)) {
            $values = [];
        }

        return $values;
    }

    /**
     * @param string $type
     * @param array $valueType
     * @param Attribute $attribute
     * @param array $values
     * @return array
     */
    private function returnValues($type, $valueType, $attribute, $values)
    {
        if (in_array($type, $valueType)) {
            $options = $this->modelOption->getOptions($attribute->getAttributeId());
            $defaultValues = $options[0];
            $inputType = 'checkbox';
            if ($type === 'select' || $type === 'dropdown') {
                $inputType = 'radio';
            }
            foreach ($options[1] as $key => $option) {
                $value = [];
                if ($defaultValues[$key]) {
                    $value['checked'] = array_key_exists($key, $defaultValues) ? 'checked="checked"' : '';
                }
                $value['intype'] = $inputType;
                $value['id'] = $key;
                $value['sort_order'] = 0;

                foreach ($this->getStores() as $store) {
                    $storeId = $store->getId();
                    if (!isset($option[$storeId])) {
                        continue;
                    }
                    $value['store' . $storeId] = $option[$storeId];
                }
                $values[] = $value;
            }
        }
            return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @codeCoverageIgnore
     */
    protected function getAttributeObject()
    {
        return $this->registry->registry('entity_attribute');
    }
}
