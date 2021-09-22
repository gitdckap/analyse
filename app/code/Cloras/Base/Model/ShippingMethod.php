<?php

namespace Cloras\Base\Model;

use Cloras\Base\Api\ShippingMethodInterface;
use Magento\Shipping\Model\Config\Source\Allmethods;

class ShippingMethod implements ShippingMethodInterface
{

    /**
     * @var Allmethods
     */
    private $allMethods;

    /**
     * ShippingMethod constructor.
     *
     * @param Allmethods $allMethods
     */
    public function __construct(
        Allmethods $allMethods
    ) {
        $this->allMethods = $allMethods;
    }//end __construct()

    /**
     * Getting active shipping methods.
     *
     * @return array
     */
    public function getShippingMethods()
    {
        return $this->allMethods->toOptionArray(true);
    }//end getShippingMethods()
}//end class
