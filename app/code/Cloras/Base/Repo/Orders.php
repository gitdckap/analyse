<?php

namespace Cloras\Base\Repo;

use Cloras\Base\Api\CustomerIndexRepositoryInterface;
use Cloras\Base\Api\Data\OrderInterfaceFactory;
use Cloras\Base\Api\Data\OrderItemsInterfaceFactory;
use Cloras\Base\Api\Data\ResultsInterfaceFactory;
use Cloras\Base\Api\OrderIndexRepositoryInterface;
use Cloras\Base\Api\OrderResultsInterface;
use Cloras\Base\Model\Data\CustomerDTO as Customer;
use Cloras\Base\Model\Data\OrderDTO as Order;
use Cloras\Base\Model\ResourceModel\Orders\CollectionFactory as OrdersIndexCollection;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Orders implements OrderResultsInterface
{
    private $orderRepository;

    private $customerRepository;

    private $orderIndexRepository;

    private $customerIndexRepository;

    private $searchCriteriaBuilder;

    private $itemsFactory;

    private $jsonHelper;

    private $registry;

    private $invoiceService;

    private $transaction;

    private $shipmentFactory;

    private $shipmentDocumentFactory;

    private $shipmentTrackCreationFactory;

    private $resourceConnection;

    private $salesOrderInterfaceFactory;

    private $orderResource;

    private $orderModelFactory;

    private $trackFactory;

    private $shipmentCollection;

    private $trackingCollection;

    private $productCollectionFactory;

    private $orderCollectionFactory;

    private $shipmentItemCreationInterface;

    private $resultsFactory;

    private $orderSender;

    private $invoiceSender;

    private $shipmentSender;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderItemsInterfaceFactory $itemsFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $salesOrderInterfaceFactory,
        Json $jsonHelper,
        Registry $registry,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\ShipmentFactory $shipmentFactory,
        \Magento\Sales\Model\Order\ShipmentDocumentFactory $shipmentDocumentFactory,
        \Magento\Sales\Api\Data\ShipmentTrackCreationInterfaceFactory $shipmentTrackCreationFactory,
        \Magento\Framework\DB\Transaction $transaction,
        OrderIndexRepositoryInterface $orderIndexRepository,
        CustomerIndexRepositoryInterface $customerIndexRepository,
        OrderInterfaceFactory $orderInterfaceFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\Data\ShipmentItemCreationInterface $shipmentItemCreationInterface,
        \Magento\Sales\Api\Data\ShipmentInterface $shipmentInterface,
        \Magento\Sales\Api\ShipOrderInterface $shipOrderInterface,
        \Magento\Sales\Api\Data\ShipmentTrackCreationInterface $trackCreationInterface,
        ResultsInterfaceFactory $resultsFactory,
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        ShipmentSender $shipmentSender,
        OrdersIndexCollection $orderIndexCollection,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        OrderManagementInterface $orderManagement,
        ScopeConfigInterface $scopeConfig,
        \Cloras\Base\Helper\Data $clorasHelper
    ) {
        $this->orderRepository            = $orderRepository;
        $this->customerRepository         = $customerRepository;
        $this->searchCriteriaBuilder      = $searchCriteriaBuilder;
        $this->itemsFactory               = $itemsFactory;
        $this->jsonHelper                 = $jsonHelper;
        $this->registry                   = $registry;
        $this->salesOrderInterfaceFactory = $salesOrderInterfaceFactory;
        $this->invoiceService             = $invoiceService;
        $this->shipmentFactory            = $shipmentFactory;
        $this->shipmentDocumentFactory    = $shipmentDocumentFactory;
        $this->shipmentTrackCreationFactory = $shipmentTrackCreationFactory;
        $this->transaction                  = $transaction;
        $this->orderIndexRepository         = $orderIndexRepository;
        $this->customerIndexRepository      = $customerIndexRepository;
        $this->orderInterfaceFactory        = $orderInterfaceFactory;
        $this->_request                 = $request;
        $this->trackFactory             = $trackFactory;
        $this->_shipmentRepository      = $shipmentRepository;
        $this->orderCollectionFactory   = $orderCollectionFactory;
        $this->shipmentItemCreationInterface = $shipmentItemCreationInterface;
        $this->_shipmentInteface             = $shipmentInterface;
        $this->_shipOrderInterface           = $shipOrderInterface;
        $this->_trackCreationInterface       = $trackCreationInterface;
        $this->resultsFactory                = $resultsFactory;
        $this->orderSender                   = $orderSender;
        $this->invoiceSender                 = $invoiceSender;
        $this->shipmentSender                = $shipmentSender;
        $this->orderIndexCollection      = $orderIndexCollection;
        $this->logger = $logger;
        $this->dir = $dir;
        $this->orderManagement = $orderManagement;
        $this->scopeConfig = $scopeConfig;
        $this->clorasHelper = $clorasHelper;
    }//end __construct()

    /**
     * @return \Cloras\Base\Api\Data\OrderItemsInterface
     */
    public function getOrders()
    {
        /*
         * @var \Cloras\Base\Api\Data\OrderItemsInterface
         */
        $items = $this->itemsFactory->create();

        $orderStatus = [
            Order::STATUS_PENDING,
            Order::STATUS_FAILED,
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('status', $orderStatus, 'in')->create();

        $loggedOrders = $this->orderIndexRepository->getOrderIds($searchCriteria);

        if ($loggedOrders->getTotalCount()) {
            $loadedCustomers = [];
            $loadedOrders    = [];
            $allIds          = $loggedOrders->getItems();

            $orderFilters = $this->searchCriteriaBuilder->addFilter('entity_id', $allIds['orders'], 'in')->create();
            $orders       = $this->orderRepository->getList($orderFilters)->getItems();
           
            foreach ($orders as $order) {
                $itemsExcludingConfigurables = [];
                foreach ($order->getItems() as $orderItem) {
                    if ($orderItem->getProductType() != 'configurable') {
                        $itemsExcludingConfigurables[] = $orderItem;
                    }
                }
                $order->setItems($itemsExcludingConfigurables);
                
                $loadedOrders[] = $order->getEntityId();
                $items->addOrder($order);
            }

            $customerIds = $allIds['customers'];

            $totalCustomers = 0;
            if (!empty($customerIds)) {
                $customerFilters = $this->searchCriteriaBuilder->addFilter('entity_id', $customerIds, 'in')->create();
                $customers       = $this->customerRepository->getList($customerFilters)->getItems();
                $customerData = [];
                foreach ($customers as $customer) {
                    $customerData = $this->clorasHelper->customizedCustomers($customer);
                    if (!$this->customerIndexRepository->getP21CustomerId($customer->getId())) {
                        ++$totalCustomers;
                    }
                    $loadedCustomers[] = $customer->getId();
                    $items->addCustomer($customer);
                }

                if (!empty($loadedCustomers)) {
                    $customerStatus = [
                        Customer::STATUS_PENDING,
                        Customer::STATUS_FAILED,
                    ];
                    $this->customerIndexRepository->updateStatuses(
                        $loadedCustomers,
                        $customerStatus,
                        Customer::STATUS_PROCESS
                    );
                }
            }//end if
            
            
            $items->setTotalOrders($loggedOrders->getTotalCount());
            $items->setTotalCustomers($totalCustomers);
            
            if (!empty($loadedOrders)) {
                $this->orderIndexRepository->updateStatuses($loadedOrders, $orderStatus, Order::STATUS_PROCESS);
            }
        }//end if

        return $items;
    }//end getOrders()

    /**
     * @param string $orderInfo
     *
     * @return boolean
     * @rework
     *
     * $orderInfo = [
     */
    public function updateOrders($orderInfo)
    {

    
        $this->logger->log($level = 100, print_r($this->jsonHelper->serialize($orderInfo), true));
        $orderInfo       = $this->jsonHelper->unserialize($orderInfo);
    
        $syncedCustomers = [];
        $syncedOrders    = [];
        $response        = [];
        $customerStatus  = [Customer::STATUS_PROCESS];
        $orderStatus     = [Order::STATUS_PROCESS];

        // Tell cloras ignore this update
        $this->registry->register('ignore_customer_update', 1);
        $this->registry->register('ignore_order_update', 1);

        // Negative Performance Impact - Load & Save in loop
        foreach ($orderInfo['orders'] as $newOrder) {
            if (array_key_exists('order_id', $newOrder)) {
                $syncedOrders[] = $newOrder['entity_id'];
        //print_r($newOrder);exit();
                // Update Data into table
                $p21OrderId = $newOrder['order_id'];
                $orderId    = $newOrder['entity_id'];
                if (!empty($p21OrderId)) {
                        $this->orderIndexCollection->create()->updateSalesOrderId(
                            $p21OrderId,
                            $orderId
                        );

                    /*$this->scopeConfig->getValue(
                                  'sales_email/order/enabled',
                                  \Magento\Store\Model\ScopeInterface::SCOPE_STORE,'1');
                      exit();*/
                    //send email after updating p21 order id
                    if ($orderId) {
                        $this->orderManagement->notify($orderId);
                    }
                }
            } else {
                if (array_key_exists('status', $newOrder)) {
                    if ($newOrder['status'] == '' || !$newOrder['status']) {
                        $orderInfo['failed_orders'] = $newOrder['entity_id'];
                    } else {
                        $syncedOrders[] = $newOrder['entity_id'];
                    }
                }
            }
        }

        foreach ($orderInfo['customers'] as $newCustomer) {
            $syncedCustomers[] = $newCustomer['entity_id'];
            $this->updateCustomerAttribute($newCustomer);
        }

        // Updating Customer status to completed (For new Customers)
        if (!empty($syncedCustomers)) {
            $this->customerIndexRepository->updateStatuses(
                $syncedCustomers,
                $customerStatus,
                Customer::STATUS_COMPLETED
            );
        }

        if (!empty($syncedOrders)) {
            $this->orderIndexRepository->updateStatuses(
                $syncedOrders,
                $orderStatus,
                Order::STATUS_PROCESS
            );
        }

        // Failed Customers
        if (!empty($orderInfo['failed_customers'])) {
            $this->customerIndexRepository->updateStatuses(
                $orderInfo['failed_customers'],
                $customerStatus,
                Customer::STATUS_FAILED
            );
        }

        // Failed orders
        if (!empty($orderInfo['failed_orders'])) {
            $this->orderIndexRepository->updateStatuses(
                $orderInfo['failed_orders'],
                $orderStatus,
                Order::STATUS_FAILED
            );
        }

        return true;
    }//end updateOrders()

    private function updateCustomerAttribute($newCustomer)
    {
        $customer = $this->customerRepository->getById($newCustomer['entity_id']);
        $customer->setCustomAttribute('cloras_p21_customer_id', $newCustomer['customer_id']);
        $customer->setCustomAttribute('cloras_p21_contact_id', $newCustomer['contact_id']);
        $customer->setCustomAttribute('cloras_p21_shipto_id', $newCustomer['shipto_id']);
        $this->customerRepository->save($customer);
    }

    /**
     * @return \Cloras\Base\Api\Data\OrderItemInterface
     */
    public function getListOrders()
    {
        /*
         * @var \Cloras\Base\Api\Data\OrderItemInterface
         */
        $items = $this->itemsFactory->create();
        $type  = $this->_request->getParam('type');

        if (empty($type)) {
            $this->searchCriteriaBuilder->addFilter('status', 'pending', 'eq');
        }

        $this->searchCriteriaBuilder->addFilter('ext_order_id', 'null', 'neq');

        $orderFilters = $this->searchCriteriaBuilder->create();
        $orderItems   = $this->orderRepository->getList($orderFilters)->getItems();
        foreach ($orderItems as $order) {
            if ($type == 'shipment') {
                $orderData = $this->orderRepository->get($order->getId());
                if ($orderData->hasShipments()) {
                    $items->addFilterOrders($order);
                }
            } else {
                $items->addFilterOrders($order);
            }
        }

        return $items;
    }//end getListOrders()

    /**
     * @param string $data
     *
     * @return \Cloras\Base\Api\Data\ResultsInterface
     */

    public function prepareOrders($data)
    {
        
        $this->logger->log($level = 100, print_r($this->jsonHelper->serialize($data), true));

        $ordersInfo = $this->jsonHelper->unserialize($data);

        $response       = [];
        $failure_orders = 0;
        $order_status   = [];

        $orderInfoCount = count($ordersInfo);

        $carrierCodeData = '';
        $countKey        = ($orderInfoCount - 1);
        $triggerInvoiceEmail = 2;
        $triggerShipmentEmail = 2;
        if (array_key_exists($countKey, $ordersInfo)) {
            if (array_key_exists('misc', $ordersInfo[$countKey])) {
                if (array_key_exists('carrier_code', $ordersInfo[$countKey]['misc'])) {
                    $carrierCodeData = $ordersInfo[$countKey]['misc']['carrier_code'];
                }

                if (array_key_exists('shipment_email', $ordersInfo[$countKey]['misc'])) {
                    $triggerShipmentEmail = $ordersInfo[$countKey]['misc']['shipment_email'];
                }

                if (array_key_exists('invoice_email', $ordersInfo[$countKey]['misc'])) {
                    $triggerInvoiceEmail = $ordersInfo[$countKey]['misc']['invoice_email'];
                }
            }
        }

        list($order_status, $status, $message) = $this->processOrderInfo(
            $ordersInfo,
            $order_status,
            $carrierCodeData,
            $triggerInvoiceEmail,
            $triggerShipmentEmail
        );

        $success_count  = 0;
        $failure_count  = 0;
        $total_order    = 0;
        $failure_orders = [];
        $success_orders = [];
        $total_count    = 0;

        foreach ($order_status as $key => $invoice_value) {
            if ($invoice_value['status'] == 'success') {
                $success_count++;
                $success_orders[$key] = $invoice_value['message'];
            } else {
                $failure_orders[$key] = $invoice_value['message'];
            }
        }

        $total_count = (count($success_orders) + count($failure_orders));
        $response[]  = [
            'total_count'    => $total_count,
            'success_count'  => count($success_orders),
            'success_orders' => $success_orders,
            'failure_count'  => count($failure_orders),
            'failure_orders' => $failure_orders,
        ];
        
        $results = $this->resultsFactory->create();

        $results->setResponse($response);

        return $results;
    }//end prepareOrders()

    private function processOrderInfo(
        $ordersInfo,
        $order_status,
        $carrierCodeData,
        $triggerInvoiceEmail,
        $triggerShipmentEmail
    ) {
        $message    = '';
        $status     = 'failure';
               
        foreach ($ordersInfo as $orderInfo) {
            $message    = '';
            $status     = 'failure';
            $p21OrderId = 0;
            if (array_key_exists('p21_order_id', $orderInfo) && array_key_exists('web_reference_id', $orderInfo)) {
                $p21OrderId = $orderInfo['p21_order_id'];

                $orderId = $this->getOrderId($p21OrderId, $orderInfo);

                if ($orderId) {
                    list($status, $message) = $this->getOrderInfo(
                        $orderId,
                        $orderInfo,
                        $status,
                        $message,
                        $carrierCodeData,
                        $triggerInvoiceEmail,
                        $triggerShipmentEmail
                    );
                } else {
                    $message .= ' P21 Order Id is not found';
                }//end if

                $order_status[$p21OrderId]['status']  = $status;
                $order_status[$p21OrderId]['message'] = $message;
                $this->orderIndexCollection->create()->updateSalesOrderId(
                    $p21OrderId,
                    $orderId
                );
                if ($status == 'success') {
                    //send email after updating p21 order id
                    if ($orderId) {
                        $this->orderManagement->notify($orderId);
                    }
                }
            } else {
                if (!array_key_exists('carrier_code', $orderInfo)) {
                    $message .= ' P21 Order Id or Web Reference Id Key is not found';
                }
            }//end if
        }//end foreach
        
        return [
            $order_status,
            $status,
            $message
        ];
    }

    private function getOrderId($p21OrderId, $orderInfo)
    {
        $ordersCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect(['entity_id', 'ext_order_id'])
            ->addFieldToFilter('ext_order_id', ['eq' => $p21OrderId]);
                    
        $orderId = 0;
        if (!empty($ordersCollection->getData())) {
            $orderId = $ordersCollection->getData()[0]['entity_id'];
        } else {
            if ($orderInfo['web_reference_id']) {
                $order = $this->salesOrderInterfaceFactory->create()->loadByIncrementId(
                    $orderInfo['web_reference_id']
                );
                if ($order && $order->getId()) {
                    $orderId = $order->getId();
                }
            }
        }

        return $orderId;
    }

    private function getOrderInfo(
        $orderId,
        $orderInfo,
        $status,
        $message,
        $carrierCodeData,
        $triggerInvoiceEmail,
        $triggerShipmentEmail
    ) {
        $order   = $this->orderRepository->get($orderId);
        $orderId = $order->getId();
        if (array_key_exists('completed', $orderInfo)) {
            if (($orderInfo['completed'] == 'Y' && $orderInfo['approved'] == 'Y') ||
                ($orderInfo['completed'] == 'N' && $orderInfo['approved'] == 'Y')) {
                // check order can invoice
                list($status, $message) = $this->processInvoice(
                    $order,
                    $orderInfo,
                    $status,
                    $message,
                    $triggerInvoiceEmail
                );
                            
                // Check order can ship
                list($status, $message) = $this->processShipment(
                    $order,
                    $orderInfo,
                    $status,
                    $message,
                    $carrierCodeData,
                    $triggerShipmentEmail
                );
            } elseif ($orderInfo['completed'] == 'NN' && $orderInfo['approved'] == 'YN') {
                list($status, $message) = $this->changeOrderStatus($order, $status, $message, 'pending');
            } elseif ($orderInfo['completed'] == 'N' && $orderInfo['approved'] == 'N'
                && $orderInfo['cancel_flag'] == 'N') {
                if (empty($orderInfo['invoices']) && empty($orderInfo['shipments'])) {
                    $message .= ' invoices and shipment array is not found';
                } else {
                    $message .= ' This Order is not approved and completed';
                }
            } elseif ($orderInfo['cancel_flag'] == 'Y') {
                list($status, $message) = $this->changeOrderStatus($order, $status, $message, 'cancel');
            }//end if
        }//end if
        
        return [
            $status,
            $message
        ];
    }

    private function changeOrderStatus($order, $status, $message, $state)
    {
        if ($state == 'pending') {
            if ($order->getState() == 'pending') {
                $order->setState('processing')->setStatus('processing');
                if ($order->save()) {
                    $status   = 'success';
                    $message .= ' Order update to Processing state';
                }
            } else {
                $status   = 'success';
                $message .= ' The Order not able to complete because of completed state is No ';
            }
        } elseif ($state == 'cancel') {
            list($status, $message) = $this->cancelOrder($order, $status, $message);
        }

        return [
            $status,
            $message
        ];
    }
    
    private function prepareOrderItems($order, $orderItems, $status, $message, $orderType = 'invoice')
    {
        $matchedOrderItems = [];
        foreach ($orderItems as $key => $value) {
            if (array_key_exists('item_id', $value)) {
                if ($value['item_id'] != 'DOWNPAYMENT') {
                    list($matchedOrderItems, $status, $message) = $this->prepareOrderLineItems(
                        $order,
                        $value,
                        $matchedOrderItems,
                        $status,
                        $message,
                        $orderType
                    );
                }
            } else {
                $message .= ' Item Id : ' . $value['item_id'] . ' is not found';
            }
        }
        return [
            $matchedOrderItems,
            $status,
            $message
        ];
    }

    private function prepareOrderLineItems($order, $value, $matchedOrderItems, $status, $message, $orderType)
    {
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderType == 'ship') {
                if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
            }
                        
            $sku = ($orderItem->getExtOrderItemId() ? $orderItem->getExtOrderItemId() : $orderItem->getSku());
            if (($value['item_id'] == $sku) && (round($value['qty']) != 0)) {
                $matchedOrderItems[$orderItem->getId()] = $value['qty'];
            } elseif (($value['item_id'] != $sku) && (round($value['qty']) == 0)) {
                $message .= ' ( Item Id : ' . $value['item_id'] . 'is not matched and Qty is not available)';
            } elseif (round($value['qty']) == 0) {
                $message .= ' ( Item Id : ' . $value['item_id'] . ' Qty is not available)';
            }
        }
        return [
            $matchedOrderItems,
            $status,
            $message
        ];
    }

    private function processInvoice($order, $orderInfo, $status, $message, $triggerInvoiceEmail)
    {
        if ($order->canInvoice()) {
            // Order Invoice
            if (array_key_exists('invoices', $orderInfo)) {
                if (!empty($orderInfo['invoices'])) {
                    foreach ($orderInfo['invoices'] as $key => $orderItems) {
                        $invoiceItems = [];
                        //invoice order items
                        list($invoiceItems, $status, $message) = $this->prepareOrderItems(
                            $order,
                            $orderItems['items'],
                            $status,
                            $message
                        );
                                   
                        list($status, $message) = $this->prepareInvoice(
                            $order,
                            $invoiceItems,
                            $status,
                            $message,
                            $triggerInvoiceEmail
                        );
                    }//end foreach
                } else {
                    $status   = 'success';
                    $message .= ' Invoice array is not found';
                }//end if
            }//end if
        } else {
            $status   = 'success';
            $message .= ' This Order already has been Invoiced';
        }//end if

        return [
            $status,
            $message
        ];
    }

    private function prepareInvoice($order, $invoiceItems, $status, $message, $triggerInvoiceEmail)
    {
        if (!empty($invoiceItems)) {
            try {
                 // generation of Invoice
                $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);
                $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
                $invoice->register();

                // Save the invoice to the order
                $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());

                // Saving the transaction
                if ($transactionSave->save()) {
                    $status   = 'success';
                    $message .= ' Invoice ID ' . $invoice->getId() . ' Created';
                    if ($triggerInvoiceEmail == 1) {
                        $this->invoiceSender->send($invoice);
                    }
                }
            } catch (\Exception $e) {
                $status   = 'failure';
                $message .= " ".$e->getMessage()." ";
            }
        } else {
            $message .= ' No Invoice generated';
        }

        return [
            $status,
            $message
        ];
    }

    private function processShipment($order, $orderInfo, $status, $message, $carrierCodeData, $triggerShipmentEmail)
    {
        if (!empty($orderInfo['shipments'])) {
            if ($order->canShip()) {
                foreach ($orderInfo['shipments'] as $key => $shipmentsData) {
                    $shipmentItems = [];
                    if (array_key_exists('items', $shipmentsData)) {
                        //ship order items
                        list($shipmentItems, $status,$message) = $this->prepareOrderItems(
                            $order,
                            $shipmentsData['items'],
                            $status,
                            $message,
                            'ship'
                        );

                        list($status, $message) = $this->orderShipTrack(
                            $order,
                            $shipmentsData,
                            $shipmentItems,
                            $status,
                            $message,
                            $carrierCodeData,
                            $triggerShipmentEmail
                        );
                    } else {
                        $message .= ' ( Shipment Items is not available)';
                    }//end if
                }//end foreach
            } else {
                $message .= ' ( This order already has been shipped)';
                $status   = 'success';
            }//end if
        } else {
            $status = 'success';
            $message .= ' Shipment array is not found';
        }//end if

        return [
            $status,
            $message
        ];
    }

    private function orderShipTrack(
        $order,
        $shipmentsData,
        $shipmentItems,
        $status,
        $message,
        $carrierCodeData,
        $triggerShipmentEmail
    ) {
        $orderStatus = [
            Order::STATUS_PROCESS
        ];

        if (!empty($shipmentItems)) {
            $s = [];
            foreach ($shipmentItems as $orderItemId => $qty) {
                $itemCreation = $this->shipmentItemCreationInterface;
                $itemCreation->setOrderItemId($orderItemId)->setQty($qty);

                $s[] = clone $itemCreation;
            }

            $shipmentItem = $this->_shipmentInteface->setItems($s);

            $_items = [];
            if (!empty($shipmentItem->getItems())) {
                $_items = $shipmentItem->getItems();
            }

            list($shipmentTracks, $message) = $this->orderShipmentTrack($shipmentsData, $message, $carrierCodeData);

            try {
                $orderId = $order->getId();
                $notifyEmail = (($triggerShipmentEmail == 1) ? 1 : 0);
                // shipment save
                $shipmentId        = $this->_shipOrderInterface->execute(
                    $orderId,
                    $_items,
                    $notify        = $notifyEmail,
                    $appendComment = false,
                    $comment       = null,
                    $shipmentTracks
                );
                if ($shipmentId) {
                    $message .= ' ( Shipment ID ' . $shipmentId . ' Created )';
                    $status   = 'success';
                }

                if ($status == 'success') {
                    $syncedOrders = [$orderId];
                    $this->orderIndexRepository->updateStatuses(
                        $syncedOrders,
                        $orderStatus,
                        Order::STATUS_COMPLETED
                    );
                }
            } catch (\Exception $e) {
                $message .= $e->getMessage();
            }//end try
        } else {
            $message .= ' (No shipment created for this order)';
        }//end if

        return [
            $status,
            $message
        ];
    }

    private function orderShipmentTrack($shipmentsData, $message, $carrierCodeData)
    {
        $shipmentTracks = [];

        if (array_key_exists('tracking_number', $shipmentsData) && array_key_exists('carrier_code', $shipmentsData)) {
            if (!empty($shipmentsData['tracking_number']) && !empty($shipmentsData['carrier_code'])) {
                $trackCreation = $this->_trackCreationInterface;

                if (!empty($carrierCodeData)) {
                    $carrier_code = str_replace("'", '"', $carrierCodeData);

                    $decode_carrier = json_decode($carrier_code, true);

                    $carrierCode = '';
                    if (isset($decode_carrier[$shipmentsData['carrier_code']])) {
                        $carrierCode = $decode_carrier[$shipmentsData['carrier_code']];
                        $trackCreation->setCarrierCode($carrierCode);

                        if (array_key_exists('title', $shipmentsData)) {
                            $trackCreation->setTitle($shipmentsData['title']);
                        }

                        $trackCreation->setTracknumber($shipmentsData['tracking_number']);
                        $trackData[] = clone $trackCreation;

                        $shipmentTrack = $this->_shipmentInteface->setTracks($trackData);
                        if (!empty($shipmentItem->getTracks())) {
                            $shipmentTracks = $shipmentTrack->getTracks();
                        }
                    }
                }//end if
            } else {
                $message .= ' Tracking number or carrier_code is null ';
            }//end if
        } else {
            $message .= " Tracking number key or Carrier Code Key doesn't exist";
        }//end if

        return [
            $shipmentTracks,
            $message
        ];
    }

    private function cancelOrder($order, $status, $message)
    {
        if ($order->canCancel()) {
            $order->setState('canceled')->setStatus('canceled');
            $orderItems = $order->getAllItems();
            foreach ($orderItems as $value) {
                $this->cancelQty($value);
            }

            if ($order->save()) {
                $status   = 'success';
                $message .= ' Order has been cancelled';
            }
        } else {
            $status   = 'success';
            $message .= ' This Order already cancelled';
        }

        return [
            $status,
            $message
        ];
    }

    private function cancelQty($value)
    {
        $value->setQtyCanceled($value['qty_ordered']);
        $value->save();
    }

    /**
     * @return int[]
     */
    public function getOrderIds()
    {
        $ids = [];

        $orderStatus = [Order::STATUS_COMPLETED];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('status', $orderStatus, 'neq')->create();
        $loggedOrders   = $this->orderIndexRepository->getOrderIds($searchCriteria);
        if ($loggedOrders->getTotalCount()) {
            foreach ($loggedOrders->getItems() as $key => $orders) {
                if ($key == 'orders') {
                    $orderIds[] = $orders;
                }
            }

            if (!empty($orderIds)) {
                $orderStatus = ['pending', 'processing'];

                $this->searchCriteriaBuilder->addFilter('status', $orderStatus, 'in');
                $this->searchCriteriaBuilder->addFilter('entity_id', $orderIds, 'in');
                $pendingOrders = $this->searchCriteriaBuilder->create();
                
                $orders        = $this->orderRepository->getList($pendingOrders);

                if ($orders->getTotalCount()) {
                    foreach ($orders as $order) {
                        $ids[] = $order->getIncrementId();
                    }
                }
            }
        }

        return $ids;
    }//end getOrderIds()
}//end class
