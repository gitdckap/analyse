<?php

namespace Cloras\Base\Observer;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class CustomerIndex implements ObserverInterface
{
    private $customerIndexRepository;

    private $registry;
    protected $_request;

    public function __construct(
        CustomerIndexRepositoryInterface $customerIndexRepository,
        Registry $registry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->customerIndexRepository  = $customerIndexRepository;
        $this->registry                 = $registry;
        $this->_request = $request;
    }//end __construct()

    public function execute(Observer $observer)
    {
        $savedCustomer = $observer->getEvent()->getCustomerDataObject();
        $postParents = $this->_request->getPost();
        $prevCustomer  = $observer->getEvent()->getOrigCustomerDataObject();
        $customerId = $savedCustomer->getId();
        $websiteId = $savedCustomer->getWebsiteId();
    
        if (!isset($postParents['existing_customer'])) {
            $this->customerIndexRepository->saveCustomerIndex($customerId, $websiteId);
        }
    }//end execute()
}//end class
