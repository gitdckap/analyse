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
namespace Bss\CheckoutCustomField\Model\Observer\Order;

class EmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;

    /**
     * EmailTemplateVars constructor.
     * @param \Bss\CheckoutCustomField\Helper\Data $helper
     */
    public function __construct(\Bss\CheckoutCustomField\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getTransport();
        $order = $transport['order'];
        
        $transport['bss_custom_field'] = $this->helper->getVariableEmailHtml($order->getBssCustomfield());

        $ship_str = strtolower($order->getShippingDescription());
        $str   = 'store';
        if (strpos($ship_str, $str) !== false) {
            $transport['shipping_method_info'] = "Store Pickup – Pickup at ".$order->getDdiPrefWarehouse();
        }
        else{
            $transport['shipping_method_info'] = $order->getShippingDescription();
        }
    }
}
