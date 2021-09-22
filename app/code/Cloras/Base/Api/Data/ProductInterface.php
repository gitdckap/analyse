<?php

namespace Cloras\Base\Api\Data;

interface ProductInterface
{
    const PRODUCTS = 'products';

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface[]|null
     */
    public function getProduct();

    /**
     * @param \Cloras\Base\Api\Data\ProductInterface $product
     *
     * @return OrderItemsInterface
     */
    public function addProduct($product);

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface[]|null
     */
    public function createProduct($product);

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface $inventory
     */
    public function getProductQty($inventory);

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface $response
     */
    public function setResponseMessage($response);

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function getTotalProducts();

    /**
     * @param  int
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function setTotalProducts($productsCount);
}//end interface
