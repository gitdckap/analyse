<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Block;

/**
 * Class ViewOrderDetails
 * @package DCKAP\OrderApproval\Block
 */
class ViewOrderDetails extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var
     */
    protected $orders;

    /**
     * ViewOrderDetails constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('View Order Detail'));
    }

    /**
     * @return string
     */
    public function getOrderDetails()
    {
        try {
            $params = $this->getRequest()->getParams();
            $this->orders['sale_order_info'] = $this->_orderCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $params['id']]);
            $this->orders['sale_order_detail_info'] = $this->getOrderProductAndShippingDetails($params['id']);
            return $this->orders;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $order_id
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderProductAndShippingDetails($order_id)
    {
        return $this->orderRepository->get($order_id);
    }

    /**
     * @return string
     */
    public function getPendingOrderApprovalListUrl()
    {
        return $this->getUrl( 'orderapproval/index/pendingorders');
    }

    /**
     * @return string
     */
    public function getSubmittedOrdersUrl()
    {
        return $this->getUrl('orderapproval/index/submittedorders');
    }
}
