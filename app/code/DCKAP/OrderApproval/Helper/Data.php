<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Setup\Exception;

/**
 * Class Data
 * @package DCKAP\OrderApproval\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    CONST DEFAULT_SHIP_TO_NUMBER = '999999999';
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \DCKAP\OrderApproval\Model\OrderApprovalFactory
     */
    protected $orderApprovalFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;
    protected $shipToModel;
    protected $customerFactory;
    protected $checkoutSession;
    protected $orderFactory;
    protected $_addresses;
    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \DCKAP\OrderApproval\Model\OrderApprovalFactory $orderApprovalFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer,
     *
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \DCKAP\OrderApproval\Model\OrderApprovalFactory $orderApprovalFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \DCKAP\Extension\Model\Shipto $shipToModel,
        CustomerFactory $customerFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Address $addresses

    ) {
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->orderApprovalFactory = $orderApprovalFactory;
        $this->addressRepository = $addressRepository;
        $this->jsonHelper = $jsonHelper;
        $this->quoteFactory = $quoteFactory;
        $this->serializer = $serializer;
        $this->shipToModel = $shipToModel;
        $this->customerFactory = $customerFactory;
		$this->inlineTranslation = $inlineTranslation;
		$this->_transportBuilder = $transportBuilder;
		$this->scopeConfig = $scopeConfig;
	    $this->_countryFactory = $countryFactory;
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->_addresses = $addresses;

        parent::__construct($context);

    }

    /**
     * Return WebsiteId
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * @return mixed
     */
    public function getWebsiteMode()
    {
        return $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return mixed
     */
    public function isOrderApprovalEnabled()
    {
        return $this->scopeConfig->getValue(
            'OrderApproval_section/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function isThresholdBasesApprovalAndAmount()
    {
        $intThresholdAmount= 0;
        $boolThreshold = $this->scopeConfig->getValue(
            'OrderApproval_section/threshold_based_order_approval/threshold_setting',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if($boolThreshold){
            $intThresholdAmount = $this->scopeConfig->getValue(
                'OrderApproval_section/threshold_based_order_approval/threshold_amount',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return $intThresholdAmount;
        }
      return false;
    }

    /**
     * @param bool $email
     * @param bool $accountNumber
     * @param bool $shiptoNumber
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderApprovalStatus($email = false, $accountNumber = false, $shiptoNumber = false, $isB2B = false)
    {
        if ($this->isOrderApprovalEnabled() && $this->getWebsiteMode() == 'b2b' && $isB2B == 1) {
            if ($email && $accountNumber && $shiptoNumber) {
                $websiteId = $this->getCurrentWebsiteId();
                $collections = $this->orderApprovalFactory->create()->getCollection()
                    ->addFieldToFilter('customer_email', ['eq' => $email])
                    ->addFieldToFilter('erp_account_number', ['eq' => $accountNumber])
                    ->addFieldToFilter('ship_to_number', ['eq' => $shiptoNumber])
                    ->addFieldToFilter('website_id', ['eq' => $websiteId])
                    ->setPageSize(1)
                    ->setCurPage(1);
                if ($collections && $collections->getSize()) {
                    $item = $collections->getFirstItem();
                    if ($item && $item['order_approval'] == 1) {
                        return 1;
                    }
                }
            }
            return 0;
        }
        return 1;
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDefaultOrderApprovalStatus()
    {
        $customerSessionFactory = $this->customerSessionFactory->create();
        $customerSession = $customerSessionFactory->getCustomer();
        $isB2B = $customerSession->getData('is_b2b');
        if ($this->isOrderApprovalEnabled() && $this->getWebsiteMode() == 'b2b' && $isB2B == '1') {
            $intShipToNumber = '';
            if ( true == is_null( $customerSession )|| !is_object( $customerSession->getDefaultShippingAddress() )) {
                return 1;
            }
            $shippingAddressId = $customerSession->getDefaultShippingAddress()->getId();
            $intAddressId = (int)$shippingAddressId;
            $objShipToAddress = $this->addressRepository->getById($intAddressId);
            $objDdiShipToNumber = $objShipToAddress->getCustomAttribute('ddi_ship_number');
            if (true == is_object($objDdiShipToNumber)) {
                $intShipToNumber = $objDdiShipToNumber->getValue();
            }
            if ($intShipToNumber && $intShipToNumber != '') {
                $email = $customerSession->getEmail();
                $customerSessionData = $customerSessionFactory->getCustomData();
                $accountNumber = $customerSessionData['accountNumber'];
                return $this->getOrderApprovalStatus($email, $accountNumber, $intShipToNumber, $isB2B);
            } else {
                return 0;
            }
        }
        return 1;
    }

    /**
     * @param $ArrJsonBssCustomFeildData
     * @return string
     */
    public function getPurchaseOrderNumber($ArrJsonBssCustomFeildData)
    {
        $strPurchaseOrderNumber = '';
        if ($ArrJsonBssCustomFeildData && $ArrJsonBssCustomFeildData != '') {
            $ArrBssCustomFeildData = (array)$this->jsonHelper->jsonDecode($ArrJsonBssCustomFeildData);
            foreach ($ArrBssCustomFeildData as $key => $field) {
                if ($field['frontend_label'] == 'Purchase Order Number') {
                    $strPurchaseOrderNumber = $field['value'];
                }
            }
        }
        return $strPurchaseOrderNumber;
    }

    /**
     * @param $intQuoteId
     * @return |null
     */
    public function getPurchaseOrderNumberByQuoteId($intQuoteId)
    {
        $strPurchaseOrderNumber = Null;
        $quote = $this->quoteFactory->create()->loadByIdWithoutStore($intQuoteId);
        if ($quote->getBssCustomfield()) {
            $customCheckoutField = $this->serializer->unserialize($quote->getBssCustomfield());

            if (isset($customCheckoutField['purchase_order_number'])) {
                if (isset($customCheckoutField['purchase_order_number']['value'])) {
                    $strPurchaseOrderNumber = $customCheckoutField['purchase_order_number']['value'];
                } else {
                    $strPurchaseOrderNumber = $customCheckoutField['purchase_order_number'];
                }
            }
        }
        return $strPurchaseOrderNumber;
    }

    public function getShiptoItems()
    {
        $shiptoItems = $this->shipToModel->toOptionArray();
        if ($shiptoItems && count($shiptoItems)) {
            return $shiptoItems;
        }
        return false;
    }

    public function getCustomerAddress($customerId)
    {
        $customer = $this->customerFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customerModel = $customer->load($customerId);
        $customerAddress = [];

        if ($customerModel->getAddresses() != null)
        {
            foreach ($customerModel->getAddresses() as $address) {
                $customerAddress[] = $address->toArray();
            }
        }

        return $customerAddress;
    }

    public function SendOrderApprovalEmail($order, $area = '', $boolIsFromCancelOrder = false ){

	    $writer = new \Zend\Log\Writer\Stream(BP . "/var/log/emailtmp.log");
	    $logger = new \Zend\Log\Logger();
	    $logger->addWriter($writer);
	    $logger->info("createMageOrder------------");
        if ($area != 'admin') {
            $customerSessionFactory = $this->customerSessionFactory->create();
            $customerSession = $customerSessionFactory->getCustomer();
            $strCcApproverEmail = $customerSession->getEmail(); //Approver email address
        }

	    $shipping_address = $order->getShippingAddress();
        $boolCopyApproverEmail = $this->getCopyApproverEmailConfig();
        $boolCCApproverCancelOrder =  $this->getCopyApproverCancelOrderEmailConfig();
        if($boolIsFromCancelOrder && $boolCCApproverCancelOrder){
            $shipToNumber = $order->getShipToNumber();
            $erpAccountNumber = $order->getAccountNumber();
            $arrApprovers = $this->getApproverList($erpAccountNumber, $shipToNumber);
        }
	    $logger->info(print_r($shipping_address->getData(),true));
	    $country = $this->_countryFactory->create()->loadByCode($shipping_address->getData("country_id"));
	    $country_name = $country->getName();
	    //Billing Address
	    $billing_address = $order->getBillingAddress();
	    $BillCountry = $this->_countryFactory->create()->loadByCode($billing_address->getData("country_id"));
	    $bill_country_name = $BillCountry->getName();

	    $quote = $this->quoteFactory->create()->load($order->getQuoteId());
	    $poNumber ='';
	    $customCheckoutField = $this->serializer->unserialize($order->getBssCustomfield());
	    if (isset($customCheckoutField['purchase_order_number'])) {
		    if (isset($customCheckoutField['purchase_order_number']['value'])) {
			    $poNumber = $customCheckoutField['purchase_order_number']['value'];
		    } else {
			    $poNumber = $customCheckoutField['purchase_order_number'];
		    }
	    }
	    $Po_Num=  !empty($poNumber) ? $poNumber : ' Not Available';
	    $createdAt =  $order->getCreatedAt();
	    $cdate = date("m/y", strtotime($createdAt));
	    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
	    $templateId = $this->scopeConfig->getValue(
			    'OrderApproval_section/general/ddi_order_approval', $storeScope);// template id
	    $sender_email_identity = $this->scopeConfig->getValue(
			    'OrderApproval_section/general/sender_email_identity', $storeScope);// email sender
	    $emailsender = 'trans_email/ident_'.$sender_email_identity.'/email';
	    $emailsenderName = 'trans_email/ident_'.$sender_email_identity.'/name';

	    if(isset($sender_email_identity) && !empty($sender_email_identity)){
		    $fromEmail = $this->scopeConfig->getValue($emailsender, $storeScope, $order->getStoreId());
		    $fromName = $this->scopeConfig->getValue($emailsenderName, $storeScope, $order->getStoreId());
	    }else{
		    $fromEmail = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope, $order->getStoreId());
		    $fromName = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope, $order->getStoreId());
	    }

	    $toEmail =$order->getCustomerEmail(); // receiver email id
	    $ddi_order_id= $order->getDdiOrderId();
	    if(empty($ddi_order_id) && $boolIsFromCancelOrder == false){
		    $ddi_order_id= $order->getIncrementId();
		    $subject =  'Reference Order #'. $ddi_order_id .'  Review By The Approver ';
		    $header ='Your Reference Order #'.$ddi_order_id;
		    $content = 'Your reference order #'.$ddi_order_id.'  has been declined.';
	    } else if($boolIsFromCancelOrder){
            $order_id= $order->getIncrementId();
            $subject =  'Reference Order #'. $order_id .'  Cancelled By The Customer ';
            $header ='Your Reference Order #'.$order_id;
            $content = 'Your reference order #'.$order_id.'  has been Cancel.';
        }else{
		    $subject =  'Order #'. $ddi_order_id .'  Review By The Approver ';
		    $header ='Your Order #'.$ddi_order_id;
		    $content = 'Your order #'.$ddi_order_id.'  has been approved successfully.';
	    }

        $ship_str = strtolower($order->getShippingDescription());
        $str   = 'store';
        if (strpos($ship_str, $str) !== false) {
            $shipping_method_info = "Store Pickup – Pickup at ".$order->getDdiPrefWarehouse();
        }
        else{
            $shipping_method_info = $order->getShippingDescription();
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
			        'ddi_order_id'=>$ddi_order_id,
			    'header'=>$header,
			    'content'=>$content,
                'shipping_method_info' => $shipping_method_info

		    ];
		    $storeId = $this->storeManager->getStore()->getId();
		    $from = ['email' => $fromEmail, 'name' => $fromName];
		    $this->inlineTranslation->suspend();

		    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		    $templateOptions = [
				    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
				    'store' => $storeId
		    ];
		    if ($area == 'admin') {
                $transport = $this->_transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($toEmail)
                    ->getTransport();
                $transport->sendMessage();
            } else if($boolCopyApproverEmail && !$boolIsFromCancelOrder){
                $transport = $this->_transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($toEmail)
                    ->addCc($strCcApproverEmail)
                    ->getTransport();
                $transport->sendMessage();
            } else if($boolIsFromCancelOrder){
		        if($boolCCApproverCancelOrder && count($arrApprovers) ) {
                    foreach ($arrApprovers as $strEmailApprover) {
                        $transport = $this->_transportBuilder->setTemplateIdentifier($templateId)
                            ->setTemplateOptions($templateOptions)
                            ->setTemplateVars($templateVars)
                            ->setFrom($from)
                            ->addTo($strEmailApprover)
                            ->getTransport();
                        $transport->sendMessage();
                        $logger->info('Cancel email sent to approver' . $strEmailApprover);
                    }
                }
                $transport = $this->_transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($toEmail)
                    ->getTransport();
                $transport->sendMessage();
                $logger->info('User Cancel email sent to ' . $toEmail);
            } else{
                $transport = $this->_transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($toEmail)
                    ->getTransport();
                $transport->sendMessage();
            }
		    $this->inlineTranslation->resume();
	    } catch (\Exception $e) {
            $logger->info('exception' . $e->getMessage());
		    $data['message'] = $e->getMessage();
	    }

    }

    /**
     * @param bool $accountNumber
     * @param bool $shiptoNumber
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getApproverList($accountNumber = false, $shiptoNumber = false)
    {
        $approverList = [];
        if ($this->isOrderApprovalEnabled() && $this->getWebsiteMode() == 'b2b') {
            if ($accountNumber && $shiptoNumber) {
                $websiteId = $this->getCurrentWebsiteId();
                if( $shiptoNumber != SELF::DEFAULT_SHIP_TO_NUMBER ) {
                    $collections = $this->orderApprovalFactory->create()->getCollection()
                    ->addFieldToFilter('erp_account_number', ['eq' => $accountNumber])
                    ->addFieldToFilter('ship_to_number', ['eq' => $shiptoNumber])
                    ->addFieldToFilter('website_id', ['eq' => $websiteId])
                    ->addFieldToFilter('order_approval', ['eq' => 1]);
                } else {
                    $collections = $this->orderApprovalFactory->create()->getCollection()
                    ->addFieldToFilter('erp_account_number', ['eq' => $accountNumber])
                    ->addFieldToFilter('website_id', ['eq' => $websiteId])
                    ->addFieldToFilter('order_approval', ['eq' => 1]);
                }
                   if ($collections && $collections->getSize()) {
                    foreach ($collections as $approver) {
                        $approverList[] = $approver->getCustomerEmail();
                    }
                }
            }
        }
        return array_unique( $approverList );
    }

    /**
     * check whether current quote is edit order or not
     */
    public function isEditOrder()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getData('order_id')) {
            return true;
        }
        return false;
    }

    /**
     * return current quotes shipping method
     */
    public function getExistingShipMethod()
    {
        $quote = $this->checkoutSession->getQuote();
        $order = $this->orderFactory->create()->load($quote->getData('order_id'));
        return $order->getShippingMethod();
    }

    /**
     * @return mixed
     */
    public function getOrderEditAllow()
    {
        return $this->scopeConfig->getValue(
            'OrderApproval_section/general/ddi_allow_edit_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getCopyApproverEmailConfig()
    {
        return $this->scopeConfig->getValue(
            'OrderApproval_section/general/copy_approver_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getCopyApproverSubmitOrderEmailConfig()
    {
        return $this->scopeConfig->getValue(
            'OrderApproval_section/general/copy_submitter_approver_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCopyApproverCancelOrderEmailConfig()
    {
        return $this->scopeConfig->getValue(
            'OrderApproval_section/general/copy_approver_cancel_order_email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * return checkout custom fields data
     */
    public function getCheckoutCustomFields()
    {
        $orderData = [];
        try {
            $quote = $this->checkoutSession->getQuote();
            $order = $this->orderFactory->create()->load($quote->getData('order_id'));
            $quote = $this->quoteFactory->create()->load($order->getQuoteId());

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

            $orderData['store_pickup_branch'] = ($order->getDdiPrefWarehouse()) ? $order->getDdiPrefWarehouse() : "";
            $orderData['delivery_contact_email'] = ($order->getDdiDeliveryContactEmail()) ? $order->getDdiDeliveryContactEmail() : "";
            $orderData['delivery_contact_no'] = ($order->getDdiDeliveryContactNo()) ? $order->getDdiDeliveryContactNo() : "";
            $orderData['pickup_date'] = ($order->getDdiPickupDate()) ? $order->getDdiPickupDate() : "";
        } catch (\Exception $e) {
            $orderData['error'] = $e->getMessage().' - '.$e->getFile();
        }
        return $orderData;
    }
    public function getIsFromOrderApprovalEdit(){
        $boolIsFromOrderEdit = false;
        if( false == is_null($this->checkoutSession->getQuote()->getOrderId())){
            $boolIsFromOrderEdit = true;
        }
      return $boolIsFromOrderEdit;
    }
    public function getExistingShipto()
    {
        /* Set existing order's shipping address and shipping method to current quote */
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getData('order_id') ) {
            $order = $this->orderFactory->create()->load($quote->getData('order_id'));
            $accountNumber = $order->getData('account_number');
            $shipToNumber = $order->getData('ship_to_number');
            $currentCustomerId = $this->customerSessionFactory->create()->getCustomer()->getId();
            $addresses = $this->_addresses->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('parent_id', ['eq' => $currentCustomerId])
                ->addAttributeToFilter('erp_account_number', ['eq' => $accountNumber])
                ->addAttributeToFilter('ddi_ship_number', ['eq' => $shipToNumber]);
            if ($addresses && $addresses->getSize()) {
                foreach ($addresses as $customerAddress) {
                    $ddiShipNumber = $customerAddress->getData('ddi_ship_number');
                    $erpAccountNumber = $customerAddress->getData('erp_account_number');
                    if ($ddiShipNumber == $shipToNumber && $erpAccountNumber == $accountNumber) {
                        return $customerAddress->getId();
                    }
                }
            }
        }
        return false;
    }

    /**
     * Function to return whether submitted orders and pending approval page has permission or not
     */
    public function checkApprovalFunctionalityStatus()
    {
        $customerSessionFactory = $this->customerSessionFactory->create();
        $customerSession = $customerSessionFactory->getCustomer();
            $isB2B = $customerSession->getData('is_b2b');
        if ($this->isOrderApprovalEnabled() && $this->getWebsiteMode() == 'b2b' && $isB2B == '1') {
            return true;
        }
        return false;
    }
}
