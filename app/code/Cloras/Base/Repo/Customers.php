<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Cloras\Base\Api\CustomerInterface;
use Cloras\Base\Api\Data\ItemsInterfaceFactory;
use Cloras\Base\Api\Data\ResultsInterfaceFactory;
use Cloras\Base\Model\Data\CustomerDTO as Customer;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

class Customers implements CustomerInterface
{
    private $customerRepository;

    private $customerIndexRepository;

    private $searchCriteriaBuilder;

    private $itemsFactory;

    private $jsonHelper;

    private $registry;

    private $customerFactory;

    private $customerResource;

    private $clorasHelper;

    private $resultsFactory;

    private $addressInterfaceFactory;

    private $addressFactory;

    private $regionFactory;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        ItemsInterfaceFactory $itemsFactory,
        CustomerFactory $customerFactory,
        Json $jsonHelper,
        Registry $registry,
        CustomerResource $customerResource,
        CustomerIndexRepositoryInterface $customerIndexRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        ResultsInterfaceFactory $resultsFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        CustomerMetadataInterface $customerMetadata,
        AddressMetadataInterface $addressMetadata
    ) {
        $this->customerRepository      = $customerRepository;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->itemsFactory            = $itemsFactory;
        $this->jsonHelper              = $jsonHelper;
        $this->registry                = $registry;
        $this->customerFactory         = $customerFactory;
        $this->customerResource        = $customerResource;
        $this->customerIndexRepository = $customerIndexRepository;
        $this->_filterBuilder          = $filterBuilder;
        $this->regionFactory           = $regionFactory;
        $this->addressFactory          = $addressFactory;
        $this->_filterGroupBuilder     = $filterGroupBuilder;
        $this->_addressFactory         = $addressFactory;
        $this->clorasHelper            = $clorasHelper;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->resultsFactory          = $resultsFactory;
        $this->customerMetadata        = $customerMetadata;
        $this->addressMetadata         = $addressMetadata;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function getCustomers()
    {
    
        $items = $this->itemsFactory->create();

        $filterAttribute['status'] = [
            'Pending',
            'Failed',
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('status', $filterAttribute['status'], 'in')->create();

        $loggedCustomers = $this->customerIndexRepository->getCustomerIds($searchCriteria);

        if ($loggedCustomers->getTotalCount()) {
            $loadedCustomers = [];
            $customersIds    = $loggedCustomers->getItems();

            $customerFilters = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $customersIds['all'], 'in')->create();
            $customers       = $this->customerRepository->getList($customerFilters)->getItems();
            
            $newCustomerCount = 0;
            $updatedCustomerCount = 0;
            $customerData = [];
            foreach ($customers as $customer) {
                $loadedCustomers[] = $customer->getId();
        //$customerData = $this->clorasHelper->customizedCustomers($customer);
                if (in_array($customer->getId(), $customersIds['new'])) {
                    $items->addNewCustomer($customer);
                    $newCustomerCount++;
                } else {
                    $items->addUpdatedCustomer($customer);
                    $updatedCustomerCount++;
                }
            }
            $items->setNewCustomerCount($newCustomerCount);
            $items->setUpdatedCustomerCount($updatedCustomerCount);
            $items->setTotalCustomers(count($customers));

            if (!empty($loadedCustomers)) {
                $customerStatus = [
                    Customer::STATUS_PENDING,
                    Customer::STATUS_FAILED,
                ];
                $this->customerIndexRepository->updateStatuses($loadedCustomers, $customerStatus, Customer::STATUS_PROCESS);
            }
        }//end if

        return $items;
    }//end getCustomers()

    /**
     * @param string $customers
     *
     * @return boolean
     */
    public function updateCustomers($customers)
    {
        try {
            $customers       = $this->jsonHelper->unserialize($customers);
            $syncedCustomers = [];
            $response        = [];
            $customerStatus  = [Customer::STATUS_PROCESS];
            foreach ($customers as $customer) {
                $response[] = $customer;
            }

            // Tell cloras ignore this update
            $this->registry->register('ignore_customer_update', 1);

            // Negative Performance Impact - Load & Save in loop
            foreach ($response[0] as $newCustomer) {
                $syncedCustomers[] = $newCustomer['entity_id'];
                $this->updateCustomerAttribute($newCustomer);
            }

            $customersToBeUpdated = array_merge($syncedCustomers, $response[1]);
            // Updating Customer status to completed (For new Customers)
            if (!empty($customersToBeUpdated)) {
                $this->customerIndexRepository->updateStatuses($customersToBeUpdated, $customerStatus, Customer::STATUS_COMPLETED);
            }

            // Failed Customers
            if (!empty($response[2])) {
                $this->customerIndexRepository->updateStatuses($response[2], $customerStatus, Customer::STATUS_FAILED);
            }

            return true;
        } catch (\Exception $e) {
            //$this->logger->info('Customer Update Error: ' . (array)$e->getMessage());
           
            return false;
        }//end try
    }//end updateCustomers()

    private function updateCustomerAttribute($newCustomer)
    {
        $customer = $this->customerRepository->getById($newCustomer['entity_id']);
        $customer->setCustomAttribute('cloras_p21_customer_id', $newCustomer['customer_id']);
        $customer->setCustomAttribute('cloras_p21_contact_id', $newCustomer['contact_id']);
        $customer->setCustomAttribute('cloras_p21_shipto_id', $newCustomer['shipto_id']);
        $this->customerRepository->save($customer);
    }

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateBillingAddress($data)
    {
        $customersInfo = $this->jsonHelper->unserialize($data);

        $response = [
            'total_count'   => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'failed_ids'    => [],
        ];
        $this->registry->register('ignore_customer_update', 1);

        foreach ($customersInfo as $customerInfo) {
            $response['total_count'] += 1;
            $p21CustomerId            = $customerInfo['customer_id'];

            try {
                $billingAddress = !empty($customerInfo['addresses']) ? $customerInfo['addresses'][0] : null;
                $contacts = !empty($customerInfo['contacts']) ? $customerInfo['contacts'] : null;
                // print_r($contacts);
                
                if ($billingAddress) {
                    $filterByP21CustomerId = $this->searchCriteriaBuilder->addFilter('cloras_p21_customer_id', $p21CustomerId)->create();
                    $customers             = $this->customerRepository->getList($filterByP21CustomerId);

                    if ($customers->getTotalCount()) {
                        $loadedCustomers = $customers->getItems();

                        foreach ($loadedCustomers as $customer) {
                            $this->updateCustomerAddresses($customer, $billingAddress, $contacts);
                        }//end foreach

                        $response['success_count'] += 1;
                    } else {
                        throw new LocalizedException(__('No Matched Customer Found'));
                    }//end if
                } else {
                    throw new LocalizedException(__('No Billing Address Available to Update'));
                }//end if
            } catch (\Exception $e) {
                $response['failure_count']             += 1;
                $response['failed_ids'][$p21CustomerId] = $e->getMessage();
            }//end try
        }//end foreach

        $results = $this->resultsFactory->create();
        $results->setResponse($response);

        return $results;
    }//end updateBillingAddress()

    private function updateCustomerAddresses($customer, $billingAddress, $contacts)
    {
        $addresses = $customer->getAddresses();

        $name = explode(' ', $billingAddress['name'], 2);
        
        $firstname = "";
        $lastname = "";
                            
        if (array_key_exists(0, $name)) {
            $firstname = $name[0];
        }
                            
        if (array_key_exists(1, $name)) {
            $lastname = $name[1];
        }

        $customerRepo = $this->customerRepository->getById($customer->getId());

        list($firstname, $lastname) = $this->getCustomerDetails($contacts, $customerRepo);
        $billingAddress['firstname'] = $firstname;
        $billingAddress['lastname'] = $lastname;
                            
        foreach ($addresses as $address) {
            if ($address->isDefaultBilling()) {
                $billingAddressData = $address;
                $this->updateAddressData($billingAddressData, $billingAddress, 'bill');
            }
       
            if ($address->isDefaultBilling() && $address->isDefaultShipping()) {
                $billingAddressData = $address;
                $this->updateAddressData($billingAddressData, $billingAddress, 'bill');
            }
        }
    }

    private function getCustomerDetails($contacts, $customerRepo)
    {
        $firstname = "";
        $lastname = "";
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                if (array_key_exists('email_address', $contact)) {
                    if ($customerRepo->getEmail() == $contact['email_address']) {
                        $firstname = $contact['first_name'];
                        $lastname = $contact['last_name'];
                    }
                }
            }
        }
        return [
            $firstname,
            $lastname
        ];
    }

    private function updateAddressData($addressData, $billingAddress, $updateTo)
    {
        $addressDetails = $this->addressFactory->create();

        if ($updateTo != "bill" && $updateTo != "ship") {
            if (!empty($billingAddress['name'])) {
                $addressData->setCompany($billingAddress['name']);
            }

            if (!empty($billingAddress['firstname'])) {
                $addressData->setFirstname($billingAddress['firstname']);
            }

            if (!empty($billingAddress['lastname'])) {
                $addressData->setLastname($billingAddress['lastname']);
            }
        }

        if ($updateTo == "bill") {
            $addressData->setCity($billingAddress['mail_city'])
                ->setCountryId($billingAddress['mail_country'])
                ->setStreet([$billingAddress['mail_address1'], $billingAddress['mail_address2']])
                ->setTelephone($billingAddress['central_phone_number'])
                ->setPostcode($billingAddress['mail_postal_code']);
            $region   = $this->regionFactory->create();
            $regionId = $region->loadByCode(
                $billingAddress['mail_state'],
                $billingAddress['mail_country']
            )->getId();
            if ($regionId) {
                    $addressData->setRegionId($regionId);
            }
        } elseif ($updateTo == "ship") {
            $addressData->setCity($billingAddress['phys_city'])
                ->setCountryId($billingAddress['phys_country'])
                ->setStreet([$billingAddress['phys_address1'], $billingAddress['phys_address2']])
                ->setTelephone($billingAddress['central_phone_number'])
                ->setPostcode($billingAddress['phys_postal_code']);
            $region   = $this->regionFactory->create();
            $regionId = $region->loadByCode(
                $billingAddress['phys_state'],
                $billingAddress['phys_country']
            )->getId();

            if ($regionId) {
                $addressData->setRegionId($regionId);
            }
        }

        $addressDetails->updateData($addressData)->save();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerMetaData()
    {
        $attributesMetadata = $this->customerMetadata->getAllAttributesMetadata();

        return $attributesMetadata;
    }//end getCustomerMetaData()

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressMetaData()
    {
        $attributesMetadata = $this->addressMetadata->getAllAttributesMetadata();

        return $attributesMetadata;
    }//end getCustomerAddressMetaData()
}//end class
