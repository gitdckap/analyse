<?php

namespace Dckap\Checkout\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addNotice(__("Login Required."));
            $loginUrl = $this->_url->getUrl('customer/account/login');
            return $resultRedirect->setPath($loginUrl);
        }

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
