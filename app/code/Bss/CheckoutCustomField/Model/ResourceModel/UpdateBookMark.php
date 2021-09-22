<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CheckoutCustomField\Model\ResourceModel;

/**
 * Class UpdateBookMark
 *
 * @package Bss\CheckoutCustomField\Model\ResourceModel
 */
class UpdateBookMark
{
    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $readAdapter;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $writeAdapter;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * UpdateBookMark constructor.
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\Serialize\SerializerInterface $serialize
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Serialize\SerializerInterface $serialize
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serialize;
        $this->readAdapter = $this->resourceConnection->getConnection('core_read');
        $this->writeAdapter = $this->resourceConnection->getConnection('core_write');
    }

    /**
     * Add Column
     *
     * @param string $attributeCode
     */
    public function addColumn($attributeCode)
    {
        $table = $this->getTableName('bss_checkout_attribute_order_grid_view');
        if (!$this->readAdapter->tableColumnExists($table, $attributeCode)) {
            $this->readAdapter->addColumn(
                $table,
                $attributeCode,
                'text'
            );
        }
    }

    /**
     * Delete Column
     *
     * @param string $attributeCode
     */
    public function deleteColumn($attributeCode)
    {
        $table = $this->getTableName('bss_checkout_attribute_order_grid_view');
        if ($this->readAdapter->tableColumnExists($table, $attributeCode)) {
            $this->readAdapter->dropColumn(
                $table,
                $attributeCode
            );
        }
    }

    /**
     * Update Book Mark
     *
     * @param int $showGrid
     * @param string $attributeCode
     */
    public function updateBookMark($showGrid, $attributeCode)
    {
        $bookMarkTable = $this->getTableName('ui_bookmark');
        $select = $this->readAdapter->select()
            ->from(
                [$bookMarkTable],
                ['bookmark_id', 'config']
            )->where("namespace = 'sales_order_grid'")
            ->where("identifier = 'current'");

        // @codingStandardsIgnoreStart
        $orderGridBookMark = $this->readAdapter->fetchAll($select);
        // @codingStandardsIgnoreEnd
        foreach ($orderGridBookMark as $row) {
            $config = $this->serializer->unserialize($row['config']);
            $columns = $config['current']['columns'];
            if (!is_array($columns)) {
                continue;
            }
            if (isset($columns[$attributeCode])) {
                $currentConfig = $config['current']['columns'][$attributeCode]['visible'];
                if (($currentConfig == false && $showGrid == 0)
                    || ($currentConfig == 'text' && $showGrid == 1)
                ) {
                    continue;
                }
                $condition = 'bookmark_id = ' .$row['bookmark_id'];
                if ($showGrid == 0) {
                    $config['current']['columns'][$attributeCode]['visible'] = false;
                } elseif ($showGrid == 1) {
                    $config['current']['columns'][$attributeCode]['visible'] = 'text';
                } else {
                    unset($config['current']['columns'][$attributeCode]);
                }
                $data = ['config' => $this->serializer->serialize($config)];
                $this->updateData($bookMarkTable, $data, $condition);
            }
        }
    }

    /**
     * Update Data
     *
     * @param string $table
     * @param array $data
     * @param string $condition
     */
    public function updateData($table, $data, $condition)
    {
        $this->writeAdapter->update($table, $data, $condition);
    }

    /**
     * Get Table Name
     *
     * @param String $entity
     * @return bool|mixed
     */
    public function getTableName($entity)
    {
        if (!isset($this->tableNames[$entity])) {
            try {
                $this->tableNames[$entity] = $this->resourceConnection->getTableName($entity);
            } catch (\Exception $e) {
                return false;
            }
        }
        return $this->tableNames[$entity];
    }
}
