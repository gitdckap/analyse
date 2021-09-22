<?php
/**
 * Copyright © DCKAP Inc. All rights reserved.
 */

namespace Mageplaza\LoginAsCustomer\Controller\Login;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\LoginAsCustomer\Helper\Data;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Mageplaza\LoginAsCustomer\Controller\Login
 */
class Login extends Action
{
    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Cart
     */
    protected $checkoutCart;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Login constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountRedirect $accountRedirect
     * @param Cart $checkoutCart
     * @param Data $helper
     * @param PageFactory $resultPageFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountRedirect $accountRedirect,
        Cart $checkoutCart,
        Data $helper,
        PageFactory $resultPageFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->session = $customerSession;
        $this->accountRedirect = $accountRedirect;
        $this->checkoutCart = $checkoutCart;
        $this->helperData = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreSession = $coreSession;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->_customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Forward|Redirect|ResultInterface
     */
    public function execute()
    {
        $customerId = $this->getRequest()->getParam('id');
        $accountNumber = $this->getRequest()->getParam('accountNumber');
        $sessionData = $this->coreSession->getEcommUserData();
        try {
            $this->session->loginById($customerId);
            $this->session->regenerateId();

            /* after customer logged in the actual account data will be stored */
            if (!$sessionData) {
                $customer = $this->_customerFactory->create()->load($customerId);
                $email = $customer->getEmail();
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('validate_user');
                if ($status) {
                    $this->coreSession->start();
                    $users = $this->clorasDDIHelper->validateEcommUser($integrationData, $email);
                    if ($users && isset($users['isValid']) && $users['isValid'] == 'yes') {
                        $sessionData = $users['user'];
                    }
                }
            }
            if ($sessionData && count($sessionData)) {
                foreach ($sessionData as $user) {
                    if ($accountNumber == $user['accountNumber']) {
                        $this->session->setEcommtoken(['ecomm_token' => $user['token']]);
                        $this->session->setCustomData($user);
                        $this->session->setMultiUserEnable(2);
                    }
                }
            }

            $redirectUrl = $this->accountRedirect->getRedirectCookie();
            if (!$this->helperData->getConfigValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                $this->accountRedirect->clearRedirectCookie();
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                return $resultRedirect;
            }
        } catch (Exception $e) {
            $this->messageManager->addError(__('An unspecified error occurred. Please contact us for assistance. '.$e->getMessage()));
        }
        return $this->accountRedirect->getRedirect();
    }
}
