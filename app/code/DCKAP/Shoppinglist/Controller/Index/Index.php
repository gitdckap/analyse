<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * @var  \Magento\Framework\View\Result\Page
     */
    protected $resultPageFactory;

    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    
    /**
     * @var  \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var  \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var  \DCKAP\Shoppinglist\Helper\Data
     */
    protected $shoppinglistHelper;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \DCKAP\Shoppinglist\Helper\Data $shoppinglistHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \DCKAP\Shoppinglist\Helper\Data $shoppinglistHelper
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession = $customerSession;
        $this->url = $context->getUrl();
        $this->shoppinglistHelper = $shoppinglistHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $customerSession = $this->customerSession->create();
        if (!$this->shoppinglistHelper->isShowShoppinglistAddOption()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account');
            return $resultRedirect;
        }
        if ($customerSession->isLoggedIn()) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Shopping List'));
            $resultPage->getLayout()->getBlock('messages')->setEscapeMessageFlag(true);
            return $resultPage;
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/login/');
        $customerSession->setAfterAuthUrl($this->url->getCurrentUrl());
        $customerSession->authenticate();
        return $resultRedirect;
    }
}
