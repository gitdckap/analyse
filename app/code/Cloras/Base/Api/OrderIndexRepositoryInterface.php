<?php

namespace Cloras\Base\Api;

use Cloras\Base\Api\Data\OrderInterface;

interface OrderIndexRepositoryInterface
{

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\OrderSearchResultsInterface
     */
    public function getOrderIds($searchCriteria);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\OrderSearchResultsInterface
     */
    public function getOrders($searchCriteria);

    /**
     * @param int[]  $customerIndexIds
     * @param string $statusToFilter
     * @param string $status
     */
    public function updateStatuses($customerIndexIds, $statusToFilter, $status);

    /**
     * @param \Cloras\Base\Api\Data\OrderInterface $order
     *
     * @return \Cloras\Base\Api\Data\OrderInterface
     */
    public function save(OrderInterface $order);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\OrderSearchResultsInterface
     */
    public function getOrderFilters($searchCriteria);
}//end interface
