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
namespace Bss\CheckoutCustomField\Block\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class CustomFields extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;

    /**
     * CustomFields constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function returnJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * @return \Bss\CheckoutCustomField\Helper\Data
     */
    public function returnHelper()
    {
        return $this->helper;
    }

    /**
     * Get current order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }
}
