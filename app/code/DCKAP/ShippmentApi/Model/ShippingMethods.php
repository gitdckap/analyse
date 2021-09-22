<?php
namespace DCKAP\ShippmentApi\Model;
use DCKAP\ShippmentApi\Api\ShippingMethodsInterface;
use DCKAP\ShippmentApi\Helper\Data;
class ShippingMethods implements ShippingMethodsInterface
{

     public $helper;
     public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function getshippingMethods() {

        return $this->helper->getshippingmethods();
    }
}