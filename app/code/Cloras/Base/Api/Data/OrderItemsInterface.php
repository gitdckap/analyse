<?php

namespace Cloras\Base\Api\Data;

interface OrderItemsInterface
{
    const CUSTOMERS = 'customers';

    const ORDERS = 'orders';

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]|null
     */
    public function getCustomers();

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function addCustomer($customer);

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]|null
     */
    public function getOrders();

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function addOrder($order);

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]|null
     */
    public function getFilterOrders();

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function addFilterOrders($order);

    /**
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function getTotalOrders();

    /**
     * @param  int
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function setTotalOrders($orderCount);

    /**
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function getTotalCustomers();

    /**
     * @param  int
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function setTotalCustomers($customerCount);
}//end interface
