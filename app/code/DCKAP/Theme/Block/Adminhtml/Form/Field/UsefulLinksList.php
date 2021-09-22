<?php


namespace Dckap\Theme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class UsefulLinksList extends AbstractFieldArray
{

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('useful_link_name', ['label'=>__('Name'),'class'=>'validate-no-html-tags']);
        $this->addColumn('useful_link_href', ['label'=>__('Link'),'class'=>'validate-no-html-tags']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Link');
    }
}
