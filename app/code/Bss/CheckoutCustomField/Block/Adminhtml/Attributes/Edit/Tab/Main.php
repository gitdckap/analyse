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

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Attribute instance
     *
     * @var Attribute
     */
    protected $attribute = null;

    /**
     * @var \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $yesnoFactory;

    /**
     * @var \Bss\CheckoutCustomField\Model\Config\Source
     */
    protected $_inputTypeFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Bss\CheckoutCustomField\Model\Config\Source\Inputtype $inputTypeFactory
     * @param \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Bss\CheckoutCustomField\Model\Config\Source\Inputtype $inputTypeFactory,
        \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->yesnoFactory = $yesnoFactory;
        $this->_inputTypeFactory = $inputTypeFactory;
        $this->propertyLocker = $propertyLocker;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set attribute object
     *
     * @param Attribute $attribute
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAttributeObject($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * Return attribute object
     *
     * @return Attribute
     */
    public function getAttributeObject()
    {
        if (null === $this->attribute) {
            return $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->attribute;
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->getAttributeObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Attribute Properties')]);

        if ($attributeObject->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }

        $this->_addElementTypes($fieldset);

        $yesno = $this->yesnoFactory->create()->toOptionArray();

        $labels = $attributeObject->getFrontendLabel();
        $fieldset->addField(
            'attribute_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default label'),
                'required' => true,
                'value' => is_array($labels) ? $labels[0] : $labels
            ]
        );

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Input Type for Store Owner'),
                'title' => __('Input Type for Store Owner'),
                'value' => 'text',
                'values' => $this->_inputTypeFactory->toOptionArray()
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesno
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

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $attributeObject->getDefaultValue(),
                'date_format' => $dateFormat
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

        if ($attributeObject->getId()) {
            $form->getElement('frontend_input')->setDisabled(1);
        }

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'label' => __('Visibility'),
                    'required' => true,
                    'name' => 'store_id',
                    'values' => $this->systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        }

        foreach ($fieldset->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\AbstractForm $element  */
            if (substr($element->getId(), 0, strlen('default_value')) == 'default_value') {
                $fiedsToRemove[] = $element->getId();
            }
        }
        foreach ($fiedsToRemove as $id) {
            $fieldset->removeField($id);
        }

        // $this->propertyLocker->lock($form);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->_eventManager->dispatch(
            'adminhtml_block_eav_attribute_edit_form_init',
            ['form' => $this->getForm()]
        );
        $this->getForm()->addValues($this->getAttributeObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Processing block html after rendering
     * Adding js block to the end of this block
     *
     * @param   string $html
     * @return  string
     */
    protected function _afterToHtml($html)
    {
        $jsScripts = $this->getLayout()->createBlock(\Magento\Eav\Block\Adminhtml\Attribute\Edit\Js::class)->toHtml();
        return $html . $jsScripts;
    }
}
