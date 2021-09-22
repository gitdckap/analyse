<?php

namespace DCKAP\AccountCreation\Plugin\Customer\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Controller\Result\RedirectFactory;

class LoginPost
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var ManagerInterface
     **/
    protected $messageManager;

    /**
     * @var Http
     **/
    protected $responseHttp;

    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    public function __construct(
        Session $customerSession,
        Validator $formKeyValidator,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ManagerInterface $messageManager,
        ResponseHttp $responseHttp,
        RedirectFactory $redirectFactory
    ) {
        $this->session = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->messageManager = $messageManager;
        $this->responseHttp = $responseHttp;
        $this->resultRedirectFactory = $redirectFactory;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $loginPost, \Closure $proceed)
    {
        if ($loginPost->getRequest()->isPost()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $login = $loginPost->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->getCustomer($login['username']);
                    if (!empty($customer->getCustomAttributes())) {
                        if ($this->isAccountNotApproved($customer)) {
                            $this->messageManager->addWarningMessage(
                                __(
                                    'Your account is not approved.
                             Kindly contact website admin for assistance.'
                                )
                            );
//                            $this->responseHttp->setRedirect('customer/account/login');
                            $resultRedirect->setPath('*/*/login');
                            return $resultRedirect;
                        } else {
                            return $proceed();
                        }
                    } else {
                        // if no custom attributes found
                        return $proceed();
                    }
                } catch (\Exception $e) {
                    $message = "Invalid User credentials.";
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
//                    $this->responseHttp->setRedirect('customer/account/login');
                    $resultRedirect->setPath('*/*/login');
                    return $resultRedirect;
                }
            } else {
                // call the original execute function
                return $proceed();
            }
        } else {
            // call the original execute function
            return $proceed();
        }
    }
    /**
     * @param $email
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer($email)
    {
        $this->currentCustomer = $this->customerRepositoryInterface->get($email);
        return $this->currentCustomer;
    }
    /**
     * Check if customer is a vendor and account is approved
     *
     * @return bool
     */
    public function isAccountNotApproved($customer)
    {
        $isApprovedAccount = $customer->getCustomAttribute('account_is_active')->getValue();
        if ($isApprovedAccount) {
            return false;
        }
        return true;
    }
}
