<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\ProductIndexRepositoryInterface;
use Cloras\Base\Api\Data\ProductIndexInterface;
use Cloras\Base\Api\Data\ProductIndexInterfaceFactory;
use Cloras\Base\Model\Data\ProductDTO as Product;
use Cloras\Base\Model\ProductsFactory as ProductIndexModel;
use Cloras\Base\Model\ResourceModel\Products as ProductIndexResource;
use Cloras\Base\Model\ResourceModel\Products\CollectionFactory as ProductIndexCollection;

class ProductIndex implements ProductIndexRepositoryInterface
{
    private $productIndexModel;

    private $productIndexResource;

    private $productIndexCollection;

    /**
     * CustomerIndex constructor.
     *
     * @param ProductIndexModel $productIndexModel
     * @param ProductIndexResource $productIndexResource
     * @param ProductIndexCollection $productIndexCollection
     */
    public function __construct(
        ProductIndexModel $productIndexModel,
        ProductIndexResource $productIndexResource,
        ProductIndexCollection $productIndexCollection,
        ProductIndexInterfaceFactory $productIndexInterfaceFactory
    ) {
        $this->productIndexModel        = $productIndexModel;
        $this->productIndexResource     = $productIndexResource;
        $this->productIndexCollection   = $productIndexCollection;
        $this->productIndexInterfaceFactory = $productIndexInterfaceFactory;
    }//end __construct()

    public function saveProductIndex($productId)
    {
        
        $productIndex = $this->productIndexInterfaceFactory->create();
        $productIndex->setProductId($productId);
        
        $productIndex->setStatus(Product::STATUS_PENDING);
        $productIndex->setState(Product::STATE_NEW);

        $this->save($productIndex);
    }

    /**
     * @param ProductIndexInterface $productIndex
     */
    public function save(ProductIndexInterface $productIndex)
    {
        $productsIndex = $this->productIndexCollection->create();

        $productIndexCollection = $productsIndex->addFieldToFilter('product_id', $productIndex->getProductId());

        $productIndexCount = count($productIndexCollection);
        if ($productIndexCount != 0) {
            $productIds[] = $productIndex->getProductId();
            $condition     = '`product_id` in (' . implode(',', $productIds) . ')';
            if (array_key_exists('0', $productIndexCollection->getData())) {
                $productIndexStatus = $productIndexCollection->getData()[0]['status'];
                
                $productsIndex->updateStatusRecords(
                    $condition,
                    [
                        'status' => $productIndex->getStatus(),
                        'state'  => $productIndex->getState(),
                    ]
                );
            }
        } else {
            $productIndexModel = $this->productIndexModel->create();
            $productIndexModel->setProductId($productIndex->getProductId());
            $productIndexModel->setStatus($productIndex->getStatus());
            $productIndexModel->setState($productIndex->getState());

            $this->productIndexResource->save($productIndexModel);
        }//end if
    }//end save()
}//end class
