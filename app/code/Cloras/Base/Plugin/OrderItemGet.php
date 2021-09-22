<?php

namespace Cloras\Base\Plugin;

class OrderItemGet
{
    private $orderExtensionFactory;

    private $orderItemExtensionFactory;

    private $productExtensionFactory;

    private $customerExtensionFactory;

    private $productRepositoryFactory;

    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Magento\Sales\Api\Data\OrderItemExtensionFactory $orderItemExtensionFactory,
        \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory,
        \Magento\Customer\Api\Data\CustomerExtensionFactory $customerExtensionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Cloras\Base\Repo\OrdersIndex $orderIndexRepository,
        \Cloras\Base\Helper\Data $clorasHelper
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->customerRepositoryFactory = $customerRepositoryFactory;
        $this->productRepositoryFactory = $productRepository;
        $this->orderIndexRepository = $orderIndexRepository;
        $this->clorasHelper = $clorasHelper;
    }

    /**
     * Get Cloras Orders Id.
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $resultOrder
     *
     * @return                                        \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        if (is_object($subject)) {
            $resultOrder = $this->getOrderItemData($resultOrder);
            $resultOrder = $this->getOrderPaymentData($resultOrder);
        }

        return $resultOrder;
    }


    public function getOrderPaymentData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        /***/
        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();

        $orderPayment = $order->getPayment()->getData();
        
        foreach ($orderPayment as $key => $value) {
            if ($key == 'cc_last_4') {
                $cardNo = (($value) ? $value : "");
                $orderExtension->setCard($cardNo);
            }

            if ($key == 'method') {
                if ($value == 'checkmo') {
                    $orderExtension->setPaymentType('Cash');
                } elseif ($value == 'chargecreditline') {
                    $orderExtension->setPaymentType('chargecreditline');
                } else {
                    $orderExtension->setPaymentType($value);
                }
            }

            if ($key == 'amount_ordered') {
                $paymentAmt = (($value) ? $value : 0);
                $orderExtension->setPaymentAmt($paymentAmt);
            }
            
            if ($key == "last_trans_id") {
                $transId = (($value) ? $value : 0);
                $orderExtension->setTransId($transId);
            }

            $orderExtension->setAuthorizeNo(0);
            if ($key =='additional_information') {
                if (array_key_exists('processorAuthorizationCode', $value)) {
                    if (!empty($value['processorAuthorizationCode'])) {
                        $orderExtension->setAuthorizeNo($value['processorAuthorizationCode']);
                    }
                }
                if (array_key_exists('cc_type', $value)) {
                    $paymentType = (($value['cc_type']) ? $value['cc_type'] : '');
                    $orderExtension->setPaymentType($paymentType);
                }
            }
        }
        $orderExtension = $this->getOrderExtentionData($order, $orderExtension);

        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * Get Cloras Order Id for items of order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    protected function getOrderItemData(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        
        $orderItems = $order->getItems();
        if (null !== $orderItems) {
            /*
             * @var \Magento\Sales\Api\Data\OrderItemInterface
             */
            if ($order->getTaxExemptAmount() < 0) {
                $taxable = 'N';
            } elseif ($order->getTaxExemptAmount() == null) {
                $taxable = 'Y';
            } else {
                $taxable = 'Y';
            }

            foreach ($orderItems as $orderItem) {
                    $extensionAttributes = $orderItem->getExtensionAttributes();

                    $supplierId = '';
                    $uom = '';

                /**
                 * @var \Magento\Sales\Api\Data\OrderItemExtension
                 */
                    $orderItemExtension = $extensionAttributes
                    ? $extensionAttributes
                    : $this->orderItemExtensionFactory->create();

                    $orderItemExtension = $this->getOrderItemExtensionData($orderItem, $orderItemExtension);
               
                 /*Tax Exemption*/
                if ($taxable == 'N') {
                    $orderItemExtension->setTaxExemption($taxable);
                } else {
                    $taxable = (($orderItem->getTaxAmount() > 0) ? 'Y' : 'N');
                    $orderItemExtension->setTaxExemption($taxable);
                }

                    $orderItem->setExtensionAttributes($orderItemExtension);
            }
        }

        /***/
        $extensionAttributes = $order->getExtensionAttributes();
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();

        /* set Custom Order Extension */
        $orderExtension->setSalesLocationId('81210');//Default sales location for Guest user
        if ($order->getCustomerId()) {
            if ($clorasP21CustomerId = $this->getCustomerAttributeValue(
                $order->getCustomerId(),
                'cloras_p21_customer_id'
            )) {
                $orderExtension->setClorasP21CustomerId($clorasP21CustomerId);
            }

            if ($clorasP21ContactId = $this->getCustomerAttributeValue($order->getCustomerId(), 'cloras_p21_contact_id')
            ) {
                $orderExtension->setClorasP21ContactId($clorasP21ContactId);
            }

            if ($clorasP21ShiptoId = $this->getCustomerAttributeValue($order->getCustomerId(), 'cloras_p21_shipto_id')
            ) {
                $orderExtension->setClorasP21ShiptoId($clorasP21ShiptoId);
            }
        
            if ($salesLocation = $this->getCustomerAttributeValue($order->getCustomerId(), 'sales_location')
            ) {
                $orderExtension->setSalesLocationId($salesLocation);
            }
        }

        $orderExtension = $this->getOrderExtentionData($order, $orderExtension);

        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    public function getOrderItemExtensionData($orderItem, $orderItemExtension)
    {

        $supplierId = '';
        $uom = '';
        $partnumber = '';

        if ($supplierId = $this->getProductAttributeValue($orderItem->getSku(), 'supplier_id')) {
            $orderItemExtension->setSupplierId($supplierId);
        }

        if ($partnumber = $this->getProductAttributeValue($orderItem->getSku(), 'partnumber')) {
            $orderItemExtension->setPartnumber($partnumber);
        }

        if ($uom = $this->getProductAttributeValue($orderItem->getSku(), 'uom')) {
            $orderItemExtension->setUom($uom);
        }

        if ($po_cost = $this->getProductAttributeValue($orderItem->getSku(), 'po_cost')) {
            $poCost = (($po_cost) ? $po_cost : '');
            $orderItemExtension->setPoCost($poCost);
        }
        
        
        return $orderItemExtension;
    }

    public function getOrderExtentionData($order, $orderExtension)
    {

        $isAvailable = false;

        $comments = "";

        if (array_key_exists('bold_order_comment', $order->getData())) {
            $comments = $order->getBoldOrderComment() ? ' Comments: ' . $order->getBoldOrderComment() : '';
        }

        if ($order->getShippingAddress()) {
            if (array_key_exists('custom_ups_number', $order->getShippingAddress()->getData())) {
                $isAvailable = true;
            }

            if (array_key_exists('custom_fedex_number', $order->getShippingAddress()->getData())) {
                $isAvailable = true;
            }
        }

        if ($isAvailable) {
            $orderExtension->setP21UpsNo($order->getShippingAddress()->getCustomUpsNumber());
        }

        if ($isAvailable) {
            $fedexInstructions = 'Fedex Account Owner : ' . $order->getShippingAddress()->getCustomFedexNumber();
            if ($comments) {
                $fedexInstructions = $fedexInstructions . $comments;
            }

            $orderExtension->setFormattedFedexNo($fedexInstructions);
        } else {
            if ($comments) {
                $orderExtension->setFormattedFedexNo($comments);
            }
        }

        return $orderExtension;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface         $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
     *
     * @return                                        \Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }

        return $resultOrder;
    }

    private function getCustomerAttributeValue($customerId, $attributeCode)
    {
        try {
            $customerRepository = $this->customerRepositoryFactory->create();
            $customers = $customerRepository->getById($customerId);
            if (is_object($customers->getCustomAttribute($attributeCode))) {
                return $customers->getCustomAttribute($attributeCode)->getValue();
            }
        } catch (\Exception $e) {
            $customers = false;
        }

        return '';
    }

    private function getProductAttributeValue($sku, $attributeCode)
    {
        try {
            $products = $this->productRepositoryFactory->get($sku);
            if (is_object($products->getCustomAttribute($attributeCode))) {
                return $products->getCustomAttribute($attributeCode)->getValue();
            }
        } catch (\Exception $e) {
            $products = false;
        }

        return '';
    }

    /**
     * @param  \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param  \Magento\Sales\Api\Data\OrderInterface      $result
     * @return mixed
     * @throws \Exception
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        $result
    ) {
        if (is_object($subject)) {
            if ($result->getState() == \Magento\Sales\Model\Order::STATE_CANCELED) {
                if ($result->getId()) {
                    $this->orderIndexRepository->deleteOrderById($result->getId());
                }
            }
        }
        
        return $result;
    }
}
