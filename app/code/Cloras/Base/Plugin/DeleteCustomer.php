<?php

namespace Cloras\Base\Plugin;

use Cloras\Base\Api\Data\CustomerInterface;

class DeleteCustomer
{
    public function __construct(
        \Cloras\Base\Model\ResourceModel\Customers\CollectionFactory $customerIndexCollection,
        \Cloras\Base\Model\ResourceModel\Orders\CollectionFactory $orderIndexCollection
    ) {
        $this->customerIndexCollection = $customerIndexCollection;
        $this->orderIndexCollection = $orderIndexCollection;
    }
    
    public function aroundDeleteById(
        \Magento\Customer\Model\ResourceModel\CustomerRepository $subject,
        \Closure $proceed,
        $customerId
    ) {
        if ($subject) {
            if ($customerId) {
                $condition    = '`customer_id` in (' . $customerId . ')';
                $condition   .= " AND `status` != '".CustomerInterface::STATUS_COMPLETED."'";
                $state = CustomerInterface::STATE_DELETE;
            
                $this->customerIndexCollection->create()->updateStatusRecords($condition, ['state' => $state]);

                $this->orderIndexCollection->create()->updateStatusRecords($condition, ['state' => $state]);
            }
        
            $result = $proceed($customerId);
            return $result;
        }
    }
}
