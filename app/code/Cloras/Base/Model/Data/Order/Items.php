<?php

namespace Cloras\Base\Model\Data\Order;

use Cloras\Base\Api\Data\OrderItemsInterface;

class Items implements OrderItemsInterface
{
    private $orders;

    private $customers;

    private $orderFilters;

    private $totalCustomers = 0;

    private $totalOrders = 0;

    public function __construct()
    {
        $this->orders       = [];
        $this->customers    = [];
        $this->orderFilters = [];
        $this->totalCustomers = $this->totalCustomers;
        $this->totalOrders = $this->totalOrders;
    }//end __construct()

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]|null
     */
    public function getOrders()
    {
        return $this->orders;
    }//end getOrders()

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return $this
     */
    public function addOrder($order)
    {
        $this->orders[] = $order;

        return $this;
    }//end addOrder()

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface[]|null
     */
    public function getCustomers()
    {
        return $this->customers;
    }//end getCustomers()

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return $this
     */
    public function addCustomer($customer)
    {
        $this->customers[] = $customer;

        return $this;
    }//end addCustomer()

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface[]|null
     */
    public function getFilterOrders()
    {
        return $this->orderFilters;
    }//end getFilterOrders()

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return $this
     */
    public function addFilterOrders($order)
    {
        $this->orderFilters[] = [
            'order_id'     => $order->getEntityId(),
            'increment_id' => $order->getIncrementId(),
            'p21_order_id' => $order->getExtOrderId(),
            'status'       => $order->getStatus(),
        ];

        return $this;
    }//end addFilterOrders()


    public function getTotalOrders()
    {
        return $this->totalOrders;
    }

    public function setTotalOrders($orderCount)
    {
        $this->totalOrders = $orderCount;
    }

    public function getTotalCustomers()
    {
        return $this->totalCustomers;
    }

    public function setTotalCustomers($customerCount)
    {
        $this->totalCustomers = $customerCount;
    }
}//end class
