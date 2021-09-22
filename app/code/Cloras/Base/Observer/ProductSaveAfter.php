<?php

namespace Cloras\Base\Observer;

use Magento\Framework\Event\ObserverInterface;
use Cloras\Base\Api\ProductIndexRepositoryInterface;

class ProductSaveAfter implements ObserverInterface
{

    public function __construct(
        ProductIndexRepositoryInterface $productIndexRepository
    ) {
        $this->productIndexRepository  = $productIndexRepository;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();  // you will get product object
        $_sku=$_product->getSku(); // for sku

        $this->productIndexRepository->saveProductIndex($_product->getId());
    }
}
