<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace DCKAP\QuickRFQ\Block\Invoice;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $themeHelper;
    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \DCKAP\Theme\Helper\Data $themeHelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->themeHelper = $themeHelper;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Invoice List'));
    }

    public function isDisplayed()
    {
        return $this->themeHelper->getViewInvoice();
    }

    public function getDdiOrders()
    {
        $orderList = $this->_registry->registry('ddi_invoices');
        return $orderList;
    }

    public function getHandle()
    {
        $handle = $this->_registry->registry('handle');
        return $handle;
    }

}
