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
namespace Bss\CheckoutCustomField\Model;

class GridViewAttribute extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_cacheTag = 'bss_checkout_attribute_order_grid_view';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_checkout_attribute_order_grid_view';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\CheckoutCustomField\Model\ResourceModel\GridViewAttribute::class);
    }
}
