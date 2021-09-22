<?php

namespace Cloras\Base\Model\ResourceModel\Products;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\Products', 'Cloras\Base\Model\ResourceModel\Products');
    }

    public function updateStatusRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('cloras_products_index'),
            $columnData,
            $where = $condition
        );
    }
}//end class
