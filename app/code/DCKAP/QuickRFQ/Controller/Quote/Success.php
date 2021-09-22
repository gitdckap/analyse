<?php

namespace Dckap\QuickRFQ\Controller\Quote;

class Success extends \Magento\Framework\App\Action\Action
{

    private $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $this->_redirect('customer/account/login');
        }
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}