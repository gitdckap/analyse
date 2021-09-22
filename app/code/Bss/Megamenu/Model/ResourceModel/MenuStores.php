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
 * @package    Bss_MegaMenu
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Megamenu\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Model\ScopeInterface;

class MenuStores extends AbstractDb
{
    /**
     * @var WriterInterface
     */
    protected $config;

    /**
     * MenuStores constructor.
     * @param Context $context
     * @param WriterInterface $config
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        WriterInterface $config,
        $connectionName = null
    ) {
        $this->config = $config;
        parent::__construct($context, $connectionName);
    }
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('bss_megamenu_stores', 'category_store_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['category_store_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('bss_megamenu_items'), $condition);

        /* Set menu tree empty */
        if (!$object->getStoreId()) {
            $storeId = 0;
            $scopeId = 'default';
        } else {
            $scopeId = ScopeInterface::SCOPE_STORES;
            $storeId = $object->getStoreId();
        }
        $this->config->save('megamenu/tree/data', '', $scopeId, $storeId);

        return parent::_beforeDelete($object);
    }
}
