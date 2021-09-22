<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_LoginAsCustomer
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
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
use Mageplaza\LoginAsCustomer\Model\LogFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Mageplaza\LoginAsCustomer\Controller\Login
 */
class Index extends Action
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
     * @var LogFactory
     */
    protected $_logFactory;

    /**
     * @var Cart
     */
    protected $checkoutCart;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param AccountRedirect $accountRedirect
     * @param Cart $checkoutCart
     * @param Data $helper
     * @param LogFactory $logFactory
     * @param PageFactory $resultPageFactory
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountRedirect $accountRedirect,
        Cart $checkoutCart,
        Data $helper,
        LogFactory $logFactory,
        PageFactory $resultPageFactory,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->session = $customerSession;
        $this->accountRedirect = $accountRedirect;
        $this->checkoutCart = $checkoutCart;
        $this->_logFactory = $logFactory;
        $this->helperData = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->coreSession = $coreSession;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Forward|Redirect|ResultInterface
     */
    public function execute()
    {
        $token = $this->getRequest()->getParam('key');

        $log = $this->_logFactory->create()->load($token, 'token');
        if (!$log || !$log->getId() || $log->getIsLoggedIn() || !$this->helperData->isEnabled()) {
            return $this->_redirect('noRoute');
        }

        try {
            if ($this->session->isLoggedIn()) {
                $this->session->logout();
            } else {
                $this->checkoutCart->truncate()->save();
            }
        } catch (Exception $e) {
            $this->messageManager->addNoticeMessage(__('Cannot truncate cart items.'));
        }

        try {
            /* call validate user api */
            $email = $log->getCustomerEmail();
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('validate_user');
            if ($status) {
                $this->coreSession->start();
                $users = $this->clorasDDIHelper->validateEcommUser($integrationData, $email);
                if ($users && isset($users['isValid']) && $users['isValid'] == 'yes') {
                    if (isset($users['user']) && count($users['user']) > 1) {
                        /* display company selection */
                        $this->coreSession->setEcommUserData($users['user']);
                    } elseif (isset($users['user']) && count($users['user']) == 1) {
                        /* if account has only one company then the customer logged in as usual */
                        $this->session->loginById($log->getCustomerId());
                        $this->session->regenerateId();
                        $log->setIsLoggedIn(true)
                            ->save();
                        $this->session->setMultiUserEnable(1);
                        $redirectUrl = $this->accountRedirect->getRedirectCookie();
                        if (!$this->helperData->getConfigValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                            $this->accountRedirect->clearRedirectCookie();
                            $resultRedirect = $this->resultRedirectFactory->create();
                            $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                            return $resultRedirect;
                        } else {
                            return $this->accountRedirect->getRedirect();
                        }
                    } else {
                        $this->messageManager->addWarningMessage(
                            __("Customer not fetched from ERP")
                        );
                    }
                } elseif (isset($users['isValid']) && $users['isValid'] == 'no') {
                    $this->coreSession->setIsEcommUservalid($users['isValid']);
                    $this->coreSession->setEcommUserErrorMessage($users['errorMessage']);
                    $this->messageManager->addWarningMessage(
                        __($users['errorMessage'])
                    );
                } else {
                    $this->session->loginById($log->getCustomerId());
                    $this->session->regenerateId();
                    $log->setIsLoggedIn(true)
                        ->save();
                    if (isset($users) && is_array($users)) {
                        $this->messageManager->addWarningMessage(
                            __("SOME ERROR FROM INFORM FOR THIS CUSTOMER")
                        );
                    } else {
                        $this->messageManager->addWarningMessage(
                            __("ERROR FROM INFORM - ".$users)
                        );
                    }
                    return $this->accountRedirect->getRedirect();
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(
                __($e->getMessage())
            );
        }

        return $this->resultPageFactory->create();
    }
}
