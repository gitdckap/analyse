<?php

namespace Cloras\Base\Model\Fields;

use Cloras\Base\Api\CustomerFieldsInterface;

class Customers implements CustomerFieldsInterface
{
    private $customerFactory;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        $this->customerFactory       = $customerFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository   = $attributeRepository;
    }//end __construct()

    /**
     * @return $this
     */
    public function getFields()
    {
        $searchCriteria      = $this->searchCriteriaBuilder->create();
        $attributeRepository = $this->attributeRepository->getList(
            'customer',
            $searchCriteria
        );
        $customerAddressattributeRepository = $this->attributeRepository->getList(
            'customer_address',
            $searchCriteria
        );
        $customers = [];
        foreach ($attributeRepository->getItems() as $items) {
            $customers[$items->getAttributeCode()] = $items->getFrontendLabel();
        }

        foreach ($customerAddressattributeRepository->getItems() as $items) {
            $customers['address'][$items->getAttributeCode()] = $items->getFrontendLabel();
        }

        $customerData = [$customers];

        return $customerData;
    }//end getFields()
}//end class
