<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Plugin;

use BitExpert\ForceCustomerLogin\Model\Session;
use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class AfterLoginPlugin
 *
 * @package BitExpert\ForceCustomerLogin\Plugin
 */
class AfterLoginPlugin
{
    /*
     * Redirect behaviour
     */
    const REDIRECT_DASHBOARD_ENABLED = '1';
    const REDIRECT_DASHBOARD_DISABLED = '0';
    /*
     * Configuration
     */
    const REDIRECT_DASHBOARD_CONFIG = 'customer/startup/redirect_dashboard';
    /**
     * @var Session
     */
    private $session;
    /**
     * @var string
     */
    private $defaultTargetUrl;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    protected $customerSession;
    protected $_storeManager;
    protected $_urlInterface;
    protected $resultRedirectNew;

    /**
     * AfterLoginPlugin constructor.
     *
     * @param Session $session
     * @param ScopeConfigInterface $scopeConfig
     * @param string $defaultTargetUrl
     */
    public function __construct(
        Session $session,
        ScopeConfigInterface $scopeConfig,
        $defaultTargetUrl,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        $this->session = $session;
        $this->scopeConfig = $scopeConfig;
        $this->defaultTargetUrl = $defaultTargetUrl;
        $this->customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->resultRedirectNew = $resultFactory;
    }

    /**
     * Customer login form page
     *
     * @param LoginPost $customerAccountLoginController
     * @param Redirect $resultRedirect
     * @return Redirect
     */
    public function afterExecute(LoginPost $customerAccountLoginController, $resultRedirect)
    {
        if (self::REDIRECT_DASHBOARD_ENABLED ===
            $this->scopeConfig->getValue(self::REDIRECT_DASHBOARD_CONFIG)) {
            $targetUrl = $this->session->getAfterLoginReferer();
            if($targetUrl){
                $resultRedirect->setUrl($targetUrl);
            }
            return $resultRedirect;
        }

        $targetUrl = $this->session->getAfterLoginReferer();
        if (empty($targetUrl)) {
//            $targetUrl = $this->defaultTargetUrl;
            $targetUrl = $this->_storeManager->getStore()->getBaseUrl();
        }
        $customerSession = $this->customerSession->create();
        $customer = $customerSession->getCustomer();
        if (!($customer->getId())) {
            $targetUrl = $this->_storeManager->getStore()->getBaseUrl().'customer/account/login';
        }

        $resultRedirectNew = $this->resultRedirectNew->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirectNew->setUrl($targetUrl);
        return $resultRedirectNew;

        /** @var $resultRedirect Redirect */
        /*$resultRedirect->setUrl($targetUrl);

        return $resultRedirect;*/
    }
}
