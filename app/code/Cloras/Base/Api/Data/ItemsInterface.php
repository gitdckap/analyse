<?php

namespace Cloras\Base\Api\Data;

interface ItemsInterface
{
    const NEW_CUSTOMERS = 'new';

    const UPDATED_CUSTOMERS = 'update';

    const TOTAL_CUSTOMERS = 'total';

    const NEW_CUSTOMERS_COUNT = 'new_customers_count';

    const UPDATED_CUSTOMERS_COUNT = 'updated_customers_count';

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]|null
     */
    public function getNewCustomers();

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function addNewCustomer($customer);

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]|null
     */
    public function getUpdatedCustomers();

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function addUpdatedCustomer($customer);

    /**
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function getTotalCustomers();

    /**
     * @param  int
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function setTotalCustomers($customerCount);

    /**
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function getUpdatedCustomerCount();

    /**
     * @param  int
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function setUpdatedCustomerCount($updatedCustomerCount);

    /**
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function getNewCustomerCount();

    /**
     * @param  int
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function setNewCustomerCount($newCustomerCount);
}//end interface
