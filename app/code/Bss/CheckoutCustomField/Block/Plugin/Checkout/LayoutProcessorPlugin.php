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

namespace Bss\CheckoutCustomField\Block\Plugin\Checkout;

/**
 * Class LayoutProcessorPlugin
 *
 * @package Bss\CheckoutCustomField\Block\Plugin\Checkout
 */
class LayoutProcessorPlugin
{
    const DISPLAY_SHIPPING_ADDRESS = 0;
    const DISPLAY_SHIPPING_METHOD = 1;
    const DISPLAY_REVIEW_PAYMENT = 2;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $attribute;

    /**
     * @var \Bss\CheckoutCustomField\Model\AttributeOption
     */
    protected $attributeOption;

    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * LayoutProcessorPlugin constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\CheckoutCustomField\Model\Attribute $attribute
     * @param \Bss\CheckoutCustomField\Model\AttributeOption $attributeOption
     * @param \Bss\CheckoutCustomField\Helper\Data $helper
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\CheckoutCustomField\Model\Attribute $attribute,
        \Bss\CheckoutCustomField\Model\AttributeOption $attributeOption,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->storeManager = $storeManager;
        $this->attribute = $attribute;
        $this->attributeOption = $attributeOption;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->localeDate = $localeDate;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array|mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        if (!$this->helper->moduleEnabled()) {
            return $jsLayout;
        }
        $attributes = $this->attribute->getCustomFieldChekout();
        $types = $this->setTypes();
        $elementTmpl = $this->setElementTmpl();
        $customerHasAddress = false;
        $customerId = $this->getSessionCustomerId();
        if ($customerId) {
            $customerData = $this->customer->load($customerId);
            $customerHasAddress = (count($customerData->getAddresses()) > 0);
        }
        $jsLayout = $this->returnJsLayout($jsLayout, $attributes, $customerHasAddress, $types, $elementTmpl);

        return $jsLayout;
    }

    /**
     * @param array $jsLayout
     * @param mixed $attributes
     * @param bool $customerHasAddress
     * @param array $types
     * @param array $elementTmpl
     * @return mixed
     */
    private function returnJsLayout($jsLayout, $attributes, $customerHasAddress, $types, $elementTmpl)
    {
        $cartVirtual = false;
        if ($this->checkoutSession->getQuote()->getIsVirtual()) {
            $cartVirtual = true;
        }
        foreach ($attributes as $attribute) {
            $storeId = $this->storeManager->getStore()->getId();
            $stores = explode(',', $attribute->getStoreId());
            if (!in_array($storeId, $stores)) {
                continue;
            }
            $label = $attribute->getFrontendLabel($storeId);
            $name = $this->setVarName($attribute);
            $validation = $this->setVarValidation($attribute);
            $options = $this->getOptions($attribute);
            $default = $this->setVarDefault($attribute);
            if ($attribute->getShowInShipping() == self::DISPLAY_SHIPPING_ADDRESS) {
                if ($cartVirtual) {
                    continue;
                }
                if (!$customerHasAddress) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['shipping-address-fieldset']['children'][$attribute->getAttributeCode()] = [
                        'component' => $types[$attribute->getFrontendInput()],
                        'config' => $this->returnConfig('shippingAddress', $elementTmpl, $attribute),
                        'dataScope' => 'shippingAddress.bss_custom_field[' . $attribute->getAttributeCode() . ']',
                        'label' => $label,
                        'options' => $options,
                        'caption' => __('Please select'),
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'validation' => $validation,
                        'sortOrder' => $attribute->getSortOrder() + 200,
                        'id' => 'bss_custom_field[' . $attribute->getAttributeCode() . ']',
                        'default' => $default,
                    ];
                } else {
                    $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                    ['shippingAddress']['children']['before-form']['children']['before-form-child']['children'][$attribute->getAttributeCode()] = [
                        'component' => $types[$attribute->getFrontendInput()],
                        'config' => $this->returnConfig('shippingAddressLogin', $elementTmpl, $attribute),
                        'dataScope' => 'shippingAddressLogin.bss_custom_field[' . $attribute->getAttributeCode() . ']',
                        'label' => $label,
                        'options' => $options,
                        'caption' => __('Please select'),
                        'provider' => 'checkoutProvider',
                        'visible' => true,
                        'validation' => $validation,
                        'sortOrder' => $attribute->getSortOrder() + 200,
                        'id' => 'bss_custom_field[' . $attribute->getAttributeCode() . ']',
                        'default' => $default,
                    ];
                }
            }

            if ($attribute->getShowInShipping() == self::DISPLAY_SHIPPING_METHOD) {
                if ($cartVirtual) {
                    continue;
                }
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['before-shipping-method-form']['children']['before-shipping-method-form-child']['children'][$attribute->getAttributeCode()] = [
                    'component' => $types[$attribute->getFrontendInput()],
                    'config' => $this->returnConfig('shippingAddressLogin', $elementTmpl, $attribute),
                    'dataScope' => 'shippingAddressLogin.bss_custom_field[' . $attribute->getAttributeCode() . ']',
                    'label' => $label,
                    'options' => $options,
                    'caption' => __('Please select'),
                    'provider' => 'checkoutProvider',
                    'visible' => true,
                    'validation' => $validation,
                    'sortOrder' => $attribute->getSortOrder() + 200,
                    'id' => 'bss_custom_field[' . $attribute->getAttributeCode() . ']',
                    'default' => $default,
                ];
            }
            //show in payment & review
            if ($attribute->getShowInShipping() == self::DISPLAY_REVIEW_PAYMENT) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['beforeMethods']['children'][$attribute->getAttributeCode()] = [
                    'component' => $types[$attribute->getFrontendInput()],
                    'config' => [
                        'customScope' => 'paymentAftermethods',
                        'template' => 'ui/form/field',
                        'elementTmpl' => $elementTmpl[$attribute->getFrontendInput()],
                        'id' => $attribute->getAttributeCode()
                    ],
                    'options' => $options,
                    'caption' => __('Please select'),
                    'dataScope' => 'paymentAftermethods.' . $name,
                    'label' => $label,
                    'provider' => 'checkoutProvider',
                    'visible' => true,
                    'validation' => $validation,
                    'sortOrder' => $attribute->getSortOrder() + 200,
                    'id' => 'bss_custom_field[' . $attribute->getAttributeCode() . ']',
                    'default' => $default,
                ];
            }
        }
        return $jsLayout;
    }

    /**
     * @param string $key
     * @param array $elementTmpl
     * @param Attribute $attribute
     * @return array
     */
    private function returnConfig($key, $elementTmpl, $attribute)
    {
        return [
            'customScope' => $key,
            'template' => 'ui/form/field',
            'elementTmpl' => $elementTmpl[$attribute->getFrontendInput()],
            'id' => $attribute->getAttributeCode(),
            'rows' => 5
        ];
    }

    /**
     * @return array
     */
    private function setTypes()
    {
        return [
            'text' => 'Magento_Ui/js/form/element/abstract',
            'textarea' => 'Magento_Ui/js/form/element/textarea',
            'select' => 'Magento_Ui/js/form/element/select',
            'boolean' => 'Magento_Ui/js/form/element/select',
            'dropdown' => 'Magento_Ui/js/form/element/select',
            'multiselect' => 'Bss_CheckoutCustomField/js/form/element/checkboxes',
            'date' => 'Bss_CheckoutCustomField/js/form/element/date'
        ];
    }

    /**
     * @return array
     */
    private function setElementTmpl()
    {
        return [
            'text' => 'ui/form/element/input',
            'textarea' => 'ui/form/element/textarea',
            'select' => 'Bss_CheckoutCustomField/form/element/radio',
            'boolean' => 'ui/form/element/select',
            'dropdown' => 'ui/form/element/select',
            'multiselect' => 'Bss_CheckoutCustomField/form/element/checkboxes',
            'date' => 'ui/form/element/date'
        ];
    }

    /**
     * @return int|null
     */
    private function getSessionCustomerId()
    {
        if ($this->customerSession->create()->getCustomerId()) {
            return $this->customerSession->create()->getCustomerId();
        }
        return 0;
    }

    /**
     * @param Attribute $attribute
     * @return array
     */
    private function setVarValidation($attribute)
    {
        $validation = [];
        if ($attribute->getIsRequired() == 1) {
            if ($attribute->getFrontendInput() == 'multiselect') {
                $validation['validate-one-required'] = true;
                $validation['required-entry'] = true;
            } else {
                $validation['required-entry'] = true;
            }
        }
        /* make po field as mandatory for some customer based on validate user api response */
        $customerSession = $this->customerSession->create();
        if ($customerSession->getCustomerId()) {
            if ($attribute->getAttributeCode() == 'purchase_order_number' && $customerSession->getCustomData()['requirePO'] == 'yes') {
                $validation['required-entry'] = true;
            }
        }
        $validation[$attribute->getFrontendClass()] = true;

        return $validation;
    }

    /**
     * @param $attribute
     * @return array|string
     */
    private function setVarDefault($attribute)
    {
        if ($attribute->getFrontendInput() == 'dropdown' || $attribute->getFrontendInput() == 'select' || $attribute->getFrontendInput() == 'multiselect') {
            $default = $this->attributeOption->getOptions($attribute->getAttributeId());
            $default = $this->attributeOption->getDefaultValue($default[0]);
        } else {
            if ($attribute->getFrontendInput() == 'date') {
                $default = $this->formatDate($attribute->getDefaultValue());
            } else {
                $default = $attribute->getDefaultValue();
            }
        }
        return $default;
    }

    /**
     * @param Attribute $attribute
     * @return string
     */
    private function setVarName($attribute)
    {
        if ($attribute->getFrontendInput() == 'multiselect') {
            $name = 'bss_custom_field[' . $attribute->getAttributeCode() . '][]';
        } else {
            $name = 'bss_custom_field[' . $attribute->getAttributeCode() . ']';
        }
        return $name;
    }

    /**
     * @param Attribute $attribute
     * @return array
     */
    protected function getOptions($attribute)
    {
        if ($attribute->getFrontendInput() == 'date') {
            $options = [
                'dateFormat' => 'M/d/Y',
                "timeFormat" => 'hh:mm',
                "showsTime" => $this->helper->isFormatDate()
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
     * @param string $date
     * @return string
     */
    public function formatDate($date)
    {

        return $this->localeDate->scopeDate(null, $date, false)->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }
}
