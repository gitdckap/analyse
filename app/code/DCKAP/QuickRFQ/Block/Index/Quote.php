<?php

namespace Dckap\QuickRFQ\Block\Index;

class Quote extends \Magento\Framework\View\Element\Template
{
    protected $themeHelper;
    protected $customerSession;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Dckap\Theme\Helper\Data $themeHelper,
        \Magento\Customer\Model\SessionFactory $customerSession,
        array $data = []
    ) {
        $this->themeHelper = $themeHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);

    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function isDisplayed()
    {
        return $this->themeHelper->getQuoteOptionView();
    }

    public function isLogIn()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            return true;
        }
        return false;
    }
}