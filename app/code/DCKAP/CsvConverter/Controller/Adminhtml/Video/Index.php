<?php

namespace Dckap\CsvConverter\Controller\Adminhtml\Video;

/**
 * Class Index
 * @package Dckap\CsvConverter\Controller\Adminhtml\Video
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
