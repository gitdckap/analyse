<?php

namespace Cloras\DDI\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerloginObserver implements ObserverInterface
{
    private $customerSession;

    private $checkoutSession;

    private $helper;

    private $ddiHelper;

    protected $logger;

    protected $coreCustomerSession;

    protected $scopeConfig;

    protected $request;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Cloras\Base\Helper\Data $helper,
        \Cloras\DDI\Helper\Data $ddiHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $coreCustomerSession,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->helper          = $helper;
        $this->ddiHelper = $ddiHelper;
        $this->logger = $logger;
        $this->coreCustomerSession = $coreCustomerSession;
        $this->extensionHelper = $extensionHelper;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    public function isB2B()
    {
        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if($configValue=="b2b") {
            return true;
        } else {
            return false;
        }
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $post = $this->request->getPost('login');
        if(!empty($post['acc_no'])) {
            $accDetail = (array)json_decode($post['acc_detail']);
            $this->coreCustomerSession->setCustomData($accDetail,true);
            if (isset($accDetail['token'])) {
                $this->coreCustomerSession->setEcommtoken(['ecomm_token' => $accDetail['token']]);
            }
        } else {
            $customerSession = $this->customerSession->create();
            if ($user = $customerSession->getEcommUserData()) {
                if (isset($user[0]['token'])) {
                    $this->coreCustomerSession->setEcommtoken(['ecomm_token' => $user[0]['token']]);
                }
                $this->coreCustomerSession->setCustomData($user[0]);
                if ($this->extensionHelper->getIsLogger() && $this->isB2B()) {
                    $this->logger->info('Ecomm user details added in session');
                }
            } else {
                /* this is used for login as customer fix */
                list($status, $integrationData) = $this->ddiHelper->isServiceEnabled('validate_user');
                if ($status) {
                    $users = $this->ddiHelper->validateEcommUser($integrationData, $customerSession->getCustomer()->getEmail());
                    if ($users && isset($users['isValid']) && $users['isValid'] == 'yes') {
                        if (isset($users['user'][0]['token'])) {
                            $this->coreCustomerSession->setEcommtoken(['ecomm_token' => $users['user'][0]['token']]);
                        }
                        $this->coreCustomerSession->setCustomData($users['user'][0]);
                    }
                }
            }
        }
    }
}
