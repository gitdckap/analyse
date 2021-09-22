<?php

namespace Cloras\Base\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Orders extends AbstractDb
{
    public function _construct()
    {
        $this->_init($this->getTable('cloras_orders_index'), 'id');
    }//end _construct()
}//end class
