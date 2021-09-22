<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Block;

/**
 * Class SubmittedOrders
 * @package DCKAP\OrderApproval\Block
 */
class SubmittedOrders extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;


    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \DCKAP\OrderApproval\Helper\Data
     */
    protected $objOrderApprovalHelper;

    /**
     * @var
     */
    protected $orders;
    protected $addressRepository;
    protected $_serializer;
    /**
     * SubmittedOrders constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \DCKAP\OrderApproval\Helper\Data $OrderApprovalHelper
     * @param \DCKAP\OrderApproval\Model\OrderApprovalFactory $orderApprovalFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \DCKAP\OrderApproval\Helper\Data $OrderApprovalHelper,
        \DCKAP\OrderApproval\Model\OrderApprovalFactory $orderApprovalFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->objOrderApprovalHelper = $OrderApprovalHelper;
        $this->orderApprovalFactory = $orderApprovalFactory;
        $this->_serializer = $json;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Submitted Orders'));
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'DCKAP.OrderApproval.submittedorders.pager'
            )->setCollection(
                $this->getOrders()
            );
            $this->setChild('pager', $pager);
            $this->getOrders()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return html_entity_decode( $this->getChildHtml('pager') );
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrders()
    {
       $arrShipTo = ['999999999'];
        $customerId = $this->_customerSession->getCustomerId();

        if (true == is_null($customerId)) {
            return false;
        }

        $strStartDate = date('y/m/d', strtotime('-90 day'));
        $strEndDate = date('y/m/d', strtotime('+1 day'));
        $strSortBy = 'created_at';
        $strSortDir = 'desc';
        $strShipToSortBy = 'ship_to_number';
        $strShipToSortDir = 'ASC';

        $arrPostParams = $this->getRequest()->getParams();
        $arrAccountNumber = $this->_customerSession->getCustomData();

        $intStoreId = $this->objOrderApprovalHelper->getCurrentWebsiteId();
        $arrShipToData = (array) $this->objOrderApprovalHelper->getShiptoItems();

        foreach( $arrShipToData as $ShipToData  ){
            array_push($arrShipTo, $ShipToData['value'] );
        }

        if (true == is_array($arrPostParams) && !empty($arrPostParams) && true == array_key_exists('sort', $arrPostParams)) {
            $strSortBy = $arrPostParams['sort'];
            $strSortDir = $strShipToSortDir = 'ASC';
            if($strSortBy=='ship_to_number'){
                $strShipToSortDir = $strSortDir;
            }
        }

        if (true == is_array($arrPostParams) && !empty($arrPostParams) && true == array_key_exists('startDate', $arrPostParams) && true == array_key_exists('endDate', $arrPostParams)) {
            $strStartDate = date("Y-m-d",strtotime($arrPostParams['startDate'] ));
            $strEndDate = date("Y-m-d", strtotime($arrPostParams['endDate'] . ' +1 day'));
        }

        if (true == is_array($arrPostParams) && !empty($arrPostParams) && true == array_key_exists('srtshipto', $arrPostParams) ) {
            $arrShipTo = [$arrPostParams['srtshipto']];
        }

        if (true == is_array($arrPostParams) && !empty($arrPostParams) && true == array_key_exists('orderby', $arrPostParams) ) {
            $strSortBy = 'entity_id';
            $strShipToSortDir = $strSortDir='asc';
            if($arrPostParams['orderby']=='desc') {
                $strShipToSortDir = $strSortDir = 'desc';
            }
            if (true == is_array($arrPostParams) && !empty($arrPostParams) && true == array_key_exists('sort', $arrPostParams)) {
                $strSortBy = $arrPostParams['sort'];
            }
        }
        $status = ['pending_approval', 'declined', 'customer_cancelled'];
        $strFldCustomerId = 'customer_id';
        $getOrderStatus = ['null' => true];
        if (true == is_array($arrPostParams) && !empty($arrPostParams) && true == array_key_exists('isEdited', $arrPostParams)) {
            $status = ['approved'];
            $getOrderStatus = ['notnull' => true];
            $strFldCustomerId = 'customer_email';
            $customerId = $this->_customerSession->getCustomer()->getEmail();
        }
        if (!$this->orders && false == empty($customerId)) {
            $this->orders = $this->_orderCollectionFactory->create()->addFieldToSelect('*')
                ->addFieldToFilter( $strFldCustomerId, ['eq' => $customerId])
                ->addFieldToFilter('account_number', ['eq' => $arrAccountNumber['accountNumber']])
                ->addFieldToFilter('store_id', ['eq' => $intStoreId])
                ->addFieldToFilter('status', ['in' => $status])
                ->addFieldToFilter('ship_to_number', ['in' => $arrShipTo])
                ->addFieldToFilter('existing_order_id', $getOrderStatus )
                ->setOrder($strSortBy, $strSortDir)
                ->setOrder($strShipToSortBy,$strShipToSortDir)
                ->addAttributeToFilter('created_at', ['lt' => $strEndDate])
                ->addAttributeToFilter('created_at', ['gteq' => $strStartDate]);

        }
        return $this->orders;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllowedShipNumber()
    {
        $email = $this->_customerSession->getCustomer()->getEmail();
        $customData = $this->_customerSession->getCustomData();
        $erpNumber = $customData['accountNumber'];
        $intStoreId = $this->objOrderApprovalHelper->getCurrentWebsiteId();
        $shipNumberArray = [];
        $collections = $this->orderApprovalFactory->create()->getCollection()
            ->addFieldToFilter('customer_email', ['eq' => $email])
            ->addFieldToFilter('erp_account_number', ['eq' => $erpNumber])
            ->addFieldToFilter('website_id', ['eq' => $intStoreId]);
        if ($collections && $collections->getSize()) {
            foreach ($collections as $item) {
                $shipNumberArray[] = $item['ship_to_number'];
            }
        }

        return array_unique($shipNumberArray);
    }

    public function getCustomerShipToAddresses(){

        $strCustomerAddress = '';
        $arrCustomerAddresses = [];
        $customerId = $this->_customerSession->getCustomerId();
        $arrCustomerAddress = $this->objOrderApprovalHelper->getCustomerAddress($customerId);
        $customerSessionData = $this->_customerSession->getCustomData();
        $accountNumber = $customerSessionData['accountNumber'];

        if ($arrCustomerAddress && !empty($arrCustomerAddress)) {
            foreach ($arrCustomerAddress as $CustomerAddres) {
                if ((true == $CustomerAddres['is_active']) && (false == empty($CustomerAddres['ddi_ship_number'])) && ($CustomerAddres['erp_account_number'] == $accountNumber)) {
                    $street = trim(preg_replace('/\s+/', ' ', $CustomerAddres['street']));
                    $strCustomerAddress = $CustomerAddres['firstname'] . ' ' . $CustomerAddres['lastname'] . ', ' . $CustomerAddres['company'] . ', ' . $street . ', ' . $CustomerAddres['city'] . ', ' . $CustomerAddres['region'] . ', ' . strtoupper($CustomerAddres['country_id']) . ' - ' . $CustomerAddres['postcode'];
                    $arrCustomerAddresses[$CustomerAddres['ddi_ship_number']] = $strCustomerAddress;
                }
            }
        }
        return $arrCustomerAddresses;
    }
    /**
     * @param $orderId
     * @return string
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl('orderapproval/order/view', ['id' => $orderId, 'from' => 'submitted']);
    }
    /**
     * @param $orderId
     * @return string
     */
    public function getSubmitedUrl($orderId)
    {
        return $this->getUrl('orderapproval/order/view', ['id' => $orderId, 'from' => 'editedorder']);
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getCancelUrl($orderId)
    {
        return $this->getUrl('orderapproval/order/cancel', ['order_id' => $orderId]);
    }

    /**
     * @return string
     */
    public function getSubmittedOrdersListUrl()
    {
        return $this->getUrl('orderapproval/index/submittedorders');
    }
    /**
     * @return string
     */
    public function getPendingOrdersUrl()
    {
        return $this->getUrl('orderapproval/index/pendingorders');
    }

    /**
     * @return array
     */
    public function getUnserilizeOrderDetail($strJsonOrderDetails)
    {
        $arrOrderDetails = [];
        if(!empty($strJsonOrderDetails)){
            $arrOrderDetails = $this->_serializer->unserialize($strJsonOrderDetails);
        }
        return $arrOrderDetails;
    }
}
