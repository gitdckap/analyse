<?php
/**
 * Created by PhpStorm.
 * User: dckap
 * Date: 3/6/19
 * Time: 12:22 PM
 */

namespace DCKAP\Extension\Controller\Account;

use DCKAP\Extension\Helper\Data;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;

class Create extends \Magento\Customer\Controller\Account\Create
{
    protected $extensionHelper;

    protected $_messageManager;
    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        Registration $registration,
        Data $extensionHelper,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $customerSession, $resultPageFactory, $registration);
        $this->extensionHelper = $extensionHelper;
        $this->_messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
         $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            ScopeInterface::SCOPE_WEBSITE
        );
        if ($this->extensionHelper->checkIsB2c() == '0') {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/login');
            $this->_messageManager->addWarningMessage('You are not authorized to access create account. 
                                                                Please contact admin.');
            return $resultRedirect;
        } else {
            if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*');
                return $resultRedirect;
            }

            /** @var Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            return $resultPage;
        }
    }
}
