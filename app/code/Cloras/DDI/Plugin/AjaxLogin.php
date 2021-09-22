<?php
namespace Cloras\DDI\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Exception\LocalizedException;

class AjaxLogin extends \Magento\Customer\Controller\Ajax\Login
{
    protected $customerSession;
    protected $customerAccountManagement;
    protected $helper;
    protected $resultJsonFactory;
    protected $resultRawFactory;
    protected $accountRedirect;
    protected $scopeConfig;
    private $cookieManager;
    private $cookieMetadataFactory;
    protected $dckapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $helper,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        CookieManagerInterface $cookieManager = null,
        CookieMetadataFactory $cookieMetadataFactory = null,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \DCKAP\Extension\Helper\Data $dckapHelper
    ) {
        parent::__construct($context, $customerSession, $helper, $customerAccountManagement, $resultJsonFactory, $resultRawFactory, $cookieManager, $cookieMetadataFactory);
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->customerFactory = $customerFactory;
        $this->dckapHelper = $dckapHelper;
    }

    public function execute()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/temp.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        try {
            $credentials = $this->helper->jsonDecode($this->getRequest()->getContent());
        } catch (\Exception $e) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }
        if (!$credentials || $this->getRequest()->getMethod() !== 'POST' || !$this->getRequest()->isXmlHttpRequest()) {
            return $resultRaw->setHttpResponseCode($httpBadRequestCode);
        }

        $response = [
            'errors' => false,
            'message' => __('Login successful.')
        ];
        try {


            $customer = $this->getCustomer($credentials['username']);
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('validate_user');
            if ($status) {
                $user = $this->clorasDDIHelper->validateEcommUser(
                    $integrationData,
                    $credentials['username']
                );
                $logger->info($user);
                if ($user) {
                    if ($customer->getId()) {
                        $this->customerSession->unsGuestProductData();

                        $customer = $this->customerAccountManagement->authenticate(
                            $credentials['username'],
                            $credentials['password']
                        );
                        $this->customerSession->setCustomerDataAsLoggedIn($customer);
                        $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
                        /* if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                             $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                             $metadata->setPath('/');
                             $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
                         }*/
                        if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                            $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                            $this->getAccountRedirect()->clearRedirectCookie();
                        }

                        $this->customerSession->setCustomData($user['user'][0]);

                    } else {
                        $this->customerSession->setEcommData($user['user']);
                        /*$result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $result->setPath('customer/account/create');
                        return $result;*/
                        $response = [
                            'errors' => true
                        ];
                        $response['redirectUrl'] = 'customer/account/create';
                    }
                } else {
                    if (isset($user['isValid']) && $user['isValid'] == 'no') {
                        $response = [
                            'errors' => true,
                            'message' => $user['errorMessage'],
                        ];
                    } else {
                        $callUs = $this->dckapHelper->getCallUs();
                        $storeName = $this->dckapHelper->getStoreName();
                        $response = [
                            'errors' => true,
                            'message' => 'We were unable to find your account at ' . $storeName . '. Please check your email address and password, or contact us at ' . $callUs,
                        ];
                    }
                }
            } else {
                $response = [
                    'errors' => true,
                    'message' => __('Invalid login or password.'),
                ];
            }



            /*$customer = $this->customerAccountManagement->authenticate(
                $credentials['username'],
                $credentials['password']
            );
            $this->customerSession->setCustomerDataAsLoggedIn($customer);
            $redirectRoute = $this->getAccountRedirect()->getRedirectCookie();
            if ($this->cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }
            if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectRoute) {
                $response['redirectUrl'] = $this->_redirect->success($redirectRoute);
                $this->getAccountRedirect()->clearRedirectCookie();
            }*/
        } catch (LocalizedException $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => __('Invalid login or password.'),
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    public function getCustomer($email)
    {
        $websiteId = $this->dckapHelper->getCurrentWebsiteId();
        $customerModel = $this->customerFactory->create()->setWebsiteId($websiteId);
        return $customerModel->loadByEmail($email);
    }
}
