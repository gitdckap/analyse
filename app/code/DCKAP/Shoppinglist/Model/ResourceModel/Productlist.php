<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Model\ResourceModel;

class Productlist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('shopping_list_item', 'shopping_list_item_id');
    }
}
