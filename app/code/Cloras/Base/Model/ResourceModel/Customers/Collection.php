<?php

namespace Cloras\Base\Model\ResourceModel\Customers;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\Customers', 'Cloras\Base\Model\ResourceModel\Customers');
    }//end _construct()

    public function updateStatusRecords($condition, $columnData)
    {
        return $this->getConnection()->update(
            $this->getTable('cloras_customers_index'),
            $columnData,
            $where = $condition
        );
    }//end updateStatusRecords()

    public function getCustomerCollection($requestParams, $p21CustomerId)
    {
        $connection = $this->getConnection();
        $select     = $connection->select()->from(
            ['cci' => $this->getTable('cloras_customers_index')]
        )->joinLeft(
            ['ce' => $this->getTable('customer_entity')],
            'cci.customer_id = ce.entity_id'
        )->joinLeft(
            ['customer_varchar' => $this->getTable('customer_entity_varchar')],
            "customer_varchar.entity_id = ce.entity_id AND customer_varchar.attribute_id = $p21CustomerId",
            []
        )->columns(['p21_customer_id' => 'customer_varchar.value']);
        if (array_key_exists('page', $requestParams) && array_key_exists('limit', $requestParams)) {
            $select->limitPage($requestParams['page'], $requestParams['limit']);
        }

        $select->order('cci.updated_at DESC');
        
        $data = $connection->fetchAll($select);
        return $data;
    }//end getCustomerCollection()
}//end class
