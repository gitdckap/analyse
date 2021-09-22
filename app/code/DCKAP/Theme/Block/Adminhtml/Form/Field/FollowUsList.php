<?php

namespace Dckap\Theme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Backend\Block\Template\Context;

class FollowUsList extends AbstractFieldArray
{

    /**
     * This variable contains an ElementFactory
     *
     * @var ElementFactory $elementFactory
     */
    protected $elementFactory;
    /**
     * This variable contains an AttributeHelper
     *
     * @var AttributeHelper $attributeHelper
     */
    protected $attributeHelper;

    /**
     * FollowUsList constructor.
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->elementFactory = $elementFactory;
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('follow_us_type', ['label' => __('Type'),'size' => '125px']);
        $this->addColumn('follow_us_link', ['label' => __('Link'), "class" => "validate-url"]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName != 'follow_us_type') {
            return parent::renderCellTemplate($columnName);
        }
        /** option values needs to be in lowercase as this values becomes CSS classes on the frontend */
        /** @var array $options */
        $options = [
            'facebook' => __('Facebook'),
            'instagram' => __('Instagram'),
            'linkedin' => __('LinkedIn'),
            'twitter' => __('Twitter'),
            'youtube' => __('Youtube'),
            'instagram' => __('Instagram')
        ];
        /** @var AbstractElement $element */
        $element = $this->elementFactory->create('select');
        $element->setForm(
            $this->getForm()
        )->setName(
            $this->_getCellInputElementName($columnName)
        )->setHtmlId(
            $this->_getCellInputElementId('<%- _id %>', $columnName)
        )->setValues(
            $options
        );
        return str_replace("\n", '', $element->getElementHtml());
    }
}
