<?php

namespace DCKAP\OrderApproval\Controller\Adminhtml\Order;

class BackToPendingApproval extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $orderRepository;
    protected $jsonFactory;

    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory )
    {
        $this->_pageFactory = $pageFactory;
        $this->orderRepository = $orderRepository;
        $this->jsonFactory = $jsonFactory;
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
        try {
            $order = $this->orderRepository->get($params['order_id']);
            $order->setState("pending")->setStatus("pending_approval");
            $order->save();
            $arrResponseData['status'] = "Success";
            $arrResponseData['message'] = __("Order successfully Back To Pending Approval.");
        } catch (\Exception $e) {
            $arrResponseData['status'] = "Failure";
            $arrResponseData['message'] = $e->getMessage();
        }
        if ( true == array_key_exists('is_ajax',$params) && $params['is_ajax'] == '1') {
            $jsonResponseData = $this->jsonFactory->create();
            return $jsonResponseData->setData($arrResponseData);
        }
    }
}