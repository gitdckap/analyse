<?php

namespace Cloras\Base\Model\Data;

use Cloras\Base\Api\Data\OrderInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

class OrderDTO extends AbstractExtensibleObject implements OrderInterface
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
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }//end getOrderId()

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
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }//end getCreatedAt()

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
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
     * @param integer $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }//end setOrderId()

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
}//end class
