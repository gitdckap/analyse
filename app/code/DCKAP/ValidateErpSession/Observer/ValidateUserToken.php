<?php


namespace DCKAP\ValidateErpSession\Observer;
use Magento\Framework\Event\ObserverInterface;

class ValidateUserToken implements ObserverInterface
{
    private $logger;
    private $customerSession;
    protected $ClorasDDIHelper;
    protected $messageManager;
    protected $_url;
    private $actionFlag;
    protected $_responseFactory;
    protected $_request;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Cloras\DDI\Helper\Data $ClorasDDIHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->logger = $logger;
        $this->actionFlag = $actionFlag;
        $this->customerSession = $customerSession;
        $this->ClorasDDIHelper = $ClorasDDIHelper;
        $this->messageManager = $messageManager;
        $this->_url = $url;
        $this->_responseFactory = $responseFactory;
        $this->_request = $request;
    }

    public function execute(\Magento\Framework\Event\Observer $observer )
    {
        try {
            $this->logger->info('add to cart before observer working fine');
            $arrmixCustomerData = ( array )$this->customerSession->getCustomData();

            list($status, $integrationData) = $this->ClorasDDIHelper->isServiceEnabled('validate_session');

            if (false == empty($arrmixCustomerData) && true == is_array($arrmixCustomerData) && true == array_key_exists('email', $arrmixCustomerData) && $status) {
                $this->logger->info('Customers session is active on store');
                $arrMixApiResponse = $this->ClorasDDIHelper->validateEcommUserSession($integrationData, $arrmixCustomerData['email']);

                if (true == array_key_exists('validateSession', $arrMixApiResponse) && true == is_array(array_filter($arrMixApiResponse['validateSession'])) && true == array_key_exists('isSessionValid', current($arrMixApiResponse['validateSession']))) {
                    $strIsValidSession = $arrMixApiResponse['validateSession'][0]['isSessionValid'];

                    if ('no' == $strIsValidSession) {
                        $this->logger->info('Customer validate session response is - ' . $strIsValidSession);
                        $this->customerSession->logout();
                        $loginUrl = $this->_url->getUrl('customer/account/login');
                        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                        $this->messageManager->addNotice(__('Your session has expired due to inactivity. You have now been redirected to the account log in page. Please re-enter your email address and password.'));
                        if ($this->_request->isXmlHttpRequest()) {
                            exit();
                        }
                        $this->_responseFactory->create()->setRedirect($loginUrl, 302)->sendResponse();
                        exit();
                    } else if ('yes' == $strIsValidSession) {
                        $this->logger->info('Customer validate session response is - ' . $strIsValidSession);
                        return true;
                    }
                } else if (true == array_key_exists('isValid', $arrMixApiResponse) && 'no' == $arrMixApiResponse['isValid'] && true == array_key_exists('errorMessage', $arrMixApiResponse)) {
                    $strErrorResponse = $arrMixApiResponse['errorMessage'];
                    $this->logger->info('Customer validate session response is - ' . $strErrorResponse);
                    $this->customerSession->logout();
                    $loginUrl = $this->_url->getUrl('customer/account/login');
                    $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $this->messageManager->addNotice(__('Your session has expired due to inactivity. You have now been redirected to the account log in page. Please re-enter your email address and password.'));
                    if ($this->_request->isXmlHttpRequest()) {
                        exit();
                    }
                    $this->_responseFactory->create()->setRedirect($loginUrl, 302)->sendResponse();
                    exit();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return true;
    }
}