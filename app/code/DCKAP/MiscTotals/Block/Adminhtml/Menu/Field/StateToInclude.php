<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Block\Adminhtml\Menu\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class StateToInclude extends AbstractFieldArray
{
    /**
     * {@inheritdoc}
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'state',
            [
                'label' => __('State'),
                'size' => '200px',
                'class' => 'required-entry'
            ]
        );

        $this->addColumn(
            'amount',
            [
                'label' => __('Amount'),
                'size' => '200px',
                'class' => 'required-entry'
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add State');
    }
}
