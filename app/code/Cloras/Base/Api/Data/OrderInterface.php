<?php

namespace Cloras\Base\Api\Data;

interface OrderInterface
{
    /**
     * ID.
     */
    const INDEX_ID = 'id';

    /**
     * Customer ID.
     */
    const CUSTOMER_ID = 'customer_id';

    /**
     * Order ID.
     */
    const ORDER_ID = 'order_id';

    /**
     * Status - Pending, Processing, Completed, Failed.
     */
    const STATUS = 'status';

    /**
     * State - New, Update.
     */
    const STATE = 'state';

    /**
     * Created at time log.
     */
    const CREATED_AT = 'created_at';

    /**
     * Updated at time log.
     */
    const UPDATED_AT = 'updated_at';

    /**
     *  Customer Statuses.
     */
    const STATUS_PENDING   = 'Pending';
    const STATUS_PROCESS   = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED    = 'Failed';

    /**
     *  Customer State.
     */
    const STATE_NEW    = 'New';
    const STATE_UPDATE = 'Update';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return integer
     */
    public function getCustomerId();

    /**
     * @return integer
     */
    public function getOrderId();

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param integer $customerId
     *
     * @return OrderInterface
     */
    public function setCustomerId($customerId);

    /**
     * @param integer $orderId
     *
     * @return OrderInterface
     */
    public function setOrderId($orderId);

    /**
     * @param string $status
     *
     * @return OrderInterface
     */
    public function setStatus($status);

    /**
     * @param string $state
     *
     * @return OrderInterface
     */
    public function setState($state);
}//end interface
