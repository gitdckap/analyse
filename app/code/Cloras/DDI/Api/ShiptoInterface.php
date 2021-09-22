<?php

namespace Cloras\DDI\Api;

interface ShiptoInterface
{
    /**
     * add/update shipping address
     *
     * @param mixed $address
     * @param string $email
     * @param string $accountNumber
     * @param string $websiteId
     * @return array|null
     */
    public function updateShipTos($address, $email, $accountNumber, $websiteId);

    /**
     * get all customers
     *
     * @return array $customers
     */
    public function getAllCustomers();

    /**
     * Setup default shipping and billing address
     *
     * @param mixed $validateUser
     * @param string $email
     * @param string $websiteId
     * @return array|null
     */
    public function setupShipTo($validateUser, $email, $websiteId);
}
