<?php

namespace Cloras\Base\Api;

interface ProductResultsInterface
{

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function getProducts();

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function updateProductsInventory($data);

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function createProducts($data);

    /**
     * @param  string $data
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function updateProductPrice($data);

    /**
     * @param  string $data
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateBulkInventory($data);

    /**
     * @return \Cloras\Base\Api\Data\ProductInterface
     */
    public function getNewProducts();

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateNewProducts($data);
}//end interface
