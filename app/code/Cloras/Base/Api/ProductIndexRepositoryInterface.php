<?php

namespace Cloras\Base\Api;

use Cloras\Base\Api\Data\ProductIndexInterface;

interface ProductIndexRepositoryInterface
{


    /**
     * @param \Cloras\Base\Api\Data\ProductIndexInterface $product
     *
     * @return \Cloras\Base\Api\Data\ProductIndexInterface
     */
    public function save(ProductIndexInterface $product);
}
