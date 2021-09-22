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
namespace Bss\CheckoutCustomField\Model\ResourceModel\Grid;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('store_id', 'main_table.store_id');
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['grid_custom_field' => $this->getTable('bss_checkout_attribute_order_grid_view')],
            'main_table.increment_id = grid_custom_field.incrementId AND main_table.store_id = grid_custom_field.store_id',
            '*'
        );
        return $this;
    }
}
