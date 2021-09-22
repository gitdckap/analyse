<?php

namespace Dckap\QuickRFQ\Controller\Invoice;

/**
 * Class Printview
 * @package Dckap\QuickRFQ\Controller\Invoice
 */
class Printview extends \Magento\Framework\App\Action\Action
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
     * Printview constructor.
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
            $this->messageManager->addNotice(__("Login Required to view order detail."));
            $loginUrl = $this->_url->getUrl('customer/account/login');
            return $resultRedirect->setPath($loginUrl);
        }
        $params = $this->getRequest()->getParams();
        $invoiceData = $this->getInvoiceData($params['id']);
        $this->_registry->register('ddi_invoice', $invoiceData);

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }

    /**
     * @param bool $invoiceNumber
     * @return bool|int
     */
    protected function getInvoiceData($invoiceNumber = false)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('invoice_detail');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getInvoiceDetail($integrationData, $invoiceNumber);
            if ($responseData && !empty($responseData)) {
                return $responseData;
            }
        }
        return false;
    }
}