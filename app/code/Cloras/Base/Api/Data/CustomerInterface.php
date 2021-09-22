<?php

namespace Cloras\Base\Api\Data;

interface CustomerInterface
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
     * Website ID.
     */
    const WEBSITE_ID = 'website_id';

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
     * Update to - ERP.
     */
    const UPDATE_TO = 'update_to';

    /**
     *  Customer Statuses.
     */
    const STATUS_PENDING   = 'Pending';
    const STATUS_PROCESS   = 'Processing';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_FAILED    = 'Failed';
    const STATUS_DELETED = 'Deleted';

    /**
     *  Customer State.
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
    public function getCustomerId();

    /**
     * @return integer|null
     */
    public function getWebsiteId();

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
     * @return CustomerInterface
     */
    public function setCustomerId($customerId);

    /**
     * @param integer $websiteId
     *
     * @return CustomerInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * @param string $status
     *
     * @return CustomerInterface
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
