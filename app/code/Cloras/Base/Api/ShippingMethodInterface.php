<?php

namespace Cloras\Base\Api;

/**
 * Interface retrieving shipping methods.
 *
 * @api
 */
interface ShippingMethodInterface
{

    /**
     * Get shipping methods.
     *
     * @return string[]
     */
    public function getShippingMethods();
}//end interface
