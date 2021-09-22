<?php

namespace Cloras\Base\Api;

interface OrderFieldsInterface
{

    /**
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getFields();
}//end interface
