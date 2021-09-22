<?php

namespace Cloras\Base\Model;

use Cloras\Base\Api\PaymentMethodInterface;
use Magento\Payment\Model\Config\Source\Allmethods;

class PaymentMethod implements PaymentMethodInterface
{

    /**
     * @var Allmethods
     */
    private $allMethods;

    /**
     * PaymentMethod constructor.
     *
     * @param Allmethods $allMethods
     */
    public function __construct(
        Allmethods $allMethods
    ) {
        $this->allMethods = $allMethods;
    }//end __construct()

    /**
     * Getting active payment methods.
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->allMethods->toOptionArray(true);
    }
}//end class
