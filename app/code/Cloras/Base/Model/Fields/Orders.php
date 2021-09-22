<?php

namespace Cloras\Base\Model\Fields;

use Cloras\Base\Api\OrderFieldsInterface;

class Orders implements OrderFieldsInterface
{
    private $config;

    private $orderFactory;

    private $orderExtFactory;

    private $shippingInterfaceFactory;

    private $shippingAssignmentFactory;

    private $totalsFactory;

    private $orderItemFactory;

    private $orderAddressFactory;

    private $orderPaymentFactory;

    private $orderItemExtFactory;

    private $orderAddressExtFactory;

    public function __construct(
        \Magento\Framework\Config\DataInterface $config,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Api\Data\ShippingInterfaceFactory $shippingInterfaceFactory,
        \Magento\Sales\Api\Data\TotalInterfaceFactory $totalsFactory,
        \Magento\Sales\Api\Data\ShippingAssignmentInterfaceFactory $shippingAssignmentFactory,
        \Magento\Sales\Api\Data\OrderExtensionInterfaceFactory $orderExtFactory,
        \Magento\Sales\Api\Data\OrderItemInterfaceFactory $orderItemFactory,
        \Magento\Sales\Api\Data\OrderAddressInterfaceFactory $orderAddressFactory,
        \Magento\Sales\Api\Data\OrderPaymentInterfaceFactory $orderPaymentFactory,
        \Magento\Sales\Api\Data\OrderItemExtensionInterfaceFactory $orderItemExtFactory,
        \Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory $orderAddressExtFactory,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformationExtFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->config                    = $config;
        $this->orderFactory              = $orderFactory;
        $this->shippingInterfaceFactory  = $shippingInterfaceFactory;
        $this->totalsFactory             = $totalsFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->orderExtFactory           = $orderExtFactory;
        $this->orderItemFactory          = $orderItemFactory;
        $this->orderAddressFactory       = $orderAddressFactory;
        $this->orderPaymentFactory       = $orderPaymentFactory;
        $this->orderItemExtFactory       = $orderItemExtFactory;
        $this->orderAddressExtFactory    = $orderAddressExtFactory;
        $this->shippingInformationExtFactory = $shippingInformationExtFactory;
        $this->resourceConnection            = $resourceConnection;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\OrderFieldsInterface
     */
    public function getFields()
    {
        $totals = [
            'base_shipping_amount'                         => 0,
            'base_shipping_discount_amount'                => 0,
            'base_shipping_discount_tax_compensation_amnt' => 0,
            'base_shipping_incl_tax'                       => 0,
            'base_shipping_tax_amount'                     => 0,
            'shipping_amount'                              => 0,
            'shipping_discount_amount'                     => 0,
            'shipping_discount_tax_compensation_amount'    => 0,
            'shipping_incl_tax'                            => 0,
            'shipping_tax_amount'                          => 0,
        ];

        $total = $this->totalsFactory->create();
        $total->setData($totals);

        $extensionAttributes = $this->config->get('Magento\Sales\Api\Data\OrderInterface');
        $attributes          = $this->orderExtFactory->create();
        foreach ($extensionAttributes as $attribute => $value) {
            $attributes->setData($attribute, '');
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName  = $this->resourceConnection->getTableName('sales_order');

        $sql    = 'SHOW COLUMNS FROM ' . $tableName;
        $result = $connection->fetchCol($sql);

        $orderData = [];
        foreach ($result as $res) {
            $orderData[$res] = 0;
        }

        $tableName     = $this->resourceConnection->getTableName('sales_order_item');
        $sql           = 'SHOW COLUMNS FROM ' . $tableName;
        $result        = $connection->fetchCol($sql);
        $orderItemData = [];
        foreach ($result as $res) {
            $orderItemData[$res] = 0;
        }

        $tableName   = $this->resourceConnection->getTableName('sales_order_address');
        $sql         = 'SHOW COLUMNS FROM ' . $tableName;
        $result      = $connection->fetchCol($sql);
        $addressData = [];
        foreach ($result as $res) {
            $addressData[$res] = 0;
        }

        $tableName   = $this->resourceConnection->getTableName('sales_order_payment');
        $sql         = 'SHOW COLUMNS FROM ' . $tableName;
        $result      = $connection->fetchCol($sql);
        $paymentData = [];
        foreach ($result as $res) {
            $paymentData[$res] = '';
        }
        
        $payment = $this->orderPaymentFactory->create();
        $payment->setData($paymentData);
        
        $billingAddress = $this->orderAddressFactory->create();
        $billingAddress->setData($addressData);
        
        $item = $this->orderItemFactory->create();
        $item->setData($orderItemData);

        $itemExtensionAttributes = $this->config->get('Magento\Sales\Api\Data\OrderItemInterface');

        $itemAttributes = $this->orderItemExtFactory->create();
        foreach ($itemExtensionAttributes as $attribute => $value) {
            $itemAttributes->setData($attribute, '');
        }

        $item->setExtensionAttributes($itemAttributes);

        $addressAttributes = $this->config->get('Magento\Sales\Api\Data\OrderAddressInterface');

        $addressExt = $this->orderAddressExtFactory->create();
        foreach ($addressAttributes as $attribute => $value) {
            $addressExt->setData($attribute, '');
        }

        $billingAddress->setExtensionAttributes($addressExt);
        
        $shipping = $this->shippingInterfaceFactory->create();
        $shipping->setTotal($total);
        $shipping->setMethod('');
        $shipping->setAddress($billingAddress);
        
        $shippingAssignments = $this->shippingAssignmentFactory->create();
        $shippingAssignments->setShipping($shipping);

        $extensionAttributes = [];
        $extensionAttributes = $this->config->get('Magento\Checkout\Api\Data\ShippingInformationInterface');
        if (!empty($extensionAttributes)) {
            foreach ($extensionAttributes as $attribute => $value) {
                $extensionAttribute[] = $attribute;
            }
        }

        $attributes->setShippingAssignments([$shippingAssignments]);

        /*
         * @var \Magento\Sales\Model\Order
         */
        $order = $this->orderFactory->create();
        $order->setData($orderData);
        $order->setItems([$item]);
        $order->setExtensionAttributes($attributes);
        $order->setBillingAddress($billingAddress);

        $order->setPayment($payment);

        return $order;
    }//end getFields()
}//end class
