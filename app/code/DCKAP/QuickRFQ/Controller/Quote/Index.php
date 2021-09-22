<?php

namespace Dckap\QuickRFQ\Controller\Quote;

/**
 * Class Index
 * @package Dckap\QuickRFQ\Controller\Quote
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;
    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;
    /**
     * @var \DCKAP\Extension\Helper\Data
     */
    protected $extensionHelper;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Cloras\Base\Helper\Data $clorasHelper
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \DCKAP\Extension\Helper\Data $extensionHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->_registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->extensionHelper = $extensionHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addNotice(__("Login Required to view quotes."));
            $loginUrl = $this->_url->getUrl('customer/account/login');
            return $resultRedirect->setPath($loginUrl);
        }
        $params = $this->getRequest()->getParams();

//        $ddiQuotes = $this->customerSession->getDdiQuotes();
        if (isset($params['startDate']) && isset($params['endDate'])) {
            $startDate = $params['startDate'];
            $endDate = $params['endDate'];
        } else {
            $startDate = date('m/d/y', strtotime('-90 day'));
            $endDate = date('m/d/y');
        }
        /*$oldStartDate = $ddiQuotes['startDate'];
        $oldEndDate = $ddiQuotes['endDate'];
        if ($startDate == $oldStartDate && $endDate == $oldEndDate) {
            if (isset($ddiQuotes['orderList']) && count($ddiQuotes['orderList']) > 0) {
                $quoteList = array();
                if (isset($ddiQuotes['orderList'])) {
                    $quoteList = $ddiQuotes['orderList'];
                }
                $formatedQuoteList = $this->getFormatedReportData($quoteList, 25);
                $this->_registry->unregister('ddi_quotes');
                $this->_registry->register('ddi_quotes', $formatedQuoteList);
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;
            }
        }*/
        $filterData = [
            'startDate' => $startDate,
            'endDate' => $endDate
            ];
        $ddiQuotes = $this->getCollectionData($filterData);
        $formatedData = $this->getFormatedReportData((isset($ddiQuotes['orderList'])) ? $ddiQuotes['orderList'] : [], 25);
//        $this->customerSession->setDdiQuotes($ddiQuotes);
        if (is_string($ddiQuotes)) {
            $this->messageManager->addNotice(__($ddiQuotes));
        } elseif (isset($ddiQuotes['isValid']) && $ddiQuotes['isValid'] == 'no') {
            $this->messageManager->addNotice(__($ddiQuotes['errorMessage']));
        } elseif (isset($ddiQuotes['isValid']) && $ddiQuotes['isValid'] == 'yes') {
            $this->_registry->register('ddi_quotes', $formatedData);
        }
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    /**
     * @param $data
     * @param $pagination
     * @return array
     */
    protected function getFormatedReportData($data, $pagination)
    {
        $params = $this->getRequest()->getParams();

        if (!empty($params['limit'])) {
            $limit = abs((int)$params['limit']);
        } elseif ($pagination && $pagination != '') {
            $limit = (int)$pagination;
        } else {
            $limit = 25;
        }
        $startDate = (isset($params['startDate'])) ? $params['startDate'] : date('m/d/y', strtotime('-90 day'));
        $endDate = (isset($params['endDate'])) ? $params['endDate'] : date('m/d/y');
        $page = (isset($params['page'])) ? abs((int)$params['page']) : 1;
        $firstPage = ($page == 1) ? null : 1;

        $lastPage = floor(count($data) / $limit);

        if (fmod(count($data), $limit) > 0) {
            $lastPage = $lastPage + 1;
        }
        if ($lastPage == $page) {
            $lastPage = NULL;
        }
        $prevPage = ($page > 1) ? ($page - 1) : null;
        $nextPage = ($page < $lastPage) ? ($page + 1) : null;

        $start = abs($limit * ($page - 1));
        $sortField = (isset($params['sfield']) && !empty($params['sfield'])) ? $params['sfield'] : 'orderNumber';

        $handleSorder = 0;
        if (isset($params['sorder']) && !empty($params['sorder'])) {
            $sortOrder = ($params['sorder'] == 1) ? SORT_ASC : SORT_DESC;
            $handleSorder = 1;
        } else{
            $sortOrder = SORT_DESC;
        }
        $fieldColumn = array_column($data, $sortField);
        if ($sortField == 'orderTotal') {
            foreach ($fieldColumn as $key => $val) {
                $fieldColumn[$key] = str_replace('$', '', $val);
            }
        }
        array_multisort($fieldColumn, $sortOrder, $data);

        $returnData = array_slice($data, $start, $limit);

        if (count($data) < $limit) {
            $end = count($data);
        } elseif (count($returnData) < $limit) {
            $end = $start + count($returnData);
        } else {
            $end = abs($limit * ($page));
        }

        $handle = ['current_page' => $page,
            'first_page' => $firstPage,
            'last_page' => $lastPage,
            'prev_page' => $prevPage,
            'next_page' => $nextPage,
            'records_count' => count($data),
            'start' => $start + 1,
            'end' => $end,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'current_sfield' => $sortField,
            'current_sorder' => $handleSorder
        ];
        $this->_registry->register('handle', $handle);

        return $returnData;
    }

    /**
     * @param bool $filterData
     * @return bool|int
     */
    protected function getCollectionData($filterData = false)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('order_list');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getOrderList($integrationData, $filterData);
            if ($responseData && isset($responseData['orderList']) && count($responseData['orderList'])) {
                foreach ($responseData['orderList'] as $key => $order) {
                    if ($order['orderStatus'] == 'Requested' || $order['orderStatus'] == 'Quoted') {
                        continue;
                    } else {
                        unset($responseData['orderList'][$key]);
                    }
                }
                $responseData['startDate'] = $filterData['startDate'];
                $responseData['endDate'] = $filterData['endDate'];
            }
            return $responseData;
        }
        return false;
    }
}