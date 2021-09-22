<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\Checkout\Block\Index;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Index extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Invoice Payment'));
    }

    public function getStaticUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC);
    }
}
