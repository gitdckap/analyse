<?php

namespace Cloras\Base\Model\Data;

use Cloras\Base\Api\Data\ProductIndexInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class ProductDTO extends AbstractExtensibleObject implements ProductIndexInterface
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
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

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
     * @param integer $productId
     *
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    
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
