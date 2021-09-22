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
namespace Bss\CheckoutCustomField\Block\Adminhtml;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class CustomField extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;

    /**
     * Metadata form factory
     *
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_metadataFormFactory;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $_extensibleDataObjectConverter;

    /**
     * CustomField constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Bss\CheckoutCustomField\Helper\HelperModel $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Bss\CheckoutCustomField\Helper\HelperModel $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $storeIdBackend = $this->helper->returnQuoteFactory()->create()->getStoreId();
        return $this->helper->getHelperData()->getTitle($storeIdBackend);
    }

    /**
     * Prepare Form and add elements to form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $fieldset = $this->_form->addFieldset(
            'advanced_fieldset',
            ['legend' => '', 'collapsable' => false]
        );

        $types = [
            'text' => 'text',
            'textarea' => 'textarea',
            'select' => 'select',
            'dropdown' => 'select',
            'boolean' => 'select',
            'multiselect' => 'multiselect',
            'date' => 'date'
        ];

        $attributes = $this->helper->getModelAttribute()->getCustomFieldCreateOrder();
        $dateFormat = $this->_localeDate->getDateFormatWithLongYear();
        $timeFormat = $this->helper->getHelperData()->isFormatDate() ? $this->_localeDate->getTimeFormat(
            $this->helper->getHelperData()->IntlDateFormatterShort()
        ) : '';
        foreach ($attributes as $attribute) {
            $storeId = $this->_sessionQuote->getStore()->getId();
            $stores = explode(',', $attribute->getStoreId());
            if (!in_array($storeId, $stores)) {
                continue;
            }
            $validateClass = $attribute->getFrontendClass();
            $required = false;
            if ($attribute->getIsRequired() == 1) {
                $required = true;
            }
            if ($attribute->getFrontendInput() == 'boolean') {
                $options = [
                    ['value' => '0', 'label' => __('No')],
                    ['value' => '1', 'label' => __('Yes')]
                ];
            } else {
                $options = $this->helper->getModelAttributeOption()->getAttributeOptions($attribute->getAttributeId());
                if ($attribute->getFrontendInput() == "select" || $attribute->getFrontendInput() == "dropdown") {
                    array_unshift($options, ["value" => "", "label" => __('-- Please Select --') ]);
                }
            }

            $fieldset->addField(
                $attribute->getAttributeCode(),
                $types[$attribute->getFrontendInput()],
                [
                    'name' => 'bss_customField[' . $attribute->getAttributeCode() . ']',
                    'label' => $attribute->getBackendLabel(),
                    'title' => $attribute->getBackendLabel(),
                    'value' => $attribute->getDefaultValue(),
                    'values' => $options,
                    'required' => $required,
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,
                    'class' => $validateClass
                ]
            );
        }

        $this->_form->setValues($this->getFormValues());
        $this->setForm($this->_form);
        return $this;
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues()
    {
        $attributes = $this->helper->getModelAttribute()->getCustomFieldChekout();
        $data = [];
        foreach ($attributes as $attribute) {
            if ($this->isCustomFieldChekoutOrder() &&
                $this->helper->getHelperData()->isValueOrderByAttributeCode(
                    $attribute->getAttributeCode(),
                    $this->isCustomFieldChekoutOrder()
                )
            ) {
                $default = $this->helper
                    ->getHelperData()
                    ->isValueOrderByAttributeCode($attribute->getAttributeCode(), $this->isCustomFieldChekoutOrder());
            } elseif ($attribute->getFrontendInput() == 'dropdown'
                || $attribute->getFrontendInput() == 'select'
                || $attribute->getFrontendInput() == 'multiselect'
            ) {
                $default = $this->helper->getModelAttributeOption()->getOptions($attribute->getAttributeId());
                $default = $this->helper->getModelAttributeOption()->getDefaultValue($default[0]);
            } else {
                $default = $attribute->getDefaultValue();
            }
            $data[$attribute->getAttributeCode()] = $default;
        }
        return $data;
    }

    /**
     * @return bool
     */
    public function hasCustomField()
    {
        $attributes = $this->helper->getModelAttribute()->getCustomFieldCreateOrder();
        foreach ($attributes as $attribute) {
            $storeId = $this->_sessionQuote->getStore()->getId();
            $stores = explode(',', $attribute->getStoreId());
            if (!in_array($storeId, $stores)) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function isCustomFieldChekoutOrder()
    {
        if ($this->_getSession()->getOrder()) {
            return $this->_getSession()->getOrder()->getBssCustomfield();
        }
        return false;
    }
}
