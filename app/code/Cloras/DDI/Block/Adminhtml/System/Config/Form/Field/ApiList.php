<?php

namespace Cloras\DDI\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;

/**
 * Class Type
 *
 * @category  Class
 * @package   Pimgento\Api\Block\Adminhtml\System\Config\Form\Field
 * @author    Agence Dn'D <contact@dnd.fr>
 * @copyright 2018 Agence Dn'D
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.pimgento.com/
 */
class ApiList extends AbstractFieldArray
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
     * Type constructor
     *
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param AttributeHelper $attributeHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->elementFactory  = $elementFactory;
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('service', ['label' => __('Service')]);
        $this->addColumn('token', ['label' => __('Token')]);
        $this->addColumn('status', ['label' => __('Status')]);
        $this->_addAfter       = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return string
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName != 'status') {
            return parent::renderCellTemplate($columnName);
        }
        /** @var array $options */
        $options = [
            '1' => 'Yes',
            '0' => 'No'
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
