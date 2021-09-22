<?php

namespace Cloras\Base\Model;

use Magento\Framework\Model\AbstractModel;

class Integration extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\ResourceModel\Integration');
    }//end _construct()
}//end class
