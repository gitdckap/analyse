<?php

namespace Cloras\Base\Plugin\Order;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Cloras\Base\Api\Data\OrderInterfaceFactory;
use Cloras\Base\Model\Data\OrderDTO;
use Cloras\Base\Repo\OrdersIndex;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class PlaceAfterPlugin
{

    private $registry;

    private $ordersIndex;

    private $orderInterfaceFactory;

    private $customerRepository;

    private $customerInterfaceFactory;

    public function __construct(
        Registry $registry,
        OrdersIndex $ordersIndex,
        OrderInterfaceFactory $orderInterfaceFactory,
        CustomerIndexRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry                 = $registry;
        $this->ordersIndex              = $ordersIndex;
        $this->orderInterfaceFactory    = $orderInterfaceFactory;
        $this->customerRepository       = $customerRepository;
        $this->storeManager             = $storeManager;
    }//end __construct()

    /**
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface
     * @param \Magento\Sales\Model\Order\Interceptor $order
     * @return $order
     */
    public function afterPlace(
        \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface,
        $order
    ) {
        $orderId = $order->getId();
        if (strtolower($order->getStatus()) != 'canceled') {
            if (!$this->registry->registry('ignore_order_update')) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();

                $orderIndex = $this->orderInterfaceFactory->create();
                if ($order->getCustomerIsGuest()) {
                    $orderIndex->setCustomerId(0);
                    $customerId = 0;
                } else {
                    $orderIndex->setCustomerId($order->getCustomerId());
                    $customerId = $order->getCustomerId();
                }

                if (empty($this->customerRepository->getP21CustomerId($customerId))) {
                    $this->customerRepository->saveCustomerIndex(
                        $customerId,
                        $websiteId
                    );
                }

                $orderIndex->setOrderId($order->getId());
                $orderIndex->setStatus(OrderDTO::STATUS_PENDING);
                $orderIndex->setState(OrderDTO::STATE_NEW);
                $this->ordersIndex->save($orderIndex);
            }//end if
        }
        

        return $order;
    }
}
