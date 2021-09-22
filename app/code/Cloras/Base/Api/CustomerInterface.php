<?php

namespace Cloras\Base\Api;

interface CustomerInterface
{

    /**
     * @return \Cloras\Base\Api\Data\ItemsInterface
     */
    public function getCustomers();

    /**
     * @param string $data
     *
     * @return boolean
     */
    public function updateCustomers($data);

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function updateBillingAddress($data);

    /**
     * Get all attribute metadata.
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerMetaData();

    /**
     * Get all attribute metadata.
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAddressMetaData();
}//end interface
