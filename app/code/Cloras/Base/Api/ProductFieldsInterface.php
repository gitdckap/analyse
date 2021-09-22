<?php

namespace Cloras\Base\Api;

interface ProductFieldsInterface
{

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getFields();
}//end interface
