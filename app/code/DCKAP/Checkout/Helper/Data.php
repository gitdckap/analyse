<?php

namespace Dckap\Checkout\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    protected $sessionFactory;
    protected $orderFactory;
    protected $clorasHelper;
    protected $clorasDDIHelper;
    protected $customerSession;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->storeManager = $storeManager;
        $this->sessionFactory = $sessionFactory;
        $this->orderFactory = $orderFactory;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getOrder($incrementId)
    {
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }

    public function getERPErrorMessage()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pay_invoice.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Error Messages fr##################################################");
        //  $logger->info($this->customerSession->getCustomer()->getErrorMessage());
        $logger->info($this->customerSession->getErrorMessage());
        return $this->customerSession->getErrorMessage();
    }
    public function getInvoices($params = false)
    {
        if ($params && !empty($params)) {
            $data = array_filter(explode('__', $params['data']));
//            $invoiceList = $this->sessionFactory->create()->getDdiInvoices();
            $invoiceList = $this->getCollectionData();
            $invoiceListNew = [];
            if (isset($invoiceList['invoiceList'])) {
                foreach ($invoiceList['invoiceList'] as $item) {
                    $invoiceListNew[$item['invoiceNumber']] = $item;
                }
            }
            $returnData = [];
            if ($data && !empty($data)) {
                foreach ($data as $dat) {
                    $returnData['list'][$dat] = $invoiceListNew[$dat];
                }
            }
            $total = 0.00;
            foreach ($returnData['list'] as $invoice) {
                $total += (float)str_replace('$', '', str_replace(',', '', $invoice['openAmount']));
            }
            $returnData['total'] = number_format($total, 2, '.', '');
            return $returnData;
        }
        return false;
    }

    protected function getCollectionData($filterData = false)
    {
        $startDate = date('m/d/y', strtotime('-20 year'));
        $endDate = date('m/d/y');
        $filterData = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('invoice_list');
        if ($status) {
            $filterData['openOnly'] = 'Y';
            $responseData = $this->clorasDDIHelper->getInvoiceList($integrationData, $filterData);
            if ($responseData && !empty($responseData)) {
                $responseData['startDate'] = $filterData['startDate'];
                $responseData['endDate'] = $filterData['endDate'];
                return $responseData;
            }
        }
        return false;
    }
}
