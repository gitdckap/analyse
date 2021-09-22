<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\QuickRFQ\Block\Invoice;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Summary extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $themeHelper;
    protected $_registry;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Dckap\Theme\Helper\Data $themeHelper,
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
        $this->pageConfig->getTitle()->set(__('Open Invoices'));
    }

    public function isDisplayed()
    {
        return $this->themeHelper->getViewInvoice();
    }

    public function getDdiInvoices()
    {
        $orderList = $this->_registry->registry('ddi_invoices');
        return $orderList;
    }

    public function getDdiCustLedger()
    {
        $orderList = $this->_registry->registry('ddi_custledger');
        return $orderList;
    }

    public function getHandle()
    {
        $handle = $this->_registry->registry('handle');
        return $handle;
    }

    public function getPayOnline()
    {
        return $this->themeHelper->getPayOnline();
    }
}
