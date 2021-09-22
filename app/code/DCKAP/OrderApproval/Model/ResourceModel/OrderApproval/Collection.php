<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Model\ResourceModel\OrderApproval;

/**
 * Class Collection
 * @package DCKAP\OrderApproval\Model\ResourceModel\OrderApproval
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('DCKAP\OrderApproval\Model\OrderApproval', 'DCKAP\OrderApproval\Model\ResourceModel\OrderApproval');
    }
}
