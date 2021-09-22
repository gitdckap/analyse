<?php

namespace Cloras\Base\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CustomerSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get items list.
     *
     * @return \Cloras\Base\Api\Data\CustomerInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Cloras\Base\Api\Data\CustomerInterface[] $items
     *
     * @return \Cloras\Base\Api\Data\CustomerSearchResultsInterface
     */
    public function setItems(array $items);
}//end interface
