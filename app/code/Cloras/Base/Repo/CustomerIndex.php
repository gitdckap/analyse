<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Cloras\Base\Api\Data\CustomerInterface;
use Cloras\Base\Api\Data\CustomerInterfaceFactory;
use Cloras\Base\Api\Data\CustomerSearchResultsInterfaceFactory;
use Cloras\Base\Model\CustomersFactory as CustomerIndexModel;
use Cloras\Base\Model\Data\CustomerDTO as Customer;
use Cloras\Base\Model\ResourceModel\Customers as CustomerIndexResource;
use Cloras\Base\Model\ResourceModel\Customers\CollectionFactory as CustomerIndexCollection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Registry;

class CustomerIndex implements CustomerIndexRepositoryInterface
{
    private $customerIndexModel;

    private $customerIndexResource;

    private $customerIndexCollection;

    private $dataObjectHelper;

    private $simpleDataObjectConverter;

    private $customerSearchResults;

    private $customerInterfaceFactory;

    /**
     * CustomerIndex constructor.
     *
     * @param CustomerIndexModel                    $customerIndexModel
     * @param CustomerIndexResource                 $customerIndexResource
     * @param CustomerIndexCollection               $customerIndexCollection
     * @param CustomerInterfaceFactory              $customerInterfaceFactory
     * @param CustomerSearchResultsInterfaceFactory $customerSearchResults
     * @param DataObjectHelper                      $dataObjectHelper
     * @param SimpleDataObjectConverter             $simpleDataObjectConverter
     */
    public function __construct(
        CustomerIndexModel $customerIndexModel,
        CustomerIndexResource $customerIndexResource,
        CustomerIndexCollection $customerIndexCollection,
        CustomerInterfaceFactory $customerInterfaceFactory,
        CustomerSearchResultsInterfaceFactory $customerSearchResults,
        DataObjectHelper $dataObjectHelper,
        SimpleDataObjectConverter $simpleDataObjectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Model\Customer $customerModel,
        Registry $registry
    ) {
        $this->customerIndexModel        = $customerIndexModel;
        $this->customerIndexResource     = $customerIndexResource;
        $this->customerIndexCollection   = $customerIndexCollection;
        $this->customerInterfaceFactory  = $customerInterfaceFactory;
        $this->dataObjectHelper          = $dataObjectHelper;
        $this->customerSearchResults     = $customerSearchResults;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
        $this->searchCriteriaBuilder    = $searchCriteriaBuilder;
        $this->customerModel            = $customerModel;
        $this->registry                 = $registry;
    }//end __construct()

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function getCustomerIds($searchCriteria)
    {
        $searchResults = $this->customerSearchResults->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->customerIndexCollection->create();
        $fields     = [];
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

        $customersData = [
            'all'    => [],
            'new'    => [],
            'update' => [],
        ];
        $customers     = $collection->getData();

        foreach ($customers as $customer) {
            $customersData['all'][] = $customer['customer_id'];
            if ($customer['state'] == Customer::STATE_NEW) {
                $customersData['new'][] = $customer['customer_id'];
            } else {
                $customersData['update'][] = $customer['customer_id'];
            }
        }

        $searchResults->setItems($customersData);

        return $searchResults;
    }//end getCustomerIds()

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function getCustomers($searchCriteria)
    {
        $searchResults = $this->customerSearchResults->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->customerIndexCollection->create();

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

        $customersData = [];
        $customers     = $collection->getData();

        foreach ($customers as $customer) {
            $customerDTO = $this->customerInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $customerDTO,
                $customer,
                '\Cloras\Base\Api\Data\CustomerInterface'
            );

            $customersData[] = $customerDTO;
        }

        $searchResults->setItems($customersData);

        return $searchResults;
    }//end getCustomers()

    /**
     * @param int[]  $customerIndexIds
     * @param string $statusToFilter
     * @param string $status
     */
    public function updateStatuses($customerIndexIds, $statusToFilter, $status)
    {
        $condition    = '`customer_id` in (' . implode(',', $customerIndexIds) . ')';
        $status_value = "'" . implode("','", $statusToFilter) . "'";
        $condition   .= " and `status` in ($status_value)";
        $this->customerIndexCollection->create()->updateStatusRecords($condition, ['status' => $status]);
    }//end updateStatuses()

    /**
     * @param CustomerInterface $customer
     */
    public function save(CustomerInterface $customer)
    {
        $customers = $this->customerIndexCollection->create();

        $customerIndexCollection = $customers->addFieldToFilter('customer_id', $customer->getCustomerId());

        $customerIndexCount = count($customerIndexCollection);
        if ($customerIndexCount != 0) {
            $customerIds[] = $customer->getCustomerId();
            $condition     = '`customer_id` in (' . implode(',', $customerIds) . ')';
            if (array_key_exists('0', $customerIndexCollection->getData())) {
                $customerIndexStatus = $customerIndexCollection->getData()[0]['status'];
                $customerIndex       = $this->customerIndexCollection->create();
                $customerIndex->updateStatusRecords(
                    $condition,
                    [
                        'status' => $customer->getStatus(),
                        'state'  => $customer->getState(),
                    ]
                );
            }
        } else {
            $customerModel = $this->customerIndexModel->create();
            $customerModel->setCustomerId($customer->getCustomerId());
            $customerModel->setWebsiteId($customer->getWebsiteId());
            $customerModel->setStatus($customer->getStatus());
            $customerModel->setState($customer->getState());

            $this->customerIndexResource->save($customerModel);
        }//end if
    }//end save()

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function getCustomerFilters($searchCriteria)
    {
        $searchResults = $this->customerSearchResults->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->customerIndexCollection->create();

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

        $customersData = [];
        $customers     = $collection->getData();

        $searchResults->setItems($customers);

        return $searchResults;
    }//end getCustomerFilters()

    public function saveCustomerIndex($customerId, $websiteId)
    {
        // update customer table if order place using existing Customer
        if (!$this->registry->registry('ignore_customer_update')) {
            $customer = $this->customerInterfaceFactory->create();
            $customer->setCustomerId($customerId);
            $customer->setWebsiteId($websiteId);
            $customer->setStatus(Customer::STATUS_PENDING);
            $customer->setState(Customer::STATE_NEW);

            $this->save($customer);
        }
    }

    /**
     * @param $customer
     *
     * @return boolean
     */
    private function checkInQueue($customerId, $websiteId)
    {
        // return true for guest customer
        if ($customerId == 0) {
            return true;
        } else {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)
                ->addFilter('website_id', $websiteId)
                ->create();

            return $this->getCustomerFilters($searchCriteria)->getTotalCount() ? false : true;
        }
    }//end checkInQueue()

    public function getP21CustomerId($customerId)
    {
        $p21CustomerId = "";
        if ($customerId != 0) {
            $customers = $this->customerModel->load($customerId);

            if (is_array($customers->getData())) {
                if (array_key_exists('cloras_p21_customer_id', $customers->getData())) {
                    $p21CustomerId = $customers->getData()['cloras_p21_customer_id'];
                }
            }
        }

        return $p21CustomerId;
    }
}//end class
