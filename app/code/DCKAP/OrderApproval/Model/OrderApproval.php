<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Model;

/**
 * Class OrderApproval
 * @package DCKAP\OrderApproval\Model
 */
class OrderApproval extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init( 'DCKAP\OrderApproval\Model\ResourceModel\OrderApproval' );
    }
}
