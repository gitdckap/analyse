<?php

namespace Dckap\QuickRFQ\Controller\Customer;

/**
 * Class Index
 * @package Dckap\QuickRFQ\Controller\Customer
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $_checkoutSession;
    protected $_QuickRFQHelper;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Cloras\Base\Helper\Data $clorasHelper
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \DCKAP\Extension\Helper\Data $extensionHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Dckap\QuickRFQ\Helper\Data $QuickRFQHelper
    )
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->_registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->extensionHelper = $extensionHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_QuickRFQHelper = $QuickRFQHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
         $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        $this->messageManager->getMessages(true);
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addNotice(__("Login Required to view order pad."));
            $loginUrl = $this->_url->getUrl('customer/account/login');
            return $resultRedirect->setPath($loginUrl);
        }
        $params = $this->getRequest()->getParams();
        $shipto = '';
        if (!empty($params['shipto'])) {
            $shipto = $params['shipto'];
            $arrCustomerData = [
                'SelectedShipTo' => $shipto
            ];
            $this->customerSession->setCustomValue($arrCustomerData);
        } else if(array_key_exists('shipto',$params) && $params['shipto'] == ''){
            $this->_QuickRFQHelper->UnsetRetainShipTo();
            $shipto = '';
        }
        if ($this->extensionHelper->getShiptoConfig() && $shipto == '' && $configValue=="b2b") {
            $this->_registry->register('orderpad_items', []);
            $this->_registry->register('handle', []);
            $this->_registry->register('config', 1);
        } else {
            $oldShipto = ($this->customerSession->getShipto()) ? $this->customerSession->getShipto() : '';
            if ($oldShipto == $shipto) {
                $orderPadItems = $this->customerSession->getReportsOrders();
                if (isset($orderPadItems['orderPad']) && count($orderPadItems['orderPad']) > 0) {
                    $formatedOrderHitory = $this->getFormatedReportData($orderPadItems['orderPad'], $orderPadItems['pagination']);
                    $this->_registry->unregister('orderpad_items');
                    $this->_registry->register('orderpad_items', $formatedOrderHitory);
                    $resultPage = $this->resultPageFactory->create();
                    return $resultPage;
                }
            }
            if (isset($params['shipto'])) {
                $this->customerSession->setShipto($params['shipto']);
            } else {
                $this->customerSession->unsShipto();
            }

            $orderPadItems = $this->getCollectionData($shipto);
            if (is_string($orderPadItems)) {
                $this->messageManager->addNotice(__($orderPadItems));
            } elseif (isset($orderPadItems['isValid']) && $orderPadItems['isValid'] == 'no') {
                $this->messageManager->addNotice(__($orderPadItems['errorMessage']));
            } elseif (isset($orderPadItems['data']['isValid']) && $orderPadItems['data']['isValid'] == 'no') {
                $this->messageManager->addNotice(__($orderPadItems['data']['errorMessage']));
            } elseif (isset($orderPadItems['isValid']) && $orderPadItems['isValid'] == 'yes') {
                $this->customerSession->setReportsOrders($orderPadItems);
                $formatedData =[];
                if (isset($orderPadItems['orderPad']) && count($orderPadItems['orderPad']) > 0) {
                    $formatedData = $this->getFormatedReportData($orderPadItems['orderPad'], $orderPadItems['pagination']);
                }
                $this->_registry->register('orderpad_items', $formatedData);
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
        $shipto = (!empty($params['shipto'])) ? $params['shipto'] : null;

        if (!empty($params['limit'])) {
            $limit = abs((int)$params['limit']);
        } elseif ($pagination && $pagination != '') {
            $limit = (int)$pagination;
        } else {
            $limit = 25;
        }

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

        $newData = $data;
        $fdesc = '';
        if (isset($params['fdesc']) && $params['fdesc'] != '') {
            $fdesc = $params['fdesc'];
            foreach ($data as $key => $val) {
                if (!(strpos(strtolower($val['description']), strtolower($fdesc)) !== false)) {
                    unset($newData[$key]);
                }
            }
        }
        $data = $newData;

        $fieldColumn = array_column($data, $sortField);
        if ($sortField == 'price') {
            foreach ($fieldColumn as $key => $val) {
                $fieldColumn[$key] = str_replace('$', '', $val);
            }
        }
//        var_dump($fieldColumn);
        if ($sortField == 'lastDate') {
            foreach ($fieldColumn as $key => $val) {
                $fieldColumn[$key] = strtotime($val);
            }
        }
//        var_dump($fieldColumn);die;
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
            'current_sfield' => $sortField,
            'current_sorder' => $handleSorder,
            'fdesc' => $fdesc,
            'shipto' => $shipto
        ];
        $this->_registry->register('handle', $handle);

        return $returnData;
    }

    /**
     * @param bool $shipto
     * @return bool|int
     */
    protected function getCollectionData($shipto = false)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('orderpad');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getOrderpadItems($integrationData, $shipto);
            if ($responseData && !empty($responseData)) {
                return $responseData;
            }
        }
        return false;
    }
}