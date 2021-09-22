<?php

namespace Dckap\QuickRFQ\Controller\Invoice;

/**
 * Class Summary
 * @package Dckap\QuickRFQ\Controller\Invoice
 */
class Summary extends \Magento\Framework\App\Action\Action
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
     * Summary constructor.
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
            $this->messageManager->addNotice(__("Login Required to view Invoice Summary."));
            $loginUrl = $this->_url->getUrl('customer/account/login');
            return $resultRedirect->setPath($loginUrl);
        }
        $params = $this->getRequest()->getParams();

//        $ddiInvoices = $this->customerSession->getDdiInvoices();
        if (isset($params['startDate']) && isset($params['endDate'])) {
            $startDate = $params['startDate'];
            $endDate = $params['endDate'];
        } else {
            $startDate = date('m/d/y', strtotime('-90 day'));
            $endDate = date('m/d/y');
        }
        /*$oldStartDate = $ddiInvoices['startDate'];
        $oldEndDate = $ddiInvoices['endDate'];
        if ($startDate == $oldStartDate && $endDate == $oldEndDate) {
            if (isset($ddiInvoices['invoiceList']) && count($ddiInvoices['invoiceList']) > 0) {
                $invoiceList = array();
                if (isset($ddiInvoices['invoiceList'])) {
                    $invoiceList = $ddiInvoices['invoiceList'];
                }
                $formatedOrderHitory = $this->getFormatedReportData($invoiceList, 25);
                $this->_registry->unregister('ddi_invoices');
                $this->_registry->register('ddi_invoices', $formatedOrderHitory);
                $this->_registry->register('ddi_custledger', $this->getCustLedgerData());
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;
            }
        }*/
        $filterData = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        $filterData['openOnly'] = 'Y';
        $ddiInvoices = $this->getCollectionData($filterData);
        $formatedData = $this->getFormatedReportData((isset($ddiInvoices['invoiceList'])) ? $ddiInvoices['invoiceList'] : [], 25);
//        $this->customerSession->setDdiInvoices($ddiInvoices);
        if (is_string($ddiInvoices)) {
            $this->messageManager->addNotice(__($ddiInvoices));
        } elseif (isset($ddiInvoices['isValid']) && $ddiInvoices['isValid'] == 'no') {
            $this->messageManager->addNotice(__($ddiInvoices['errorMessage']));
        } elseif (isset($ddiInvoices['data']['isValid']) && $ddiInvoices['data']['isValid'] == 'no') {
            $this->messageManager->addNotice(__($ddiInvoices['data']['errorMessage']));
        } elseif (isset($ddiInvoices['isValid']) && $ddiInvoices['isValid'] == 'yes') {
            $custLedgerData = $this->getCustLedgerData();
            if (is_string($custLedgerData)) {
                $this->messageManager->addNotice(__($custLedgerData));
            } elseif (isset($custLedgerData['isValid']) && $custLedgerData['isValid'] == 'no') {
                $this->messageManager->addNotice(__($custLedgerData['errorMessage']));
            } elseif (isset($custLedgerData['data']['isValid']) && $custLedgerData['data']['isValid'] == 'no') {
                $this->messageManager->addNotice(__($custLedgerData['data']['errorMessage']));
            } else {
                $this->_registry->register('ddi_invoices', $formatedData);
                $this->_registry->register('ddi_custledger', $custLedgerData);
            }
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
        $limit = (count($data)) ? count($data) : $limit;
        $startDate = (isset($params['startDate'])) ? $params['startDate'] : date('m/d/y', strtotime('-90 day'));
        $endDate = (isset($params['endDate'])) ? $params['endDate'] : date('m/d/y');
        $page = (isset($params['page'])) ? abs((int)$params['page']) : 1;
        $firstPage = ($page == 1) ? null : 1;

        $lastPage = floor(count($data) / $limit);

        if (fmod(count($data), $limit) > 0) {
            $lastPage = $lastPage + 1;
        }
        if ($lastPage == $page) {
            $lastPage = null;
        }
        $prevPage = ($page > 1) ? $page - 1 : null;
        $nextPage = ($page < $lastPage) ? $page + 1 : null;

        $start = abs($limit * ($page - 1));

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
            'end_date' => $endDate
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
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('invoice_list');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getInvoiceList($integrationData, $filterData);
            if ($responseData && isset($responseData['invoiceList']) && count($responseData['invoiceList'])) {
                foreach ($responseData['invoiceList'] as $key => $invoice) {
                    if ($invoice['invoiceStatus'] == 'Completed') {
                        unset($responseData['invoiceList'][$key]);
                    }
                }
                $responseData['startDate'] = $filterData['startDate'];
                $responseData['endDate'] = $filterData['endDate'];
                return $responseData;
            }
            return $responseData;
        }
        return false;
    }

    /**
     * @return bool|int
     */
    protected function getCustLedgerData()
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('cust_ledger');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getCustLedger($integrationData);
            if ($responseData && !empty($responseData)) {
                return $responseData;
            }
        }
        return false;
    }
}