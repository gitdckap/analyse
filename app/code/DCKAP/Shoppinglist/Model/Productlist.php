<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Model;

use DCKAP\Shoppinglist\Model\ProductlistInterface;

class Productlist extends \Magento\Framework\Model\AbstractModel implements ProductlistInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'dckap_shoppinglist_productlist';
 
    protected function _construct()
    {
        $this->_init('DCKAP\Shoppinglist\Model\ResourceModel\Productlist');
    }
 
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getShoppingListItemId()];
    }
}
