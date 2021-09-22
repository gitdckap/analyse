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
namespace Bss\CheckoutCustomField\Block\Express;

class Review extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\CheckoutCustomField\Model\AttributeOption
     */
    protected $attributeOption;

    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $attribute;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * Review constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\CheckoutCustomField\Model\AttributeOption $attributeOption
     * @param \Bss\CheckoutCustomField\Model\Attribute $attribute
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\CheckoutCustomField\Model\AttributeOption $attributeOption,
        \Bss\CheckoutCustomField\Model\Attribute $attribute,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->attributeOption = $attributeOption;
        $this->attribute = $attribute;
        $this->jsonHelper = $jsonHelper;
        $this->cartHelper = $cartHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getCustomField()
    {
        return $this->cartHelper->getQuote()->getBssCustomfield();
    }

    /**
     * @param Attribute $attribute
     * @return array
     */
    public function getOptions($attribute)
    {
        if ($attribute->getFrontendInput() == 'date') {
            $options = [
                'dateFormat' => 'm/d/Y',
                "timeFormat" => 'hh:mm',
                "showsTime" => true
            ];
        } elseif ($attribute->getFrontendInput() == 'boolean') {
            $options = [
                ['value' => '0', 'label' => __('No')],
                ['value' => '1', 'label' => __('Yes')]
            ];
        } else {
            $options = $this->attributeOption->getAttributeOptions($attribute->getAttributeId());
        }

        return $options;
    }

    /**
     * @param Attribute $attribute
     * @return array
     */
    public function getDefaultValue($attribute)
    {
        if ($this->getCustomField()) {
            $values = $this->jsonHelper->jsonDecode($this->getCustomField());
        }
        if ($attribute->getFrontendInput() == 'dropdown'
            || $attribute->getFrontendInput() == 'select'
            || $attribute->getFrontendInput() == 'multiselect'
        ) {
            if (isset($values[$attribute->getAttributeCode()])) {
                $default = $values[$attribute->getAttributeCode()];
            } else {
                $default = $this->attributeOption->getOptions($attribute->getAttributeId());
                $default = $this->attributeOption->getDefaultValue($default[0]);
            }
        } else {
            if (isset($values[$attribute->getAttributeCode()])) {
                $default = $values[$attribute->getAttributeCode()];
            } else {
                $default = $attribute->getDefaultValue();
            }
        }

        return $default;
    }

    /**
     * @return \Bss\CheckoutCustomField\Model\Attribute
     */
    public function getCustomFieldChekout()
    {
        return $this->attribute->getCustomFieldChekout();
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param string $json
     * @return mixed
     */
    public function jsonDecode($json)
    {
        return $this->jsonHelper->jsonDecode($json);
    }
}
