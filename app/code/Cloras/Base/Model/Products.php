<?php

namespace Cloras\Base\Model;

use Magento\Framework\Model\AbstractModel;

class Products extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\ResourceModel\Products');
    }//end _construct()
}//end class
