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

namespace Bss\CheckoutCustomField\Model\Plugin;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class OrderGet
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
     * Init plugin
     *
     * @param \Magento\GiftMessage\Api\OrderRepositoryInterface $giftMessageOrderRepository
     * @param \Magento\GiftMessage\Api\OrderItemRepositoryInterface $giftMessageOrderItemRepository
     */
    public function __construct(
        JsonHelper $jsonHelper,
        \Bss\CheckoutCustomField\Helper\Data $helper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function isBssCustomField($order)
    {
        $customField = [];
        $extensionAttributes = $order->getExtensionAttributes();

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
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->isBssCustomField($order);
        }
        return $resultOrder;
    }
}
