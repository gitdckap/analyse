<?php

namespace Dckap\ShippingAdditionalFields\Observer;

/**
 * Class Observeorder
 *
 * @package Dckap\ShippingAdditionalFields\Observer
 */
class Observeorder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* before quote submit save the freight list values in sales_order_address table */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $order->setData("ddi_delivery_contact_email", $quote->getDdiDeliveryContactEmail());
        $order->setData("ddi_delivery_contact_no", $quote->getDdiDeliveryContactNo());
        $order->setData("ddi_pref_warehouse", $quote->getDdiPrefWarehouse());
        $order->setData("ddi_pickup_date", $quote->getDdiPickupDate());
        
        return $this;
    }
}
