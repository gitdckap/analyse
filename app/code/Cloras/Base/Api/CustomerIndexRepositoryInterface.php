<?php

namespace Cloras\Base\Api;

use Cloras\Base\Api\Data\CustomerInterface;

interface CustomerIndexRepositoryInterface
{

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function getCustomerIds($searchCriteria);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function getCustomers($searchCriteria);

    /**
     * @param int[]  $customerIndexIds
     * @param string $statusToFilter
     * @param string $status
     */
    public function updateStatuses($customerIndexIds, $statusToFilter, $status);

    /**
     * @param \Cloras\Base\Api\Data\CustomerInterface $customer
     *
     * @return \Cloras\Base\Api\Data\CustomerInterface
     */
    public function save(CustomerInterface $customer);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function getCustomerFilters($searchCriteria);
}//end interface
