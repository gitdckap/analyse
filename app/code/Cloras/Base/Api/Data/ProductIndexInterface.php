<?php

namespace Cloras\Base\Api\Data;

interface ProductIndexInterface
{
    /**
     * ID.
     */
    const INDEX_ID = 'id';

    /**
     * Product ID.
     */
    const PRODUCT_ID = 'product_id';

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
     *  Product Statuses.
     */
    const STATUS_PENDING   = 'Pending';
    const STATUS_PROCESS   = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED    = 'Failed';
    const STATUS_DELETED = 'Deleted';

    /**
     *  Product State.
     */
    const STATE_NEW    = 'New';
    const STATE_UPDATE = 'Update';
    const STATE_DELETE= 'Delete';

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return integer
     */
    public function getProductId();

    

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return datetime
     */
    public function getCreatedAt();

    /**
     * @return datetime
     */
    public function getUpdatedAt();

    /**
     * @param integer $customerId
     *
     * @return ProductInterface
     */
    public function setProductId($customerId);


    /**
     * @param string $status
     *
     * @return ProductInterface
     */
    public function setStatus($status);

    /**
     * @param string $state
     *
     * @return string
     */
    public function setState($state);

    /**
     * @param string $createdAt
     *
     * @return datetime
     */
    public function setCreatedAt($createdAt);

    /**
     * @param string $updatedAt
     *
     * @return datetime
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @param integer $id
     *
     * @return integer
     */
    public function setId($id);
}//end interface
