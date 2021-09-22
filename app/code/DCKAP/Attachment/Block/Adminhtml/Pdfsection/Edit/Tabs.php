<?php
namespace Dckap\Attachment\Block\Adminhtml\Pdfsection\Edit;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('pdfsection_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Section Information'));
    }
}
