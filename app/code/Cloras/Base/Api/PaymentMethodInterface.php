<?php

namespace Cloras\Base\Api;

/**
 * Interface retrieving shipping methods.
 *
 * @api
 */
interface PaymentMethodInterface
{

    /**
     * Get shipping methods.
     *
     * @return string[]
     */
    public function getPaymentMethods();
}//end interface
