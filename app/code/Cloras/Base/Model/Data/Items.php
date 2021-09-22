<?php

namespace Cloras\Base\Model\Data;

use Cloras\Base\Api\Data\ItemsInterface;

class Items implements ItemsInterface
{
    private $newCustomers;

    private $updatedCustomers;

    private $totalCustomers = 0;

    private $newCustomerCount = 0;

    private $updatedCustomerCount = 0;

    public function __construct()
    {
        $this->newCustomers     = [];
        $this->updatedCustomers = [];
        $this->totalCustomers = $this->totalCustomers;
        $this->newCustomerCount = $this->newCustomerCount;
        $this->updatedCustomerCount = $this->updatedCustomerCount;
    }//end __construct()

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]|null
     */
    public function getNewCustomers()
    {
        return $this->newCustomers;
    }//end getNewCustomers()

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return $this
     */
    public function addNewCustomer($customer)
    {
        $this->newCustomers[] = $customer;

        return $this;
    }//end addNewCustomer()

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]|null
     */
    public function getUpdatedCustomers()
    {
        return $this->updatedCustomers;
    }//end getUpdatedCustomers()

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return $this
     */
    public function addUpdatedCustomer($customer)
    {
        $this->updatedCustomers[] = $customer;

        return $this;
    }//end addUpdatedCustomer()


    public function getTotalCustomers()
    {
        return $this->totalCustomers;
    }

    public function setTotalCustomers($customerCount)
    {
        $this->totalCustomers = $customerCount;
    }


    public function getNewCustomerCount()
    {
        return $this->newCustomerCount;
    }

    public function setNewCustomerCount($newCustomerCount)
    {
        $this->newCustomerCount = $newCustomerCount;
    }

    public function getUpdatedCustomerCount()
    {
        return $this->updatedCustomerCount;
    }

    public function setUpdatedCustomerCount($updateCustomerCount)
    {
        $this->updatedCustomerCount = $updateCustomerCount;
    }
}//end class
