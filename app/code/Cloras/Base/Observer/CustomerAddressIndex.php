<?php

namespace Cloras\Base\Observer;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class CustomerAddressIndex implements ObserverInterface
{
    private $customerRepository;

    private $registry;

    public function __construct(
        CustomerIndexRepositoryInterface $customerRepository,
        Registry $registry
    ) {
        $this->customerRepository       = $customerRepository;
        $this->registry                 = $registry;
    }

    public function execute(Observer $observer)
    {
        $customer   = $observer->getCustomerAddress()->getCustomer();
        $customerId = 0;
        if ($customer->getId()) {
            $customerId = $customer->getId();
        }

        $websiteId = $customer->getWebsiteId();
    }
}
