<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Model\ResourceModel;

/**
 * Class OrderApproval
 * @package DCKAP\OrderApproval\Model\ResourceModel
 */
class OrderApproval extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('ddi_order_approval', 'id');
    }
}
