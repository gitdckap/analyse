<?php

namespace DCKAP\OrderApproval\Block\Adminhtml\Order\View;

use Magento\Setup\Exception;

/**
 * Class View
 * @package DCKAP\OrderApproval\Block\Adminhtml\Order\View
 */
class View extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var
     */
    protected $urlBuider;

    /**
     * @var \DCKAP\OrderApproval\Helper\Data
     */
    protected $orderApprovalHelper;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;
    /**
     * @var
     */
    protected $_logger;

    /**
     * View constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\UrlInterface $urlBuilder,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->urlBuilder = $urlBuilder;
        $this->orderApprovalHelper = $orderApprovalHelper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function isOrderApprovalEnabled()
    {
        return $this->orderApprovalHelper->isOrderApprovalEnabled();
    }

    /**
     * @param bool $orderId
     * @return array|bool|float|int|mixed|string|null
     */
    public function getAdminOrderApprovalData($orderId = false)
    {
        if ($orderId && $orderId != '') {
            try {
                $order = $this->orderRepository->get($orderId);
                $adminOrderApprovalDetails = $order->getAdminApprovalDetails();
                if ($adminOrderApprovalDetails != '' && $adminOrderApprovalDetails != null) {
                    return $this->serializer->unserialize($adminOrderApprovalDetails);
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param $intOrderId
     * @return string
     */
    public function getBackToPendingApprovalUrl($intOrderId)
    {
        return $this->urlBuilder->getUrl('orderapproval/order/backtopendingapproval', ['order_id' => $intOrderId]);
    }

    /**
     * @param $intOrderId
     * @return string
     */
    public function getApprovalUrl($intOrderId)
    {
        return $this->urlBuilder->getUrl('orderapproval/order/approve', ['order_id' => $intOrderId]);
    }

    /**
     * @param $intOrderId
     * @return string
     */
    public function getOriginalOrderUrl($intOrderId)
    {
        return $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $intOrderId]);
    }

    /**
     * @param $intOldOrderId
     * @return string
     */
    public function getNewOrderUrl($intOldOrderId){
        $strNewOrderUrl = '#';
        try{
            $objExistingOrderDetail= $this->_orderCollectionFactory->create()->addFieldToSelect('*')
                ->addFieldToFilter('existing_order_id', ['eq' => $intOldOrderId])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem();
            $intOrderId = (int) $objExistingOrderDetail->getId();
            return $this->urlBuilder->getUrl('sales/order/view', ['order_id' =>$intOrderId ]);
        } catch (\Exception $e) {
            $this->_logger->info('Error in admin - '.$e->getMessage());
        }
        return $strNewOrderUrl;
    }
    /**
     * @return array
     */
    public function getUnserilizeOrderDetail($strJsonOrderDetails)
    {
        $arrOrderDetails = [];
        if(!empty($strJsonOrderDetails)){
            $arrOrderDetails = $this->serializer->unserialize($strJsonOrderDetails);
        }
        return $arrOrderDetails;
    }
}
