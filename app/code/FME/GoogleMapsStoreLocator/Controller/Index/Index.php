<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace FME\GoogleMapsStoreLocator\Controller\Index;

/**
 * Class Index
 * @package FME\GoogleMapsStoreLocator\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
         $this->_view->loadLayout();
         $this->_view->renderLayout();
    }
}
