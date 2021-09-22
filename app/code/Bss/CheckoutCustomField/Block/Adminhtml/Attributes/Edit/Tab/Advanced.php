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
namespace Bss\CheckoutCustomField\Block\Adminhtml\Attributes\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\App\ObjectManager;
use Bss\CheckoutCustomField\Model\Config\Source\ShowCheckout;
use Bss\CheckoutCustomField\Model\Config\Source\FrontendClass;
use Bss\CheckoutCustomField\Model\Config\Source\ShowGird;

class Advanced extends Generic
{
    /**
     * FrontendClass
     *
     * @var FrontendClass
     */
    protected $frontendClass;

    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var showCheckout
     */
    protected $showCheckout;

    /**
     * @var ShowGird
     */
    protected $showGird;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param Yesno $yesNo
     * @param Data $eavData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Yesno $yesNo,
        FrontendClass $frontendClass,
        ShowCheckout $showCheckout,
        ShowGird $showGird,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->frontendClass = $frontendClass;
        $this->showCheckout = $showCheckout;
        $this->showGird = $showGird;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Advanced Attribute Properties'), 'collapsable' => true]
        );

        $yesno = $this->yesNo->toOptionArray();

        $showCheckout = $this->showCheckout->toOptionArray();

        $showGird = $this->showGird->toOptionArray();

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order')
            ]
        );

        $fieldset->addField(
            'default_value_text',
            'text',
            [
                'name' => 'default_value_text',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue()
            ]
        );

        $fieldset->addField(
            'default_value_yesno',
            'select',
            [
                'name' => 'default_value_yesno',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $yesno,
                'value' => $attributeObject->getDefaultValue()
            ]
        );
        
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue(),
                'date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT
            ]
        );

        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            [
                'name' => 'default_value_textarea',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue()
            ]
        );

        $fieldset->addField(
            'frontend_class',
            'select',
            [
                'name' => 'frontend_class',
                'label' => __('Input Validation for Store Owner'),
                'title' => __('Input Validation for Store Owner'),
                'values' => $this->frontendClass->toOptionArray()
            ]
        );

        $fieldset->addField(
            'visible_frontend',
            'select',
            [
                'name' => 'visible_frontend',
                'label' => __('Frontend Visibility'),
                'title' => __('Frontend Visibility'),
                'values' => $yesno,
                'value' => $attributeObject->getData('visible_frontend') ?: 1,
            ]
        );

        $fieldset->addField(
            'visible_backend',
            'select',
            [
                'name' => 'visible_backend',
                'label' => __('Backend Visibility'),
                'title' => __('Backend Visibility'),
                'values' => $yesno,
                'value' => $attributeObject->getData('visible_backend') ?: 1,
            ]
        );

        $fieldset->addField(
            'show_in_shipping',
            'select',
            [
                'name' => 'show_in_shipping',
                'label' => __('Show in Checkout'),
                'title' => __('Show in Checkout'),
                'values' => $showCheckout,
                'value' => $attributeObject->getData('show_in_shipping') ?: 0,
            ]
        );

        $fieldset->addField(
            'show_gird',
            'select',
            [
                'name' => 'show_gird',
                'label' => __('Add to Order Grid'),
                'title' => __('Add to Order Grid'),
                'values' => $showGird,
                'value' => $attributeObject->getData('show_gird') ?: 1,
            ]
        );

        $fieldset->addField(
            'show_in_order',
            'select',
            [
                'name' => 'show_in_order',
                'label' => __('Add to Order Detail'),
                'title' => __('Add to Order Detail'),
                'values' => $yesno,
                'value' => $attributeObject->getData('show_in_order') ?: 1,
            ]
        );

        $fieldset->addField(
            'show_in_pdf',
            'select',
            [
                'name' => 'show_in_pdf',
                'label' => __('Add to PDF Documents'),
                'title' => __('Add to PDF Documents'),
                'values' => $yesno,
                'value' => $attributeObject->getData('show_in_pdf') ?: 1,
            ]
        );

        $fieldset->addField(
            'show_in_email',
            'select',
            [
                'name' => 'show_in_email',
                'label' => __('Add to Email'),
                'title' => __('Add to Email'),
                'values' => $yesno,
                'value' => $attributeObject->getData('show_in_email') ?: 1,
            ]
        );

        if ($attributeObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
        }

        $this->_eventManager->dispatch('product_attribute_form_build', ['form' => $form]);
        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
