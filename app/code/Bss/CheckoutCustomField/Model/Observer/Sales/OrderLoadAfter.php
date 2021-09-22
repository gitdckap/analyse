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
namespace Bss\CheckoutCustomField\Model\Observer\Sales;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Bss\CheckoutCustomField\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtension
     */
    protected $orderExtension;

    /**
     * OrderLoadAfter constructor.
     * @param JsonHelper $jsonHelper
     * @param \Bss\CheckoutCustomField\Helper\Data $helper
     */
    public function __construct(
        JsonHelper $jsonHelper,
        \Bss\CheckoutCustomField\Helper\Data $helper,
        \Magento\Sales\Api\Data\OrderExtension $orderExtension
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->orderExtension = $orderExtension;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customField = [];
        $order = $observer->getOrder();
        $extensionAttributes = $this->returnExtensionAttributes($order);
        if (!$order->getData('bss_customfield') || !$this->helper->moduleEnabled()) {
            return false;
        }
        $customAttr = $this->jsonHelper->jsonDecode($order->getData('bss_customfield'));
        foreach ($customAttr as $field) {
            if ($field['show_in_order'] == '1') {
                if (is_array($field['value']) && !empty($field['value'])) {
                    $str = "";
                    foreach ($field['value'] as $value) {
                        $str .= $value . ", ";
                    }
                    $customField[] = $field['frontend_label'] . " : " . $str;
                } elseif ($field['value'] != "") {
                    $customField[] = $field['frontend_label'] . " : " . $field['value'];
                }
            }
        }
        $extensionAttributes->setBssCustomfield($customField);
        $order->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param Order $order
     * @return mixed
     */
    private function returnExtensionAttributes($order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtension;
        }
        return $extensionAttributes;
    }
}
