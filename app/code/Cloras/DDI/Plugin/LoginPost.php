<?php
namespace Cloras\DDI\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Controller\ResultFactory;

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

    protected $responseHttp;

    protected $currentCustomer;
    /**
     * @var Data
     */
    private $stylistHelper;
    /**
     * @var TimezoneInterface
     */
    private $dateTime;

    protected $clorasHelper;
    protected $clorasDDIHelper;
    private $resultFactory;
    protected $_registry;
    protected $customerFactory;
    protected $dckapHelper;
    protected $scopeConfig;

    public function __construct(
        Session $customerSession,
        Validator $formKeyValidator,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ManagerInterface $messageManager,
        ResponseHttp $responseHttp,
        TimezoneInterface $dateTime,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \DCKAP\Extension\Helper\Data $dckapHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->session = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->messageManager = $messageManager;
        $this->responseHttp = $responseHttp;
        $this->dateTime = $dateTime;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->resultFactory = $resultFactory;
        $this->_registry = $registry;
        $this->customerFactory = $customerFactory;
        $this->dckapHelper = $dckapHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $loginPost, \Closure $proceed)
    {

        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if ($loginPost->getRequest()->isPost()) {

            $login = $loginPost->getRequest()->getPost('login');
//            if (!empty($login['username']) && !empty($login['password']) && $configValue=="b2b") {
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->getCustomer($login['username']);
                    /* add a condition to validate ecomm user before login   */
                    $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sessioncustomer.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info($this->session->getIsEcommUservalid());
                    if($this->session->getIsEcommUservalid() == null) {
                        $logger->info('Inside Null');
                        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('validate_user');
                        $logger->info('status'.$status);
                        if ($status) {
                            $logger->info('Inside status');
                            $users = $this->clorasDDIHelper->validateEcommUser(
                                $integrationData,
                                $login['username']
                            );
                            if ($users && isset($users['isValid']) && $users['isValid'] == 'yes') {
                                $this->session->setIsEcommUservalid($users['isValid']);
                                $this->session->setEcommUserData($users['user']);
                            } elseif (isset($users['isValid']) && $users['isValid'] == 'no') {
                                $this->session->setIsEcommUservalid($users['isValid']);
                                $this->session->setEcommUserErrorMessage($users['errorMessage']);
                            } elseif (is_string($users)) { //if (is_array($users))
                                $logger->info('Inside array & string validation');
                                $this->session->setIsEcommUservalid('no');
                                $this->session->setEcommUserErrorMessage($users);
                            }
                        } else {
                            return $proceed();
                        }
                    }
                    $logger->info('Is ecomm customer'.$this->session->getIsEcommUservalid());
                    if ($this->session->getIsEcommUservalid() == 'yes') {
                        $logger->info('Valid customer Id'.$customer->getId());
                        if ($customer->getId()) {
                            $logger->info('Inside the condition');
                            $this->session->unsGuestProductData();
                            return $proceed();
                        } else {
                            $this->session->setEcommData($this->session->getEcommUserData());
                            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                            $result->setPath('customer/account/create');
                            return $result;
                        }
                    } else {
                        if ($this->session->getIsEcommUservalid() == 'no' || $this->session->getIsEcommUservalid() == '') {
                            $logger->info('Inside not a valid customer');
                            $this->messageManager->addWarningMessage($this->session->getEcommUserErrorMessage());
                        } else {
                            $logger->info('Else part (Inside not a valid customer)');
                            $callUs = $this->dckapHelper->getCallUs();
                            $storeName = $this->dckapHelper->getStoreName();
                            $this->messageManager->addWarningMessage('We were unable to find your account at '.$storeName.'. Please check your email address and password, or contact us at '.$callUs);
                        }
                        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $result->setPath('customer/account/login');
                        return $result;
                    }
                } catch (\Exception $e) {
                    $message = "Invalid User credentials.";
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                    $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $result->setPath('customer/account/login');
                    return $result;
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
        $websiteId = $this->dckapHelper->getCurrentWebsiteId();
        $customerModel = $this->customerFactory->create()->setWebsiteId($websiteId);
        return $customerModel->loadByEmail($email);
    }
}

