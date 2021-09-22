<?php

namespace Dckap\QuickRFQ\Controller\Quote;

/**
 * Class Allowquote
 * @package Dckap\QuickRFQ\Controller\Quote
 */
class Allowquote extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Dckap\Theme\Helper\Data
     */
    protected $themeHelper;

    /**
     * Allowquote constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Dckap\Theme\Helper\Data $themeHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Dckap\Theme\Helper\Data $themeHelper
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->themeHelper= $themeHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $customerSession = $this->customerSession->create();
        $response = [
            'success' => false,
            'login' => false
        ];
        if ($customerSession->isLoggedIn()) {
            $response['success'] = $this->themeHelper->getQuoteOptionView();
            $response['login'] = true;
        }
        $resultJson->setData($response);
        return $resultJson;
       
    }
}