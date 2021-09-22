<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Block;

/**
 * Class PendingOrders
 * @package DCKAP\OrderApproval\Block
 */
class PendingOrders extends \Magento\Framework\View\Element\Template
{
    CONST DEFAULT_SHIP_TO_NUMBER = '999999999';
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
     * @var \DCKAP\OrderApproval\Model\OrderApprovalFactory
     */
    protected $orderApprovalFactory;

    /**
     * @var
     */
    protected $orders;

    /**
     * PendingOrders constructor.
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
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->objOrderApprovalHelper = $OrderApprovalHelper;
        $this->orderApprovalFactory = $orderApprovalFactory;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Pending Approval'));
    }

    /**
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrders()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'sales.pendingorders.review.pager'
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
        return $this->getChildHtml('pager');
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrders()
    {

        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $arrAccountNumber = $this->_customerSession->getCustomData();
        $intStoreId = $this->objOrderApprovalHelper->getCurrentWebsiteId();
        $shipTo = (array) $this->getAllowedShipNumber();
        array_push($shipTo, SELF::DEFAULT_SHIP_TO_NUMBER);

        if ($shipTo && !empty($shipTo)) {
            $params = $this->getRequest()->getParams();
            if (true == array_key_exists('shipto', $params) && false == is_null($params)) {
                $shipTo = [$params['shipto']];
            }
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('ship_to_number', ['in' => $shipTo])
                ->addFieldToFilter('status', ['eq' => 'pending_approval'])
                ->addFieldToFilter('account_number', ['eq' => $arrAccountNumber['accountNumber']] )
               ->addFieldToFilter('customer_id', ['neq' => $customerId ])
                ->addFieldToFilter('store_id', ['eq' => $intStoreId])
                ->setOrder('created_at', 'desc');
            return $collection;
        }
        return false;
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
            ->addFieldToFilter('website_id', ['eq' => $intStoreId])
            ->addFieldToFilter('order_approval', ['eq' => 1]);
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
    public function getApproveUrl($orderId)
    {
        return $this->getUrl('orderapproval/order/approve', ['order_id' => $orderId]);
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getDeclineUrl($orderId)
    {
        return $this->getUrl('orderapproval/order/decline', ['order_id' => $orderId]);
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getViewUrl($orderId)
    {
        return $this->getUrl('orderapproval/order/view', ['id' => $orderId, 'from' => 'pending']);
    }


    /**
     * @return string
     */
    public function getPendingOrderApprovalListUrl()
    {
        return $this->getUrl( 'orderapproval/index/pendingorders');
    }
}
