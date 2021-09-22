<?php

namespace Cloras\Base\Model;

use Magento\Framework\Model\AbstractModel;

class Customers extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\ResourceModel\Customers');
    }//end _construct()
}//end class
