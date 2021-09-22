<?php

namespace Cloras\Base\Api;

interface OrderResultsInterface
{

    /**
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function getOrders();

    /**
     * @return int[]
     */
    public function getOrderIds();

    /**
     * @param string $data
     *
     * @return boolean
     */
    public function updateOrders($data);

    /**
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function getListOrders();

    /**
     * @param string
     *
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */
    public function prepareOrders($data);
}//end interface
