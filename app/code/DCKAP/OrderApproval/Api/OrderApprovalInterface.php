<?php

namespace DCKAP\OrderApproval\Api;

interface OrderApprovalInterface
{
    /**
     * add/update shipto's in order approval
     *
     * @param mixed $address
     * @param string $email
     * @param string $accountNumber
     * @param string $websiteId
     * @param string $userId
     * @return array|null
     */
    public function updateShipToApproval($address, $email, $accountNumber, $websiteId, $userId);

    /**
     * get all customers
     *
     * @return array $customers
     */
    public function getAllCustomers();
}
