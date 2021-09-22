<?php

namespace Dckap\Checkout\Controller\Payinvoice;

class Transaction extends \Magento\Framework\App\Action\Action
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
        $params = $this->getRequest()->getParams();
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
