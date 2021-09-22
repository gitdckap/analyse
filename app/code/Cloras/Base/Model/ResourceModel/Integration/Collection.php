<?php

namespace Cloras\Base\Model\ResourceModel\Integration;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init('Cloras\Base\Model\Integration', 'Cloras\Base\Model\ResourceModel\Integration');
    }//end _construct()

    public function createIntegration($columnData)
    {
        $keys = [];
        if (array_key_exists('additional_data', $columnData)) {
            $keys = $columnData['additional_data'];
            unset($columnData['additional_data']);
        }

        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['ce' => 'cloras_integration_entity'],
            ['entity_id']
        )->where('type = ?', $columnData['type']);

        $data = $connection->fetchAll($select);

        $insertData = [];
        $updateData = [];
        $where      = [];

        if (!empty($data)) {

            if ($entityId = $data[0]['entity_id']) {
                $this->getConnection()->update(
                    $this->getTable('cloras_integration_entity'),
                    $columnData,
                    "`entity_id` = '" . $entityId . "'"
                );

                $conditions = [];
                if (!empty($keys)) {
                    $attributeList = $keys;
                    // select before insert or update
                    $select = $connection->select()->from(
                        ['ciea' => 'cloras_integration_eav_attribute'],
                        ['attribute']
                    )->where('integration_id = ?', $entityId);

                    $data               = $connection->fetchAll($select);
                    $availableAttribute = [];

                    // print_r($availableAttribute);exit;
                    if (!empty($data)) {
                        $additionalData = $keys;
                        foreach ($data as $column) {
                            $availableAttribute[] = $column['attribute'];
                            unset($additionalData[$column['attribute']]);
                        }

                        foreach ($additionalData as $columnKey => $columnValue) {
                            $insertData[] = [
                                'integration_id' => $entityId,
                                'attribute'      => $columnKey,
                                'value'          => $columnValue,
                            ];
                        }
                    }

                    if (!empty($insertData)) {
                        $this->beginConnection(
                            $connection,
                            'cloras_integration_eav_attribute',
                            'insert',
                            $insertData,
                            $updateData,
                            $where
                        );
                    }

                    foreach ($keys as $column => $value) {
                        $case              = $connection->quoteInto('?', $column);
                        $result            = $connection->quoteInto('?', $value);
                        $conditions[$case] = $result;
                    }

                    $value = $connection->getCaseSql('attribute', $conditions, 'value');

                    $where = [
                        'attribute IN (?)'     => array_keys($keys),
                        'integration_id = (?)' => $entityId,
                    ];

                    $updateData = ['value' => $value];

                    $this->beginConnection($connection, 'cloras_integration_eav_attribute', 'update', $insertData, $updateData, $where);
                } else {
                    $updateData = ['token' => $columnData['token']];
                    $where      = ['type eq (?)' => $columnData['type']];
                    $this->beginConnection($connection, 'cloras_integration_entity', 'update', $insertData, $updateData, $where);
                }//end if
            }//end if
        } else {
            try {
                $connection->beginTransaction();
                $connection->insert($this->getTable('cloras_integration_entity'), $columnData);
                $entity_id = $connection->lastInsertId();
                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollBack();
            }

            if (!empty($keys) && !empty($entity_id)) {
                foreach ($keys as $column => $value) {
                    $insertData[] = [
                        'integration_id' => $entity_id,
                        'attribute'      => $column,
                        'value'          => $value,
                    ];
                }

                $this->beginConnection($connection, 'cloras_integration_eav_attribute', 'insert', $insertData, $updateData, $where);

                return true;
            }
        }//end if
    }//end createIntegration()

    protected function beginConnection($connection, $tableName, $type, $insertData = [], $updateData = [], $where = [])
    {
        try {
            $connection->beginTransaction();
            if ($type == 'insert') {
                $connection->insertMultiple($this->getTable($tableName), $insertData);
            } elseif ($type == 'update') {
                $connection->update($this->getTable($tableName), $updateData, $where);
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }//end beginConnection()

    public function getClorasIntegrationEntity($type)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['ce' => 'cloras_integration_entity'],
            ['*']
        )->where('type = ?', $type);

        $data = $connection->fetchAll($select);

        $mergedClorasData = [];
        $clorasData       = [];

        if (!empty($data)) {
            $eavSelect = $connection->select()->from(
                ['ciea' => 'cloras_integration_eav_attribute'],
                ['*']
            )->where('integration_id = ?', $data[0]['entity_id']);
            $eavData   = $connection->fetchAll($eavSelect);

            foreach ($eavData as $key => $value) {
                $clorasData[$value['attribute']] = $value['value'];
            }

            $mergedClorasData = array_merge($data[0], $clorasData);
        }

        return $mergedClorasData;
    }//end getClorasIntegrationEntity()
}//end class
