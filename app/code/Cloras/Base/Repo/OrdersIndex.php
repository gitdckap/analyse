<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\Data\OrderInterface;
use Cloras\Base\Api\Data\OrderInterfaceFactory;
use Cloras\Base\Api\Data\OrderSearchResultsInterfaceFactory;
use Cloras\Base\Api\OrderIndexRepositoryInterface;
use Cloras\Base\Model\OrdersFactory as OrdersIndexModel;
use Cloras\Base\Model\ResourceModel\Orders as OrdersIndexResource;
use Cloras\Base\Model\ResourceModel\Orders\CollectionFactory as OrdersIndexCollection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SimpleDataObjectConverter;

class OrdersIndex implements OrderIndexRepositoryInterface
{
    private $ordersIndexModel;

    private $ordersIndexResource;

    private $orderIndexCollection;

    private $dataObjectHelper;

    private $simpleDataObjectConverter;

    private $orderSearchResults;

    private $orderInterfaceFactory;

    public function __construct(
        OrdersIndexModel $ordersIndexModel,
        OrdersIndexResource $ordersIndexResource,
        OrdersIndexCollection $orderIndexCollection,
        OrderInterfaceFactory $orderInterfaceFactory,
        OrderSearchResultsInterfaceFactory $orderSearchResults,
        DataObjectHelper $dataObjectHelper,
        SimpleDataObjectConverter $simpleDataObjectConverter
    ) {
        $this->ordersIndexModel          = $ordersIndexModel;
        $this->ordersIndexResource       = $ordersIndexResource;
        $this->orderIndexCollection      = $orderIndexCollection;
        $this->orderInterfaceFactory     = $orderInterfaceFactory;
        $this->dataObjectHelper          = $dataObjectHelper;
        $this->orderSearchResults        = $orderSearchResults;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
    }//end __construct()

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\OrderSearchResultsInterface
     */
    public function getOrderIds($searchCriteria)
    {
        $searchResults = $this->orderSearchResults->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->orderIndexCollection->create();
        $fields     = [];
        $condition  = [];
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField()) {
                    $condition    = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $fields[]     = $filter->getField();
                    $conditions[] = [$condition => $filter->getValue()];
                }
            }
        }

        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }

        $searchResults->setTotalCount($collection->getSize());

        $ordersData = [
            'orders'    => [],
            'customers' => [],
        ];
        $orders     = $collection->getData();

        foreach ($orders as $order) {
            $ordersData['orders'][] = $order['order_id'];
            if (!empty($order['customer_id'])) {
                $ordersData['customers'][] = $order['customer_id'];
            }
        }

        $searchResults->setItems($ordersData);

        return $searchResults;
    }//end getOrderIds()

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\OrderSearchResultsInterface
     */
    public function getOrders($searchCriteria)
    {
        $searchResults = $this->orderSearchResults->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->orderIndexCollection->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField()) {
                    $conditionType = $filter->getConditionType() ?: 'eq';
                    $collection->addFieldToFilter(
                        $filter->getField(),
                        [$conditionType => $filter->getValue()]
                    );
                }
            }
        }

        $searchResults->setTotalCount($collection->getSize());

        $ordersData = [];
        $orders     = $collection->getData();

        foreach ($orders as $order) {
            $orderDTO = $this->orderInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $orderDTO,
                $order,
                '\Cloras\Base\Api\Data\OrderInterface'
            );

            $ordersData[] = $orderDTO;
        }

        $searchResults->setItems($ordersData);

        return $searchResults;
    }//end getOrders()

    /**
     * @param int[]  $orderIndexIds
     * @param string $statusToFilter
     * @param string $status
     */
    public function updateStatuses($orderIndexIds, $statusToFilter, $status)
    {
        $condition    = '`order_id` in (' . implode(',', $orderIndexIds) . ')';
        $status_value = "'" . implode("','", $statusToFilter) . "'";
        $condition   .= " and `status` in ($status_value)";
        $this->orderIndexCollection->create()->updateStatusRecords($condition, ['status' => $status]);
    }//end updateStatuses()

    /**
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function save(OrderInterface $order)
    {
        $orderModel = $this->ordersIndexModel->create();

        if ($order->getId()) {
            $orderModel->setId($order->getId());
        }

        $orderModel->setCustomerId($order->getCustomerId());
        $orderModel->setOrderId($order->getOrderId());
        $orderModel->setStatus($order->getStatus());
        $orderModel->setState($order->getState());

        $this->ordersIndexResource->save($orderModel);

        return $order;
    }//end save()

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\OrderSearchResultsInterface
     */
    public function getOrderFilters($searchCriteria)
    {
        $searchResults = $this->orderSearchResults->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->orderIndexCollection->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField()) {
                    $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $collection->addFieldToFilter(
                        $filter->getField(),
                        [$conditionType => $filter->getValue()]
                    );
                }
            }
        }

        $searchResults->setTotalCount($collection->getSize());

        $ordersData = [];
        $orders     = $collection->getData();

        $searchResults->setItems($orders);

        return $searchResults;
    }//end getOrderFilters()

    public function deleteOrderById($orderId)
    {
        if ($orderId) {
            $this->orderIndexCollection->create()->deleteOrderIndex($orderId);
        }
    }//end deleteOrderById()
}//end class
