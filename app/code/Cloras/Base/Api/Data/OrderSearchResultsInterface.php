<?php

namespace Cloras\Base\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface OrderSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get items list.
     *
     * @return \Cloras\Base\Api\Data\OrderInterface[]
     */
    public function getItems();

    /**
     * Set items list.
     *
     * @param \Cloras\Base\Api\Data\OrderInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);

    /**
     * @return \Cloras\Base\Api\Data\OrderInterface[]
     */
    public function getFilterProduct();

    /**
     * @param \Cloras\Base\Api\Data\OrderInterface[] $order
     *
     * @return OrderItemsInterface
     */
    public function addFilterProduct($order);
}//end interface
