<?php

namespace Cloras\Base\Model\ResourceModel\Orders;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\Orders', 'Cloras\Base\Model\ResourceModel\Orders');
    }//end _construct()

    public function updateStatusRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('cloras_orders_index'),
            $columnData,
            $where = $condition
        );
    }//end updateStatusRecords()

    public function updateSalesOrderId($p21OrderId, $orderId)
    {
        if (!empty($orderId) && !empty($p21OrderId)) {
            $sql = 'UPDATE ' . $this->getConnection()->getTableName('sales_order') .
            " SET ext_order_id = $p21OrderId where entity_id = $orderId";
            $this->getConnection()->query($sql);
        }
    }//end updateSalesOrderId()

    public function deleteOrderIndex($orderId)
    {
        if (!empty($orderId)) {
            $this->getConnection()->delete(
                $this->getTable('cloras_orders_index'),
                ['order_id = ?' => $orderId]
            );
            return;
        }
    }//end deleteOrderIndex()

    public function getOrdersCollection($requestParams)
    {
        
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            ['coi' => $this->getTable('cloras_orders_index')]
        )->joinLeft(
            ['so' => $this->getTable('sales_order')],
            'coi.order_id = so.entity_id',
            'so.entity_id'
        )->columns(['so.ext_order_id']);


        if (array_key_exists('page', $requestParams) && array_key_exists('limit', $requestParams)) {
            $select->limitPage($requestParams['page'], $requestParams['limit']);
        }

        $select->order('coi.updated_at DESC');
        
        $data = $connection->fetchAll($select);
        return $data;
    }
}//end class
