<?php

namespace Dckap\Checkout\Controller\Ajax;

class Details extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \DCKAP\Extension\Helper\Data $extensionHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->extensionHelper = $extensionHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = [];
        try {
            $params = $this->getRequest()->getParams();
            $data['call_us'] = $this->extensionHelper->getCallUs();
            $data['store_name'] = $this->extensionHelper->getStoreName();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $resultJson->setData($data);
    }
}
