<?php

namespace DCKAP\OrderApproval\Controller\Adminhtml\Order;

use Magento\Backend\Model\Auth\Session;

class Approve extends \Magento\Framework\App\Action\Action
{
    const DEFAULT_SHIP_TO_NUMBER = '999999999';

    protected $_pageFactory;
    protected $orderRepository;
    protected $jsonFactory;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    /**
     * @var \Dckap\ShippingAdditionalFields\Helper\Data
     */
    protected $storePickupHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    protected $_orderApprovalHelper;

    protected $authSession;

    protected $extensionHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Dckap\ShippingAdditionalFields\Helper\Data $storePickupHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Session $authSession,
        \DCKAP\Extension\Helper\Data $extensionHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->orderRepository = $orderRepository;
        $this->jsonFactory = $jsonFactory;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->storePickupHelper = $storePickupHelper;
        $this->quoteFactory = $quoteFactory;
        $this->serializer = $serializer;
        $this->_orderApprovalHelper = $orderApprovalHelper;
        $this->authSession = $authSession;
        $this->scopeConfig = $scopeConfig;
        $this->extensionHelper = $extensionHelper;
        parent::__construct($context);
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('DCKAP_OrderApproval::order');
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $arrResponseData = [];
        $orderId = $params['order_id'];
        $data = [];
        try {
            $order = $this->orderRepository->get($orderId);
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
            if ($status) {
                $lineItemData = [];
                foreach ($order->getAllVisibleItems() as $item) {
                    $itemData = [];
                    $uom = 'EA';
                    if (isset($item->getProductOptions()['info_buyRequest'])) {
                        $uom = isset($item->getProductOptions()['info_buyRequest']['custom_uom']) ? $item->getProductOptions()['info_buyRequest']['custom_uom']:'EA';
                    }
                    $itemData['stockNum'] = $item->getSku();
                    $itemData['qty'] = (string) $item->getQtyOrdered();
                    $itemData['uom'] = $uom;
                    $itemData['price'] = (string) number_format($item->getPrice(), 4);
                    $itemData['mfgNum'] = '';
                    $itemName = preg_replace('/[^A-Za-z0-9_., -]/', '', $item->getName());
                    $itemData['description'] = $itemName;

                    /* commented existing $0 lineitem removal code */
                    /*if ($item->getPrice() > 0) {
                        $lineItemData[] = $itemData;
                    }*/
                    /* Added new code to allow $0 lineitem */
                    $allowZero = $this->extensionHelper->getProceedToCheckout();
                    if ($allowZero == '0') {
                        $superAttribute = "0";
                        if (isset($item->getProductOptions()['info_buyRequest'])) {
                            $superAttribute = isset($item->getProductOptions()['info_buyRequest']['super_attribute']) ? $item->getProductOptions()['info_buyRequest']['super_attribute'] : '0';
                        }
                        if ($superAttribute != "0" && $item->getParentItem() != null) {
                            if ($item->getPrice() > 0) {
                                $lineItemData[] = $itemData;
                            }
                        } else {
                            $lineItemData[] = $itemData;
                        }
                    } else {
                        if ($item->getPrice() > 0) {
                            $lineItemData[] = $itemData;
                        }
                    }
                }
                $shippingAddress = $order->getShippingAddress();
                $shipToNumber = ($order->getShipToNumber() && $order->getShipToNumber()!='') ? $order->getShipToNumber():self::DEFAULT_SHIP_TO_NUMBER;
                $street = $shippingAddress->getStreet();
                $orderData = [
                    "shipAddress" => [
                        "shipId" => $shipToNumber,
                        "shipCompanyName" => ($shippingAddress->getCompany()) ? $shippingAddress->getCompany():"",
                        "shipAddress1" => (isset($street[0])) ? $street[0] : "",
                        "shipAddress2" => (isset($street[1])) ? $street[1] : "",
                        "shipAddress3" => (isset($street[2])) ? $street[2] : "",
                        "shipCity" => $shippingAddress->getCity(),
                        "shipState" => $shippingAddress->getRegionCode(),
                        "shipPostCode" => (string) $shippingAddress->getPostcode(),
                        "shipCountry" => $shippingAddress->getCountryId(),
                        "shipPhone" => (string) $shippingAddress->getTelephone(),
                        "shipFax" => "",
                        "shipAttention" => $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname(),
                        "quoteRequest" => "N",
                        "validateOnly" => "N"
                    ],
                    "lineItems" => [
                        "itemData" => $lineItemData
                    ],
                    "shippingMethod" => $order->getShippingMethod(),
                    "shippingAmount" => (string) $order->getShippingAmount()
                ];

                $orderData['branch'] = '';
                if ($order->getShippingMethod()=='ddistorepickup_ddistorepickup') {
                    $ddiPrefWarehouse = $order->getDdiPrefWarehouse();
                    $warehouseList = $this->storePickupHelper->getWarehouseDetail();
                    if ($warehouseList && !empty($warehouseList->getData())) {
                        foreach ($warehouseList->getData() as $warehouse) {
                            if ($ddiPrefWarehouse==$warehouse['store_name']) {
                                $orderData['branch'] = $warehouse['store_description'];
                            }
                        }
                    }
                }

                if (isset($orderData['branch']) && $orderData['branch'] == '') {
                    $orderData['branch'] = $this->getBranchCodeByStoreId($order->getStoreId());
                }

                $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
                $orderData['special_instructions'] = "";
                $orderData['purchase_order_number'] = "";
                $orderData['expected_delivery_date'] = "";
                $customCheckoutField = [];
                if ($quote->getBssCustomfield()) {
                    $customCheckoutField = $this->serializer->unserialize($quote->getBssCustomfield());
                    if (isset($customCheckoutField['special_instructions'])) {
                        if (isset($customCheckoutField['special_instructions']['value'])) {
                            $orderData['special_instructions'] = $customCheckoutField['special_instructions']['value'];
                        } else {
                            $orderData['special_instructions'] = $customCheckoutField['special_instructions'];
                        }
                    }
                    if (isset($customCheckoutField['purchase_order_number'])) {
                        if (isset($customCheckoutField['purchase_order_number']['value'])) {
                            $orderData['purchase_order_number'] = $customCheckoutField['purchase_order_number']['value'];
                        } else {
                            $orderData['purchase_order_number'] = $customCheckoutField['purchase_order_number'];
                        }
                    }
                    if (isset($customCheckoutField['expected_delivery_date'])) {
                        if (isset($customCheckoutField['expected_delivery_date']['value'])) {
                            $orderData['expected_delivery_date'] = $customCheckoutField['expected_delivery_date']['value'];
                        } else {
                            $orderData['expected_delivery_date'] = $customCheckoutField['expected_delivery_date'];
                        }
                    }
                }
                $orderData['delivery_contact_email'] = ($order->getDdiDeliveryContactEmail()) ? $order->getDdiDeliveryContactEmail():"";
                $orderData['delivery_contact_no'] = ($order->getDdiDeliveryContactNo()) ? $order->getDdiDeliveryContactNo():"";
                $orderData['pickup_date'] = ($order->getDdiPickupDate()) ? $order->getDdiPickupDate():"";
                $customerData = [];
                $customerData['email'] = $order->getCustomerEmail();
                $customerData['account_number'] = $order->getAccountNumber();
                $customerData['user_id'] = $order->getUserId();

                $orderPlaced = $this->clorasDDIHelper->submitPendingOrder($integrationData, $orderData, $customerData);
                if (isset($orderPlaced['status']) && $orderPlaced['status'] == 0) {
                    $data['status'] = "Failure";
                    $data['message'] = $orderPlaced['message'];
                } elseif (isset($orderPlaced['data']['isValid']) && $orderPlaced['data']['isValid'] == 'no') {
                    $data['status'] = "Failure";
                    $data['message'] = $orderPlaced['data']['errorMessage'];
                } elseif (isset($orderPlaced['data']['orderNumber'])) {
                    try {
                        $order->setDdiOrderId($orderPlaced['data']['orderNumber']);
                        if (isset($orderPlaced['data']['orderDetails']['taxTotal']) && $orderPlaced['data']['orderDetails']['taxTotal']!='') {
                            $order->setTaxAmount((float) str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['taxTotal'])));
                            $order->setBaseTaxAmount((float) str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['taxTotal'])));
                        } else {
                            $order->setTaxAmount(0.0000);
                            $order->setBaseTaxAmount(0.0000);
                        }
                        if (isset($orderPlaced['data']['orderDetails']['freightTotal']) && $orderPlaced['data']['orderDetails']['freightTotal']!='') {
                            $order->setShippingAmount((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['freightTotal']))));
                            $order->setBaseShippingAmount((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['freightTotal']))));
                        } else {
                            $order->setShippingAmount(0.0000);
                            $order->setBaseShippingAmount(0.0000);
                        }
                        if (isset($orderPlaced['data']['orderDetails']['merchandiseTotal']) && $orderPlaced['data']['orderDetails']['merchandiseTotal']!='') {
                            $order->setSubtotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['merchandiseTotal']))));
                            $order->setBaseSubtotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['merchandiseTotal']))));
                        }
                        if (isset($orderPlaced['data']['orderDetails']['orderTotal']) && $orderPlaced['data']['orderDetails']['orderTotal']!='') {
                            $order->setGrandTotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['orderTotal']))));
                            $order->setBaseGrandTotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['orderTotal']))));
                        }
                        $order->setState("pending")->setStatus("approved");
                        /* store admin approval user details */
                        $adminUser = $this->getCurrentUser();
                        $adminOrderApprovalDetails = [
                            'user_id' => $adminUser->getUserId(),
                            'firstname' => $adminUser->getFirstname(),
                            'lastname' => $adminUser->getLastname(),
                            'email' => $adminUser->getEmail(),
                            'username' => $adminUser->getUserName()
                        ];
                        $order->setAdminApprovalDetails($this->serializer->serialize($adminOrderApprovalDetails));
                        $order->save();
                        //For send email
                        $this->_orderApprovalHelper->SendOrderApprovalEmail($order, 'admin');

                        //End Email
                        $data['status'] = "Success";
                        $data['message'] = __("Order approved successfully. The order number is " . $order->getDdiOrderId());
                    } catch (\Exception $e) {
                        $data['status'] = "Failure";
                        $data['message'] = $e->getMessage();
                    }
                }
            }
        } catch (\Exception $e) {
            $data['status'] = "Failure";
            $data['message'] = $e->getMessage();
        }
        if (isset($params['is_ajax']) && $params['is_ajax']=='1') {
            $res = $this->jsonFactory->create();
            $result = $res->setData($data);
            return $result;
        }
    }

    protected function getCurrentUser()
    {
        return $this->authSession->getUser();
    }
    /**
     * Get branch code by id
     *
     * @param int $storeId
     *
     * @return string|null
     */
    protected function getBranchCodeByStoreId($storeId = null)
    {
        return $this->scopeConfig->getValue('dckapextension/ddi_branch/branch_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }
}