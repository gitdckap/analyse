<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\Data\RepoItemsInterfaceFactory;
use Cloras\Base\Api\RepoResultsInterface;
use Cloras\Base\Model\ResourceModel\Customers\CollectionFactory as CustomerIndexCollection;
use Cloras\Base\Model\ResourceModel\Orders\CollectionFactory as OrderIndexCollection;

class RepoResults implements RepoResultsInterface
{
    private $orderIndexCollection;

    private $customerIndexCollection;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        CustomerIndexCollection $customerIndexCollection,
        OrderIndexCollection $orderIndexCollection,
        RepoItemsInterfaceFactory $repoItemInterface
    ) {
        $this->_request                = $request;
        $this->_eavAttribute           = $eavAttribute;
        $this->customerIndexCollection = $customerIndexCollection;
        $this->orderIndexCollection    = $orderIndexCollection;
        $this->_repoItemInterface      = $repoItemInterface;
    }//end __construct()

    /**
     * @param string $requestParams
     *
     * @return \Cloras\Base\Api\Data\RepoItemsInterface
     */
    public function getSearchResults()
    {
        $type = $this->_request->getParam('type');

        /*
         * @var \Cloras\Base\Api\Data\RepoItemInterface
         */
        $repoItems = $this->_repoItemInterface->create();

        $requestParams = $this->_request->getParams();
        // all params
        if ($type == 'customers') {
            $p21CustomerId = $this->_eavAttribute->getIdByCode('customer', 'cloras_p21_customer_id');
            $collection    = $this->customerIndexCollection->create()->getCustomerCollection(
                $requestParams,
                $p21CustomerId
            );

            $repoItems->setResults($collection);
        } elseif ($type == 'orders') {
            $collection     = $this->orderIndexCollection->create()->getOrdersCollection($requestParams);

            $repoItems->setResults($collection);
        } else {
            $error[] = ['Error' => 'Invalid type or params'];
            $repoItems->setResults($error);
        }//end if

        return $repoItems;
    }//end getSearchResults()
}//end class
