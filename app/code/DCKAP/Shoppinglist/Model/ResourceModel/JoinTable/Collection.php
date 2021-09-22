<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Model\ResourceModel\JoinTable;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init('DCKAP\Shoppinglist\Model\Productlist', 'DCKAP\Shoppinglist\Model\ResourceModel\Shoppinglist');
    }

    protected function filterOrder($productId, $customerId, $storeId)
    {
        $this->shopping_list_item = "main_table";
        $this->shopping_list = $this->getTable("shopping_list");
        $this->getSelect()
            ->join(
                ['sl' =>$this->shopping_list],
                $this->shopping_list_item . '.shopping_list_id= sl.list_id',
                ['list_id' => 'sl.list_id', 'list_name' => 'sl.list_name', 'shopping_list_item_id' => $this->shopping_list_item.'.shopping_list_item_id']
            );
        $this->getSelect()->where($this->shopping_list_item . ".product_id= ".$productId);
    }
}
