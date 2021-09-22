<?php

namespace Dckap\Checkout\Controller\Ajax;

class User extends \Magento\Framework\App\Action\Action
{
    CONST DEFAULT_SHIP_TO_NUMBER = '999999999';
    protected $resultJsonFactory;
    protected $customerSession;
    protected $extensionHelper;
    protected $orderApprovalHelper;
    protected $checkoutSession;
    protected $addressRepository;
    protected $_cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->extensionHelper = $extensionHelper;
        $this->orderApprovalHelper = $orderApprovalHelper;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->_cart = $cart;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = [];
        try {
            $params = $this->getRequest()->getParams();
            $customerSessionData = $this->customerSession->getCustomData();
            $websiteMode = $this->extensionHelper->getWebsiteMode();
            if ($websiteMode == 'b2c') {
                $data['allowOnAccount'] = 'no';
            } else {
                $data['allowOnAccount'] = $customerSessionData['allowOnAccount'];
            }
            /**
             * Get order approval status based on the shipto
             */
            $orderApprovalStatus = '';
            if (isset($params['place']) && $params['place'] == 'minicart') {
                $orderApprovalStatus = $this->orderApprovalHelper->getDefaultOrderApprovalStatus();
            } else {
                $email = $this->customerSession->getCustomer()->getEmail();
                $isB2B = $this->customerSession->getCustomer()->getData('is_b2b');
                $accountNumber = $customerSessionData['accountNumber'];
                $shiptoNumber = '';
                $arrMixShippingAddress = $this->checkoutSession->getQuote()->getShippingAddress()->getData();
                if (true == array_key_exists('customer_address_id', $arrMixShippingAddress) && false == empty($arrMixShippingAddress['customer_address_id'])) {
                    $intAddressId = (int) $arrMixShippingAddress['customer_address_id'];
                    $objShipToAddress = $this->addressRepository->getById($intAddressId);
                    $objDdiShipToNumber = $objShipToAddress->getCustomAttribute('ddi_ship_number');
                    if (true == is_object($objDdiShipToNumber)) {
                        $shiptoNumber = $objDdiShipToNumber->getValue();
                    }
                }
                $data['is_b2b'] = 1;

                $orderApprovalStatus = $this->orderApprovalHelper->getOrderApprovalStatus($email, $accountNumber, $shiptoNumber, $isB2B);
                if(!$shiptoNumber){
                    $shiptoNumber = SELF::DEFAULT_SHIP_TO_NUMBER;
                }
                if ($orderApprovalStatus == 0) {
                    if (isset($params['totalAmount'])) {
                        $orderApprovalStatus = $this->ThresholdAmountBaseOrderApproval($params['totalAmount']);
                    }
                }
                $arrApproverList = $this->orderApprovalHelper->getApproverList($accountNumber, $shiptoNumber);
                if(empty($arrApproverList)){
                    $orderApprovalStatus = 1;
                }
            }
            $boolIsFromOrderEdit = $this->orderApprovalHelper->getIsFromOrderApprovalEdit();
            if($boolIsFromOrderEdit){
                $orderApprovalStatus = 1;
            }
            $data['order_approval'] = $orderApprovalStatus;
            $data['is_from_edit_order'] = $boolIsFromOrderEdit;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $resultJson->setData($data);
    }

    public function ThresholdAmountBaseOrderApproval($intTotalAmount){
        $tax = $intAdultS = $subTotal = $grandTotal = 0;
        $tax =  $this->_cart->getQuote()->getShippingAddress()->getData('tax_amount');
        $intAdultS = $this->_cart->getQuote()->getShippingAddress()->getData('adult_signature_fee');
        $subTotal = $this->_cart->getQuote()->getShippingAddress()->getData('subtotal_with_discount');
        $grandTotal = $subTotal + $tax + $intAdultS;
        if($intTotalAmount > $grandTotal){
            $grandTotal = $intTotalAmount;
        }
        $intThresholdAmount = $this->orderApprovalHelper->isThresholdBasesApprovalAndAmount();
        if( $intThresholdAmount != false && $intThresholdAmount > 0 && $grandTotal > $intThresholdAmount || $grandTotal == $intThresholdAmount ){
            return 0;
        }
        return 1;
    }
}
