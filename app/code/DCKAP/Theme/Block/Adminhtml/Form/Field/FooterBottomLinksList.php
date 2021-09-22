<?php

namespace Dckap\Theme\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class FooterBottomLinksList extends AbstractFieldArray
{

    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn('footer_bottom_link_name', ['label' => __('Name'), 'class' => 'validate-no-html-tags']);
        $this->addColumn('footer_bottom_link_href', ['label' => __('Link'), 'class' => 'validate-no-html-tags']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Link');
    }
}
