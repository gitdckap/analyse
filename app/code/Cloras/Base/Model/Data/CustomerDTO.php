<?php

namespace Cloras\Base\Model\Data;

use Cloras\Base\Api\Data\CustomerInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class CustomerDTO extends AbstractExtensibleObject implements CustomerInterface
{

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->_get(self::INDEX_ID);
    }//end getId()

    /**
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }//end getCustomerId()

    /**
     * @return integer
     */
    public function getWebsiteId()
    {
        return $this->_get(self::WEBSITE_ID);
    }//end getWebsiteId()

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }//end getStatus()

    /**
     * @return string
     */
    public function getState()
    {
        return $this->_get(self::STATE);
    }//end getState()

    /**
     * @return datatime
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }//end getCreatedAt()

    /**
     * @return datetime
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }//end getUpdatedAt()

    /**
     * @return datetime
     */
    public function getUpdatedTo()
    {
        return $this->_get(self::UPDATE_TO);
    }//end getUpdatedAt()

    /**
     * @param integer $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }//end setCustomerId()

    /**
     * @param integer $websiteId
     *
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }//end setWebsiteId()

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }//end setStatus()

    /**
     * @param string $state
     *
     * @return $this
     */
    public function setState($state)
    {
        return $this->setData(self::STATE, $state);
    }//end setState()

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }//end setCreatedAt()

    /**
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }//end setUpdatedAt()

    /**
     * @param integer $id
     *
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::INDEX_ID, $id);
    }//end setId()

    /**
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedTo($updatedTo)
    {
        return $this->setData(self::UPDATE_TO, $updatedTo);
    }//end setUpdatedAt()
}//end class
