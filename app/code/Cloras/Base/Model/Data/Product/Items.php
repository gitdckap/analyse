<?php

namespace Cloras\Base\Model\Data\Product;

use Cloras\Base\Api\Data\ProductInterface;

class Items implements ProductInterface
{
    private $items;

    private $productscount = 0;

    public function __construct()
    {
        $this->items = [];
        $this->productscount = $this->productscount;
    }//end __construct()

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface[]|null
     */
    public function getProduct()
    {
        return $this->items;
    }//end getProduct()

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return $this
     */
    public function addProduct($product)
    {
        $this->items = $product;

        return $this;
    }//end addProduct()

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return $this
     */
    public function createProduct($product)
    {
        $this->items[] = $product;

        return $this->items;
    }//end createProduct()

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface $inventory
     */
    public function getProductQty($inventory)
    {
        $this->items[] = $inventory;

        return $this->items;
    }//end getProductQty()

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface $inventory
     */
    public function setResponseMessage($inventory)
    {
        $this->items = $inventory;

        return $this->items;
    }//end setResponseMessage()


    public function getTotalProducts()
    {
        return $this->productscount;
    }

    public function setTotalProducts($productsCount)
    {
        $this->productscount = $productsCount;
    }
}//end class
