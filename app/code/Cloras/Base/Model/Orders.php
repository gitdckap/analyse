<?php

namespace Cloras\Base\Model;

use Magento\Framework\Model\AbstractModel;

class Orders extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\ResourceModel\Orders');
    }//end _construct()
}//end class
