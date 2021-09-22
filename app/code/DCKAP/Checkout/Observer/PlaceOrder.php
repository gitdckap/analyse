<?php
/**
 * Copyright © DCKAP Inc. All rights reserved.
 */
namespace Dckap\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class PlaceOrder
 * package Dckap\Checkout\Observer
 */
class PlaceOrder implements ObserverInterface
{
    const DEFAULT_SHIP_TO_NUMBER = '999999999';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Dckap\ShippingAdditionalFields\Helper\Data
     */
    protected $storePickupHelper;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_objCustomerSessionFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_objCheckoutSession;

    /**
     * @var \DCKAP\OrderApproval\Helper\Data
     */
    protected $orderApprovalHelper;

    protected $messageManager;

    private $json;
    protected $orderRepository;
    protected $_quoteFactory;
    protected $extensionHelper;
    protected $_customerSession;

    /**
     * PlaceOrder constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \Cloras\Base\Helper\Data $clorasHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Dckap\ShippingAdditionalFields\Helper\Data $storePickupHelper
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Dckap\ShippingAdditionalFields\Helper\Data $storePickupHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $_transportBuilder,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\App\ResourceConnection $connection,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \Magento\Customer\Model\Session $customerSessiondata,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->clorasHelper = $clorasHelper;
        $this->serializer = $serializer;
        $this->storePickupHelper = $storePickupHelper;
        $this->addressRepository = $addressRepository;
        $this->_objCheckoutSession = $_checkoutSession;
        $this->_objCustomerSessionFactory = $customerSession;
        $this->orderApprovalHelper = $orderApprovalHelper;
        $this->storeManager = $storeManager;
        $this->_transportBuilder = $_transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->quoteManagement = $quoteManagement;
        $this->_countryFactory = $countryFactory;
        $this->orderRepository = $orderRepository;
        $this->_quoteFactory = $quoteFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->connection  = $connection->getConnection();
        $this->json = $json;
        $this->extensionHelper = $extensionHelper;
        $this->_customerSession = $customerSessiondata;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $boolIsB2B = $intThresholdAmtApproval = false;
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();
        $boolIsFromOrderEdit = $this->orderApprovalHelper->getIsFromOrderApprovalEdit();
        $strB2B = $this->orderApprovalHelper->getWebsiteMode();
        if ($strB2B == 'b2b') {
            $boolIsB2B = true;
        }
        /* This code block place the order having status is pending approval */
        try {
            $arrMixShippingAddress = $this->_objCheckoutSession->getQuote()->getShippingAddress()->getData();
            $customerSessionFactory = $this->_objCustomerSessionFactory->create();
            $arrCustomerSessionData = $customerSessionFactory->getCustomData();
            if ($boolIsFromOrderEdit) {
                $arrAprroverSessionData = $customerSessionFactory->getCustomData();
                $arrExistingOrderDetails = [
                        'firstName'         => $arrAprroverSessionData['firstName'],
                        'lastName'          => $arrAprroverSessionData['lastName'],
                        'email'             => $arrAprroverSessionData['email'],
                        'accountNumber'     => $arrAprroverSessionData['accountNumber'],
                        'billCompanyName'   => $arrAprroverSessionData['billCompanyName'],
                ];
                $strJasonExistingOrderDetails = $this->serializer->serialize($arrExistingOrderDetails);
                $intOldOrderId = $this->_objCheckoutSession->getQuote()->getOrderId();
                $order->setExistingOrderId($intOldOrderId);
                $order->setExistingOrderDetails($strJasonExistingOrderDetails);
                try {
                    $objExistingOrderDetail= $this->_orderCollectionFactory->create()->addFieldToSelect('*')
                            ->addFieldToFilter('entity_id', ['eq' => $intOldOrderId])->getFirstItem();
                    $oldOrderIncrementId =$objExistingOrderDetail->getIncrementId();
                    $submitterName =$objExistingOrderDetail->getErpCustomerFirstName()." ".$objExistingOrderDetail->getErpCustomerLastName();
                    /*$objExistingOrderDetail = $this->orderRepository ->get($intOldOrderId);*/
                } catch (\Exception $e) {
                    $this->logger->info('ERROR - '.$e->getMessage());
                }
            }
            $intShipToNumber = self::DEFAULT_SHIP_TO_NUMBER;

            if (true == array_key_exists('customer_address_id', $arrMixShippingAddress) && false == empty($arrMixShippingAddress['customer_address_id'])) {
                $intAddressId = (int) $arrMixShippingAddress['customer_address_id'];
                $objShipToAddress = $this->addressRepository->getById($intAddressId);
                $objDdiShipToNumber = $objShipToAddress->getCustomAttribute('ddi_ship_number');
                if (true == is_object($objDdiShipToNumber)) {
                    $intShipToNumber = $objDdiShipToNumber->getValue();
                }
            }
            if ($boolIsFromOrderEdit) {
                $email = $objExistingOrderDetail->getCustomerEmail();
                $intCustomerAccountNumber = $objExistingOrderDetail->getAccountNumber();
                $order->setShipToNumber($objExistingOrderDetail->getShipToNumber());
                $order->setUserId($objExistingOrderDetail->getUserId());
                $order->setAccountNumber($objExistingOrderDetail->getAccountNumber());
                $order->setErpCustomerFirstName($objExistingOrderDetail->getErpCustomerFirstName());
                $order->setErpCustomerLastName($objExistingOrderDetail->getErpCustomerLastName());
                $order->setErpCustomerFirstName($objExistingOrderDetail->getErpCustomerFirstName());
                $order->setErpCustomerLastName($objExistingOrderDetail->getErpCustomerLastName());
                $order->SetCustomerEmail($objExistingOrderDetail->getCustomerEmail());
            } else {
                $email = $customerSessionFactory->getCustomer()->getEmail();
                $intCustomerAccountNumber = $arrCustomerSessionData['accountNumber'];
                $order->setShipToNumber($intShipToNumber);
                $order->setUserId($arrCustomerSessionData['userId']);
                $order->setAccountNumber($arrCustomerSessionData['accountNumber']);
                $order->setErpCustomerFirstName($arrCustomerSessionData['firstName']);
                $order->setErpCustomerLastName($arrCustomerSessionData['lastName']);
            }

            $intThresholdAmount = $this->orderApprovalHelper->isThresholdBasesApprovalAndAmount();
            $shipToNumber = $order->getShipToNumber();
            $erpAccountNumber = $order->getAccountNumber();
            $arrApprovers = $this->orderApprovalHelper->getApproverList($erpAccountNumber, $shipToNumber);
            if( empty($arrApprovers) || ( $intThresholdAmount != false && $intThresholdAmount > 0 && ( $order->getGrandTotal() < (int) $intThresholdAmount || $order->getGrandTotal() == (int) $intThresholdAmount) && $boolIsFromOrderEdit == false)){
                $intThresholdAmtApproval = true;
            }
            /**
             * Condition to check whether the shipto has order approval or not
             */
            $orderApprovalStatus = $this->orderApprovalHelper->getOrderApprovalStatus($email, $intCustomerAccountNumber, $intShipToNumber, $boolIsB2B);
            if ( $intThresholdAmtApproval || $orderApprovalStatus || $boolIsFromOrderEdit || (!$orderApprovalStatus && $paymentMethod != 'cashondelivery') || $paymentMethod == 'checkmo') {
                if ($paymentMethod == 'checkmo') {
                    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_quote');
                    if (!$status) {
                        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
                    }
                } else {
                    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
                }
                if ($status) {
                    $orderData = $lineItemData = [];
                    foreach ($order->getAllVisibleItems() as $item) {
                        $data = [];
                        $uom = 'EA';
                        if (isset($item->getProductOptions()['info_buyRequest'])) {
                            $uom = isset($item->getProductOptions()['info_buyRequest']['custom_uom']) ? $item->getProductOptions()['info_buyRequest']['custom_uom'] : 'EA';
                        }
                        $data['stockNum'] = $item->getSku();
                        $data['qty'] = (string)$item->getQtyOrdered();
                        $data['uom'] = $uom;
                        $data['price'] = (string)number_format($item->getPrice(), 4);
                        $data['mfgNum'] = '';
                        $itemName = preg_replace('/[^A-Za-z0-9_., -]/', '', $item->getName());
                        $data['description'] = $itemName;
                        /*if ($item->getPrice() > 0) {
//                        if ($item->getPrice() > 0 && $item->getProductType() != 'configurable') {
                            $lineItemData[] = $data;
                        }*/

                        $allowZero = $this->extensionHelper->getProceedToCheckout();
                        if ($allowZero == '0') {
                            $superAttribute = "0";
                            if (isset($item->getProductOptions()['info_buyRequest'])) {
                                $uom = isset($item->getProductOptions()['info_buyRequest']['custom_uom']) ? $item->getProductOptions()['info_buyRequest']['custom_uom'] : 'EA';
                                $superAttribute = isset($item->getProductOptions()['info_buyRequest']['super_attribute']) ? $item->getProductOptions()['info_buyRequest']['super_attribute'] : '0';
                            }
                            if ($superAttribute != "0" && $item->getParentItem() != null) {
                                if ($item->getPrice() > 0) {
                                    $lineItemData[] = $data;
                                }
                            } else {
                                $lineItemData[] = $data;
                            }
                        } else {
                            if ($item->getPrice() > 0) {
                                $lineItemData[] = $data;
                            }
                        }
                    }
                    $shippingAddress = $order->getShippingAddress();

                    $addressId = (int)$shippingAddress->getCustomerAddressId();
                    $shipToNumber = self::DEFAULT_SHIP_TO_NUMBER;
                    if ($addressId) {
                        $shipToAddress = $this->addressRepository->getById($addressId);
                        $ddiShipToNumber = $shipToAddress->getCustomAttribute('ddi_ship_number');

                        if ($ddiShipToNumber) {
                            $shipToNumber = $ddiShipToNumber->getValue();
                        }
                    }

                    $street = $shippingAddress->getStreet();
                    $orderData = [
                            "shipAddress" => [
                                    "shipId" => $shipToNumber,
                                    "shipCompanyName" => ($shippingAddress->getCompany()) ? $shippingAddress->getCompany() : "",
                                    "shipAddress1" => (isset($street[0])) ? $street[0] : "",
                                    "shipAddress2" => (isset($street[1])) ? $street[1] : "",
                                    "shipAddress3" => (isset($street[2])) ? $street[2] : "",
                                    "shipCity" => $shippingAddress->getCity(),
                                    "shipState" => $shippingAddress->getRegionCode(),
                                    "shipPostCode" => (string)$shippingAddress->getPostcode(),
                                    "shipCountry" => $shippingAddress->getCountryId(),
                                    "shipPhone" => (string)$shippingAddress->getTelephone(),
                                    "shipFax" => "",
                                    "shipAttention" => $shippingAddress->getFirstname()." ".$shippingAddress->getLastname(),
                                    "quoteRequest" => "N",
                                    "validateOnly" => "N"
                            ],
                            "lineItems" => [
                                    "itemData" => $lineItemData
                            ],
                            "shippingMethod" => $order->getShippingMethod(),
                            "shippingAmount" => (string)$order->getShippingAmount()
                    ];
                    if ($paymentMethod == 'checkmo') {
                        $orderData["shipAddress"]["quoteRequest"] = "Y";
                    }
                    $this->logger->info('paymentMethod - '.$paymentMethod);
                    if ($paymentMethod == 'cayancc') {
                        $info = $payment->getAdditionalInformation();
                        if ($info['method_title'] == 'Credit Card') {
                            $orderData['paymentDetails']['creditCardNumber'] = $info['cc_number'];
                            $orderData['paymentDetails']['creditCardName'] = $info['cc_holder_name'];
                        }
                        if ($info['method_title'] == 'Saved Credit Card') {
                            $orderData['paymentDetails']['creditCardNumber'] = $info['cc_card_number'];
                            $orderData['paymentDetails']['creditCardName'] = $info['holder_name'];
                        }
                        $orderData['paymentDetails']['cashTendered'] = (string)$payment->getAmountPaid();
                        $orderData['paymentDetails']['methodOfPayment'] = 'CreditCard';
                        $ccType = $info['cc_type'];
                        $orderData['paymentDetails']['creditCardType'] = (string)$ccType;
                        $orderData['paymentDetails']['creditCardTransCode'] = 'S';
                        $orderData['paymentDetails']['creditCardApprovalCode'] = $info['token'];
                    }

                    if ($paymentMethod == 'elementpayment') {
                        $info = $payment->getAdditionalInformation();
                        $orderData['paymentDetails']['cashTendered'] = (string)$info['cc_amount_approved'];
                        $orderData['paymentDetails']['methodOfPayment'] = 'CreditCard';
                        $orderData['paymentDetails']['creditCardNumber'] = (string)$info['cc_number'];
                        $orderData['paymentDetails']['creditCardName'] = $info['cc_holder'];
                        $orderData['paymentDetails']['creditCardType'] = (string)$info['cc_type'];
                        $orderData['paymentDetails']['creditCardTransCode'] = 'S';
                        $orderData['paymentDetails']['creditCardApprovalCode'] = (string)$info['cc_token'];
                        $orderData['paymentDetails']['creditCardAuthCode'] = (string)$info['cc_auth_code'];
                    }

                    /* If payment method is authorize.net */
                    if ($paymentMethod == 'authorizenet_acceptjs') {
                        $info = $payment->getAdditionalInformation();
                        $orderData['paymentDetails']['cashTendered'] = (string)$payment->getBaseAmountAuthorized();
                        $orderData['paymentDetails']['methodOfPayment'] = 'CreditCard';
                        $orderData['paymentDetails']['creditCardNumber'] = (string)$info['ccLast4'];
                        $orderData['paymentDetails']['creditCardName'] = $shippingAddress->getFirstname();
                        $ccType = '';
                        if ($info['accountType'] == 'Visa') {
                            $ccType = '4';
                        } elseif ($info['accountType'] == 'MasterCard') {
                            $ccType = '3';
                        } elseif ($info['accountType'] == 'AmericanExpress') {
                            $ccType = '1';
                        } elseif ($info['accountType'] == 'Discover') {
                            $ccType = '3';
                        }
                        $orderData['paymentDetails']['creditCardType'] = $ccType;
                        $orderData['paymentDetails']['creditCardTransCode'] = (string)$info['cvvResultCode'];
                        $orderData['paymentDetails']['creditCardApprovalCode'] = (string)$info['avsResultCode'];
                        $orderData['paymentDetails']['creditCardAuthCode'] = (string)$info['authCode'];
                    }


                    // Authorize.net payment module
                    if ($paymentMethod == 'anet_creditcard') {
                        $info = $payment->getAdditionalInformation();
                        $orderData['paymentDetails']['cashTendered'] = (string)$payment->getBaseAmountAuthorized();
                        $orderData['paymentDetails']['methodOfPayment'] = 'CreditCard';
                        $last4= substr($info['cardNumber'], -4, 4);
                        $orderData['paymentDetails']['creditCardNumber'] = (string)$last4;
                        $orderData['paymentDetails']['creditCardName'] = $shippingAddress->getFirstname();
                        $ccType = '';
                        if ($info['cardType'] == 'Visa') {
                            $ccType = '4';
                        } elseif ($info['cardType'] == 'MasterCard') {
                            $ccType = '3';
                        } elseif ($info['cardType'] == 'AmericanExpress') {
                            $ccType = '1';
                        } elseif ($info['cardType'] == 'Discover') {
                            $ccType = '3';
                        }
                        $orderData['paymentDetails']['creditCardType'] = $ccType;
                        $orderData['paymentDetails']['creditCardTransCode'] = (string)$info['cvvResultCode'];
                        $orderData['paymentDetails']['creditCardApprovalCode'] = (string)$info['avsResultCode'];
                        $orderData['paymentDetails']['creditCardAuthCode'] = (string)$info['authCode'];
                    }

	                /* If payment method is payflow.net */
	                if ($paymentMethod == 'payflowpro') {
		                $info = $payment->getAdditionalInformation();
		                $orderData['paymentDetails']['cashTendered'] = (string)$payment->getBaseAmountAuthorized();
		                $orderData['paymentDetails']['methodOfPayment'] = 'CreditCard';
		                $orderData['paymentDetails']['creditCardNumber'] = (string)$payment['cc_last_4'];
		                $orderData['paymentDetails']['creditCardName'] = $shippingAddress->getFirstname();
		                $ccType = '';
		                if ($payment['cc_type'] == 'VI') {
			                $ccType = '4';
		                } elseif ($payment['cc_type'] == 'MC') {
			                $ccType = '3';
		                } elseif ($payment['cc_type'] == 'AE') {
			                $ccType = '1';
		                } elseif ($payment['cc_type'] == 'DI') {
			                $ccType = '3';
		                }

		                $orderData['paymentDetails']['creditCardType'] = $ccType;
		                $orderData['paymentDetails']['creditCardTransCode'] = (string)$payment->getCreatedTransaction()->getTxnType();
		                $orderData['paymentDetails']['creditCardApprovalCode'] = (string)$payment->getCreatedTransaction()->getTxnId();
		                $orderData['paymentDetails']['creditCardAuthCode'] = (string)$payment->getCreatedTransaction()->getHtmlTxnId();

	                }



	                $orderData['branch'] = '';
                    $specialInstructionsAddon = '';
                    if ($order->getShippingMethod() == 'ddistorepickup_ddistorepickup') {
                        $ddiPrefWarehouse = $order->getDdiPrefWarehouse();
                        $specialInstructionsAddon = $ddiPrefWarehouse;
                        $warehouseList = $this->storePickupHelper->getWarehouseDetail();
                        if ($warehouseList && !empty($warehouseList->getData())) {
                            foreach ($warehouseList->getData() as $warehouse) {
                                if ($ddiPrefWarehouse == $warehouse['store_name']) {
                                    $orderData['branch'] = $warehouse['store_description'];
                                }
                            }
                        }
                    }

                    $quote = $this->_quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
                    /* updated code - START */
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
                    /* updated code - END */
                    if ($boolIsFromOrderEdit) {
                        $orderData['delivery_contact_email'] = ($objExistingOrderDetail->getDdiDeliveryContactEmail()) ? $objExistingOrderDetail->getDdiDeliveryContactEmail() : "";
                        $orderData['delivery_contact_no'] = ($objExistingOrderDetail->getDdiDeliveryContactNo()) ? $objExistingOrderDetail->getDdiDeliveryContactNo() : "";
                        $orderData['pickup_date'] = ($objExistingOrderDetail->getDdiPickupDate()) ? $objExistingOrderDetail->getDdiPickupDate() : "";
                        ;
                    } else {
                        $orderData['delivery_contact_email'] = ($order->getDdiDeliveryContactEmail()) ? $order->getDdiDeliveryContactEmail() : "";
                        $orderData['delivery_contact_no'] = ($order->getDdiDeliveryContactNo()) ? $order->getDdiDeliveryContactNo() : "";
                        $orderData['pickup_date'] = ($order->getDdiPickupDate()) ? $order->getDdiPickupDate() : "";
                    }
                    if ($paymentMethod == 'purchaseorder') {
                        $orderData['purchase_order_number'] = $payment->getPoNumber();
                    }

                    if ($specialInstructionsAddon != '') {
                        $orderData['special_instructions'] = $orderData['special_instructions'].' - Storepickup Branch: '.$specialInstructionsAddon;
                    }
                    $orderPlaced = $this->clorasDDIHelper->submitOrder($integrationData, $orderData);
                    if (isset($orderPlaced['status']) && $orderPlaced['status'] == false) {
                        $data['message'] = $orderPlaced['message'];
                        $this->logger->info('Submit Order - Place Order -Error message String');
                        $this->logger->info($data['message']);
                        $this->_customerSession->unsErrorMessage();
                        $this->_customerSession->setErrorMessage($data['message']);
                     //   $this->logger->info($this->_customerSession->getErrorMessage());
                    } elseif (isset($orderPlaced['data']['isValid']) && $orderPlaced['data']['isValid'] == 'no') {
                        $data['message'] = $orderPlaced['data']['errorMessage'];
                        $this->logger->info('Submit Order - Place Order -Error message');
                        $this->logger->info($data['message']);
                        $this->logger->info('session value');
                        $this->_customerSession->unsErrorMessage();
                        $this->_customerSession->setErrorMessage($data['message']);
                      //  $this->logger->info($this->_customerSession->getErrorMessage());

                    } elseif (isset($orderPlaced['data']['orderNumber'])) {
                     try {
                            $this->_customerSession->unsCustomerShipto();
                            $order->setDdiOrderId($orderPlaced['data']['orderNumber']);
                            if (isset($orderPlaced['data']['orderDetails']['taxTotal']) && $orderPlaced['data']['orderDetails']['taxTotal'] != '') {
                                $order->setTaxAmount((float)str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['taxTotal'])));
                                $order->setBaseTaxAmount((float)str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['taxTotal'])));
                            } else {
                                $order->setTaxAmount(0.0000);
                                $order->setBaseTaxAmount(0.0000);
                            }
                            if (isset($orderPlaced['data']['orderDetails']['freightTotal']) && $orderPlaced['data']['orderDetails']['freightTotal'] != '') {
                                $order->setShippingAmount((float)(str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['freightTotal']))));
                                $order->setBaseShippingAmount((float)(str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['freightTotal']))));
                            } else {
                                $order->setShippingAmount(0.0000);
                                $order->setBaseShippingAmount(0.0000);
                            }
                            if (isset($orderPlaced['data']['orderDetails']['merchandiseTotal']) && $orderPlaced['data']['orderDetails']['merchandiseTotal'] != '') {
                                $order->setSubtotal((float)(str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['merchandiseTotal']))));
                                $order->setBaseSubtotal((float)(str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['merchandiseTotal']))));
                            }
                            if (isset($orderPlaced['data']['orderDetails']['orderTotal']) && $orderPlaced['data']['orderDetails']['orderTotal'] != '') {
                                $order->setGrandTotal((float)(str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['orderTotal']))));
                                $order->setBaseGrandTotal((float)(str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['orderTotal']))));
                            }

                            if (false == is_null($this->_objCheckoutSession->getQuote()->getOrderId())) {
                                $order->setState("approved")->setStatus("approved");
                                try {
                                    $this->saveOldOrderStatus($intOldOrderId);
                                } catch (\Exception $e) {
                                    $this->logger->info('ERROR - '.$e->getMessage());
                                }
	                            $order->setEmailSent(1);
	                            $order->setCanSendNewEmailFlag(false);
	                            $order->setSendEmail(false);
	                            $order->save();
	                            $writer = new \Zend\Log\Writer\Stream(BP . "/var/log/approval_edit_order.log");
	                            $logger = new \Zend\Log\Logger();
	                            $logger->addWriter($writer);
	                            $shipping_address = $order->getShippingAddress();
	                            $country = $this->_countryFactory->create()->loadByCode($shipping_address->getData("country_id"));
	                            $country_name = $country->getName();
	                            $logger->info(print_r($country_name,true));
	                            //Billing Address
	                            $billing_address = $order->getBillingAddress();
	                            $BillCountry = $this->_countryFactory->create()->loadByCode($billing_address->getData("country_id"));
	                            $bill_country_name = $BillCountry->getName();

	                            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	                            $quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
	                            $quote = $quoteFactory->create()->load($order->getQuoteId());
	                            $customCheckoutField = array();
	                            if ($quote->getBssCustomfield()) {
		                            $customCheckoutField = $this->serializer->unserialize($quote->getBssCustomfield());
		                            if (isset($customCheckoutField['purchase_order_number'])) {
			                            if (isset($customCheckoutField['purchase_order_number']['value'])) {
				                            $poNumber = $customCheckoutField['purchase_order_number']['value'];
			                            }else {
				                            $poNumber = $customCheckoutField['purchase_order_number'];
			                            }
		                            }
	                            }

	                            $poNumber = !empty($poNumber) ? $poNumber : '';
	                            $Po_Num=  !empty($poNumber) ? $poNumber : ' Not Available';
	                            $createdAt =  $order->getCreatedAt();
	                            $cdate = date("m/y", strtotime($createdAt));
	                            $subject = 'Your Modified Order Confirmation: #'.$order->getIncrementId().'  PO: #'.$poNumber.'  '.$cdate.'';

	                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	                            $templateId = $this->scopeConfig->getValue(
			                            'OrderApproval_section/general/ddi_update_order_email', $storeScope);// template id

	                            $sender_email_identity = $this->scopeConfig->getValue(
			                            'OrderApproval_section/general/sender_email_identity', $storeScope);// email sender
	                            $logger->info('$sender_email_identity - '.print_r($sender_email_identity,true));
	                            $emailsender = 'trans_email/ident_'.$sender_email_identity.'/email';
	                            $emailsenderName = 'trans_email/ident_'.$sender_email_identity.'/name';

	                            if(isset($sender_email_identity) && !empty($sender_email_identity)){
		                            $fromEmail = $this->scopeConfig->getValue($emailsender, $storeScope, $order->getStoreId());
		                            $fromName = $this->scopeConfig->getValue($emailsenderName, $storeScope, $order->getStoreId());
	                            }else{
		                            $fromEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope, $order->getStoreId());
		                            $fromName = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope, $order->getStoreId());
	                            }
	                            $logger->info('fromEmail - '.print_r($fromEmail,true));
	                            $logger->info('fromName - '.print_r($fromName,true));
	                            $customerSessionFactory = $this->_objCustomerSessionFactory->create();
	                            $approver = $customerSessionFactory->getCustomer()->getEmail();
	                            $logger->info('bccemail - '.print_r($approver,true));
	                            $logger->info('toemail - '.print_r($email,true));
	                            $update_shipping_method_info = $order->getShippingDescription();

	                            $ship_str = strtolower($order->getShippingDescription());
	                            $str   = 'store';
	                            if (strpos($ship_str, $str) !== false) {
		                            $update_shipping_method_info = "Store Pickup – Pickup at ".$order->getDdiPrefWarehouse();
	                            }

	                            $logger->info('ship method infor - '.print_r($update_shipping_method_info,true));
	                            try {
		                            // template variables pass here
		                            $templateVars = [
				                            'store' => $this->storeManager->getStore(),
				                            'customer_name' =>$order->getCustomerName(),
				                            'oldOrderIncrementId'=>$oldOrderIncrementId,
				                            'order' => $order,
				                            'name' => $shipping_address->getData("firstname") . ' ' . $shipping_address->getData("lastname"),
				                            'company' => $shipping_address->getData("company"),
				                            'street' => $shipping_address->getData("street"),
				                            'city' => $shipping_address->getData("city") . ',' . $shipping_address->getData("region") . ',' . $shipping_address->getData("postcode"),
				                            'country' => $country_name,
				                            'telephone' => "T: " . $shipping_address->getData("telephone"),
				                            'poNumber' => $poNumber,
				                            'status'=>$order->getStatusLabel(),
				                            'bname' => $billing_address->getData("firstname") . ' ' . $billing_address->getData("lastname"),
				                            'bcompany' => $billing_address->getData("company"),
				                            'bstreet' => $billing_address->getData("street"),
				                            'bcity' => $billing_address->getData("city") . ',' . $billing_address->getData("region") . ',' . $billing_address->getData("postcode"),
				                            'bcountry' => $bill_country_name,
				                            'btelephone' => "T: " . $billing_address->getData("telephone"),
				                            'cdate'=>$cdate,
				                            'subject'=> $subject,
				                            'po'=>$Po_Num,
				                            'shipping_method_info' => $update_shipping_method_info
		                            ];

		                            $storeId = $this->storeManager->getStore()->getId();
		                            $from = ['email' => $fromEmail, 'name' => $fromName];
		                            $this->inlineTranslation->suspend();

		                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		                            $templateOptions = [
				                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
				                            'store' => $storeId
		                            ];
		                            $transport = $this->_transportBuilder->setTemplateIdentifier($templateId, $storeScope)
				                            ->setTemplateOptions($templateOptions)
				                            ->setTemplateVars($templateVars)
				                            ->setFrom($from)
				                            ->addTo($email)
				                            ->addBcc($approver)
				                            ->getTransport();
		                            $transport->sendMessage();
		                            $this->inlineTranslation->resume();
		                            $logger->info('shopper email sent');
	                            } catch (\Exception $e) {
		                            $logger->info($e->getMessage());
	                            }
                            }else{
	                            $order->save();
                            }
                        } catch (\Exception $e) {
                            $this->logger->info('ERROR - '.$e->getMessage());
                        }
                    }
                }
            } else {
                $this->_customerSession->unsCustomerShipto();
                $order->setState("pending")->setStatus("pending_approval");
                $comment='';
                $order->addStatusToHistory($order->getStatus(), $comment);
                $order->setEmailSent(1);
                $order->setCanSendNewEmailFlag(false);
                $order->setSendEmail(false);
                $order->save();

                $shipping_address = $order->getShippingAddress();
                $country = $this->_countryFactory->create()->loadByCode($shipping_address->getData("country_id"));
                $country_name = $country->getName();
                $this->logger->info(print_r($country_name, true));
                //Billing Address
                $billing_address = $order->getBillingAddress();
                $BillCountry = $this->_countryFactory->create()->loadByCode($billing_address->getData("country_id"));
                $bill_country_name = $BillCountry->getName();

                $quote = $this->_quoteFactory->create()->load($order->getQuoteId());
                $customCheckoutField = [];
                if ($quote->getBssCustomfield()) {
                    $customCheckoutField = $this->serializer->unserialize($quote->getBssCustomfield());
                    if (isset($customCheckoutField['purchase_order_number'])) {
                        if (isset($customCheckoutField['purchase_order_number']['value'])) {
                            $poNumber = $customCheckoutField['purchase_order_number']['value'];
                        } else {
                            $poNumber = $customCheckoutField['purchase_order_number'];
                        }
                    }
                }

                $poNumber = !empty($poNumber) ? $poNumber : '';
                $Po_Num=  !empty($poNumber) ? $poNumber : ' Not Available';
                $createdAt =  $order->getCreatedAt();
                $cdate = date("m/y", strtotime($createdAt));
                $subject = 'Pending Order Confirmation: #'.$order->getIncrementId().' Requesting Approval PO: #'.$poNumber.'  '.$cdate.'';

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $templateId = $this->scopeConfig->getValue(
                    'OrderApproval_section/general/ddi_waiting_for_order_approval',
                    $storeScope
                );// template id

                $sender_email_identity = $this->scopeConfig->getValue(
                    'OrderApproval_section/general/sender_email_identity',
                    $storeScope
                );// email sender
                $this->logger->info('$sender_email_identity - '.print_r($sender_email_identity, true));
                $emailsender = 'trans_email/ident_'.$sender_email_identity.'/email';
                $emailsenderName = 'trans_email/ident_'.$sender_email_identity.'/name';

                if (isset($sender_email_identity) && !empty($sender_email_identity)) {
                    $fromEmail = $this->scopeConfig->getValue($emailsender, $storeScope, $order->getStoreId());
                    $fromName = $this->scopeConfig->getValue($emailsenderName, $storeScope, $order->getStoreId());
                } else {
                    $fromEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope, $order->getStoreId());
                    $fromName = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope, $order->getStoreId());
                }
                $this->logger->info('fromEmail - '.print_r($fromEmail, true));
                $this->logger->info('fromName - '.print_r($fromName, true));
                $toEmail = $order->getCustomerEmail(); // receiver email id

                $ship_str = strtolower($order->getShippingDescription());
                $str   = 'store';
                if (strpos($ship_str, $str) !== false) {
                    $waiting_shipping_method_info = "Store Pickup – Pickup at ".$order->getDdiPrefWarehouse();
                } else {
                    $waiting_shipping_method_info = $order->getShippingDescription();
                }
                try {
                    // template variables pass here
                    $templateVars = [
                            'store' => $this->storeManager->getStore(),
                            'customer_name' =>$order->getCustomerName(),
                            'order' => $order,
                            'name' => $shipping_address->getData("firstname") . ' ' . $shipping_address->getData("lastname"),
                            'company' => $shipping_address->getData("company"),
                            'street' => $shipping_address->getData("street"),
                            'city' => $shipping_address->getData("city") . ',' . $shipping_address->getData("region") . ',' . $shipping_address->getData("postcode"),
                            'country' => $country_name,
                            'telephone' => "T: " . $shipping_address->getData("telephone"),
                            'poNumber' => $poNumber,
                            'status'=>$order->getStatusLabel(),
                            'bname' => $billing_address->getData("firstname") . ' ' . $billing_address->getData("lastname"),
                            'bcompany' => $billing_address->getData("company"),
                            'bstreet' => $billing_address->getData("street"),
                            'bcity' => $billing_address->getData("city") . ',' . $billing_address->getData("region") . ',' . $billing_address->getData("postcode"),
                            'bcountry' => $bill_country_name,
                            'btelephone' => "T: " . $billing_address->getData("telephone"),
                            'cdate'=>$cdate,
                            'subject'=> $subject,
                            'po'=>$Po_Num,
                            'shipping_method_info'=>$waiting_shipping_method_info

                    ];

                    $storeId = $this->storeManager->getStore()->getId();
                    $from = ['email' => $fromEmail, 'name' => $fromName];
                    $this->inlineTranslation->suspend();

                    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                    $templateOptions = [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $storeId
                    ];
                    $transport = $this->_transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($templateVars)
                            ->setFrom($from)
                            ->addTo($toEmail)
                            ->getTransport();
                    $transport->sendMessage();
                    $this->inlineTranslation->resume();
                    $this->logger->info('shopper email sent');
                } catch (\Exception $e) {
                    $this->logger->info($e->getMessage());
                }
               $boolCopyApproverConfig = $this->orderApprovalHelper->getCopyApproverSubmitOrderEmailConfig();
                $writer = new \Zend\Log\Writer\Stream(BP . "/var/log/approver_email_copy.log");
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('Email Cc config - '.print_r($boolCopyApproverConfig,true));
                /* send email to approvers */
                if($boolCopyApproverConfig) {
                    try {

                        $shipToNumber = $order->getShipToNumber();
                        $erpAccountNumber = $order->getAccountNumber();
                        $arrApprovers = $this->orderApprovalHelper->getApproverList($erpAccountNumber, $shipToNumber);
                        $logger->info('Erp_account_number - ' . $erpAccountNumber);
                        $logger->info('Shipto_number - ' . $shipToNumber);
                        $createdAt = $order->getCreatedAt();
                        $strCreatedDate = date("m/y", strtotime($createdAt));
                        $strSubject = 'Approve Pending Order : #' . $order->getIncrementId() . ' Requesting for Approve PO: #' . $poNumber . '  ' . $strCreatedDate . '';

                        $intStoreScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        $intTemplateId = $this->scopeConfig->getValue(
                            'OrderApproval_section/general/ddi_approve_customer_order', $intStoreScope);// template id

                        $sender_email_identity = $this->scopeConfig->getValue(
                            'OrderApproval_section/general/sender_email_identity', $intStoreScope);// email sender

                        $logger->info('sender_email_identity - ' . print_r($sender_email_identity, true));

                        $strApproverEmailSender = 'trans_email/ident_' . $sender_email_identity . '/email';
                        $strApproverEmailSenderName = 'trans_email/ident_' . $sender_email_identity . '/name';

                        if (isset($sender_email_identity) && !empty($sender_email_identity)) {
                            $strApproverFromEmail = $this->scopeConfig->getValue($strApproverEmailSender, $intStoreScope, $order->getStoreId());
                            $strApproverFromName = $this->scopeConfig->getValue($strApproverEmailSenderName, $intStoreScope, $order->getStoreId());
                        } else {
                            $strApproverFromEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $intStoreScope, $order->getStoreId());
                            $strApproverFromName = $this->scopeConfig->getValue('trans_email/ident_support/name', $intStoreScope, $order->getStoreId());
                        }
                        $logger->info('strApproverFromEmail - ' . print_r($strApproverFromEmail, true));
                        $logger->info('strApproverFromName - ' . print_r($strApproverFromName, true));

                        $intStoreId = $this->storeManager->getStore()->getId();
                        $strFromEmailDetail = ['email' => $strApproverFromEmail, 'name' => $strApproverFromName];
                        $arrTemplateOptions = [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $intStoreId
                        ];
                        $ship_str = strtolower($order->getShippingDescription());
                        $str = 'store';
                        if (strpos($ship_str, $str) !== false) {
                            $approver_shipping_method_info = "Store Pickup – Pickup at " . $order->getDdiPrefWarehouse();
                        } else {
                            $approver_shipping_method_info = $order->getShippingDescription();
                        }
                        if (false == empty($arrApprovers) && true == is_array($arrApprovers)) {
                            $arrmixApproverTemplateVars = [
                                'store' => $this->storeManager->getStore(),
                                'approver_name' => 'Approver',
                                'customer_name' => $order->getCustomerName(),
                                'order' => $order,
                                'name' => $shipping_address->getData("firstname") . ' ' . $shipping_address->getData("lastname"),
                                'company' => $shipping_address->getData("company"),
                                'street' => $shipping_address->getData("street"),
                                'city' => $shipping_address->getData("city") . ',' . $shipping_address->getData("region") . ',' . $shipping_address->getData("postcode"),
                                'country' => $country_name,
                                'telephone' => "T: " . $shipping_address->getData("telephone"),
                                'poNumber' => $poNumber,
                                'status' => $order->getStatusLabel(),
                                'bname' => $billing_address->getData("firstname") . ' ' . $billing_address->getData("lastname"),
                                'bcompany' => $billing_address->getData("company"),
                                'bstreet' => $billing_address->getData("street"),
                                'bcity' => $billing_address->getData("city") . ',' . $billing_address->getData("region") . ',' . $billing_address->getData("postcode"),
                                'bcountry' => $bill_country_name,
                                'btelephone' => "T: " . $billing_address->getData("telephone"),
                                'cdate' => $strCreatedDate,
                                'subject' => $strSubject,
                                'po' => $Po_Num,
                                'shipping_method_info' => $approver_shipping_method_info
                            ];

                            $logger->info(print_r($arrApprovers, true));
                            foreach ($arrApprovers as $arrApprover) {
                                $this->inlineTranslation->suspend();
                                $transport = $this->_transportBuilder->setTemplateIdentifier($intTemplateId)
                                    ->setTemplateOptions($arrTemplateOptions)
                                    ->setTemplateVars($arrmixApproverTemplateVars)
                                    ->setFrom($strFromEmailDetail)
                                    ->addTo($arrApprover)
                                    ->getTransport();
                                $transport->sendMessage();
                                $this->inlineTranslation->resume();
                                $logger->info('approver email sent to ' . $arrApprover);
                            }
                            $logger->info('all approver email sent');
                        }

                    } catch (\Exception $e) {
                        $logger->info($e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
    //This action will update the existing order status
    private function saveOldOrderStatus($OrderId)
    {
        if (isset($OrderId)) {
            $data=['state' => 'edited_by_approver','status'=>'edited_by_approver'];
            $this->connection->update($this->connection->getTableName('sales_order'), $data, 'entity_id=' . $OrderId);
            //This action will update the existing order status into sales order grid
            $data=['status'=>'edited_by_approver'];
            $this->connection->update($this->connection->getTableName('sales_order_grid'), $data, 'entity_id=' . $OrderId);
        } else {
            return false;
        }
        return true;
    }
}
